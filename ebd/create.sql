-- Remove Duplicate Tables --------------
DROP TABLE IF EXISTS user_ CASCADE;
DROP TABLE IF EXISTS event CASCADE;
DROP TABLE IF EXISTS event_host CASCADE;
DROP TABLE IF EXISTS invited CASCADE;
DROP TABLE IF EXISTS ticket CASCADE;
DROP TABLE IF EXISTS review CASCADE;
DROP TABLE IF EXISTS comment CASCADE;
DROP TABLE IF EXISTS report CASCADE;
DROP TABLE IF EXISTS photo CASCADE;
DROP TABLE IF EXISTS tag CASCADE;
DROP TABLE IF EXISTS city CASCADE;
DROP TABLE IF EXISTS country CASCADE;

-- Types --------------------------------
CREATE TYPE Gender AS ENUM (
    'M',
    'F',
    'O'
);

CREATE TYPE Reason AS ENUM (
    'Dangerous/Illegal',
    'Discriminatory',
    'Misinformation',
    'Disrespectful',
    'Other'
);

-- Tables -------------------------------
CREATE TABLE user_ (
    userID serial PRIMARY KEY,
    name text NOT NULL,
    email text NOT NULL UNIQUE,
    birthDate date NOT NULL,
    password TEXT NOT NULL,
    gender Gender NOT NULL,
    profilePic text DEFAULT 'assets/user_profile_photos/default_user_profile.png',
    admin boolean DEFAULT FALSE
);

CREATE TABLE tag (
    tagID serial PRIMARY KEY,
    name text NOT NULL,
    symbol text NOT NULL
);

CREATE TABLE country (
    countryID serial PRIMARY KEY,
    name text NOT NULL
);

CREATE TABLE city (
    cityID serial PRIMARY KEY,
    name text NOT NULL,
    countryID integer NOT NULL REFERENCES country (countryID) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE event (
    eventID serial PRIMARY KEY,
    name text NOT NULL,
    description text NOT NULL,
    capacity integer CHECK (capacity > 0),
    date timestamp CHECK (date > CURRENT_DATE),
    creationDate date DEFAULT CURRENT_DATE,
    price float CHECK (price >= 0),
    avg_rating integer DEFAULT 0,
    address text NOT NULL,
    tagID integer REFERENCES tag (tagID) ON UPDATE CASCADE,
    cityID integer REFERENCES city (cityID) ON UPDATE CASCADE,
    isPrivate boolean DEFAULT FALSE
);

CREATE TABLE event_host (
    userID integer REFERENCES user_ (userID) ON UPDATE CASCADE,
    eventID integer REFERENCES event (eventID) ON UPDATE CASCADE,
    PRIMARY KEY (userID, eventID)
);

CREATE TABLE invited (
    status boolean DEFAULT FALSE,
    userID integer REFERENCES user_ (userID) ON UPDATE CASCADE ON DELETE CASCADE,
    eventID integer REFERENCES event (eventID) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (userID, eventID)
);

CREATE TABLE ticket (
    qr_genstring text NOT NULL,
    userID integer REFERENCES user_ (userID) ON UPDATE CASCADE ON DELETE CASCADE,
    eventID integer REFERENCES event (eventID) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (userID, eventID)
);

CREATE TABLE review (
    rating integer CHECK (rating >= 0 AND rating <= 5),
    userID integer NOT NULL REFERENCES user_ (userID) ON UPDATE CASCADE ON DELETE CASCADE,
    eventID integer NOT NULL REFERENCES event (eventID) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (userID, eventID)
);

CREATE TABLE comment (
    commentID serial PRIMARY KEY,
    text text NOT NULL,
    date date DEFAULT CURRENT_DATE,
    time time without time zone DEFAULT CURRENT_TIME,
    userID integer NOT NULL REFERENCES user_ (userID) ON UPDATE CASCADE ON DELETE CASCADE,
    eventID integer NOT NULL REFERENCES event (eventID) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE report (
    reason Reason NOT NULL,
    description text NOT NULL,
    date date DEFAULT CURRENT_DATE,
    time time without time zone DEFAULT CURRENT_TIME,
    userID integer REFERENCES user_ (userID) ON UPDATE CASCADE ON DELETE CASCADE,
    eventID integer REFERENCES event (eventID) ON UPDATE CASCADE ON DELETE CASCADE,
    commentID integer REFERENCES comment (commentID) ON UPDATE CASCADE ON DELETE CASCADE,
    -- Because reports can be used for either comments or events (or both)
    CHECK ((eventID IS NOT NULL AND commentID IS NULL) OR (eventID IS NULL AND commentID IS NOT NULL) OR eventID IS NOT NULL AND commentID IS NOT NULL)
);

CREATE TABLE photo (
    photoID serial PRIMARY KEY,
    path text NOT NULL,
    eventID integer REFERENCES event (eventID) ON UPDATE CASCADE
);

-- Indexes -------------------------------

CREATE INDEX user_name ON user_ USING HASH(name);  

CREATE INDEX event_date ON event USING BTREE(date);

CREATE INDEX event_rating ON event USING BTREE(date);
CLUSTER event USING event_rating;

-- FTS Indexes -------------------------------

ALTER TABLE Event
ADD COLUMN tsvectors TSVECTOR;

CREATE FUNCTION event_search_update() RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN 
        NEW.tsvectors = (
            setweight(to_tsvector('english', coalesce(NEW.name,'')), 'A') ||
            setweight(to_tsvector('english', coalesce(NEW.description,'')), 'B')
        );
    END IF;

    IF TG_OP = 'UPDATE' THEN 
        IF (NEW.name <> OLD.name OR NEW.description <> OLD.description) THEN
            NEW.tsvectors = (
                setweight(to_tsvector('english', coalesce(NEW.name,'')), 'A') ||
                setweight(to_tsvector('english', coalesce(NEW.description,'')), 'B')
            );
        END IF;
    END IF;
    RETURN NEW;
END $$
LANGUAGE plpgsql;

CREATE TRIGGER tsvectorupdate BEFORE INSERT OR UPDATE 
    ON Event FOR EACH ROW EXECUTE PROCEDURE event_search_update();

 CREATE INDEX event_search ON Event USING GIN(tsvectors);

ALTER TABLE City
ADD COLUMN tsvectors_city TSVECTOR;

CREATE FUNCTION city_search_update() RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN 
        NEW.tsvectors_city = (
            setweight(to_tsvector('english', coalesce(NEW.name,'')), 'C')
        );
    END IF;

    IF TG_OP = 'UPDATE' THEN 
        IF (NEW.name <> OLD.name) THEN
            NEW.tsvectors_city = (
                setweight(to_tsvector('english', coalesce(NEW.name,'')), 'C')
            );
        END IF;
    END IF;
    RETURN NEW;
END $$
LANGUAGE plpgsql;

CREATE TRIGGER tsvectorupdate_city BEFORE INSERT OR UPDATE 
    ON City FOR EACH ROW EXECUTE PROCEDURE city_search_update(); 

 CREATE INDEX city_search ON City USING GIN(tsvectors_city);

ALTER TABLE Country
ADD COLUMN tsvectors_country TSVECTOR;

CREATE FUNCTION country_search_update() RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN 
        NEW.tsvectors_country = (
            setweight(to_tsvector('english', coalesce(NEW.name,'')), 'C')
        );
    END IF;

    IF TG_OP = 'UPDATE' THEN 
        IF (NEW.name <> OLD.name) THEN
            NEW.tsvectors_country = (
                setweight(to_tsvector('english', coalesce(NEW.name,'')), 'C')
            );
        END IF;
    END IF;
    RETURN NEW;
END $$
LANGUAGE plpgsql;

CREATE TRIGGER tsvectorupdate_country BEFORE INSERT OR UPDATE 
    ON Country FOR EACH ROW EXECUTE PROCEDURE country_search_update();  

CREATE INDEX country_search ON Country USING GIN(tsvectors_country);

-- Triggers and UDFs -------------------------------

--Trigger 01
--When some user creates or updates a review, the event average rating must be updated so that it can be used for sorting or searching purposes. 

CREATE FUNCTION update_event_rating () RETURNS TRIGGER AS
$BODY$
BEGIN
	UPDATE event
	SET
    avg_rating = ( SELECT AVG(rating)
    FROM review
    WHERE
    eventID = NEW.eventID
    );
	RETURN NEW;
END
$BODY$ 
LANGUAGE plpgsql;

CREATE TRIGGER event_avg_rating
	AFTER INSERT OR UPDATE OR DELETE ON review
	EXECUTE PROCEDURE update_event_rating ();    

--Trigger 02
--When a user tries to enroll in an event that is already at full capacity, an error message should be displayed.

CREATE FUNCTION check_capacity () RETURNS TRIGGER AS
$BODY$
BEGIN
	PERFORM capacity FROM event
	WHERE eventID = New.eventID;
    IF (SELECT capacity FROM event WHERE eventID = New.eventID AND capacity = 0) THEN RAISE EXCEPTION 'This event is already at full capacity';
	END IF;
	RETURN NEW;
END
$BODY$ 
LANGUAGE plpgsql;

CREATE TRIGGER check_event_capacity 
	BEFORE INSERT ON ticket
	EXECUTE PROCEDURE check_capacity ();

--Trigger 03
--When a ticket is deleted or bought, the corresponding event capacity should be updated.

CREATE FUNCTION _create_ticket () RETURNS TRIGGER AS 
$BODY$
BEGIN
	UPDATE event
	SET capacity = event.capacity - 1
	WHERE eventID = NEW.eventID;
	RETURN NEW;

END
$BODY$
LANGUAGE plpgsql;

CREATE FUNCTION _delete_ticket () RETURNS TRIGGER AS 
$BODY$ 
BEGIN
	UPDATE event
	SET capacity = event.capacity + 1
	WHERE eventID = OLD.eventID;
	RETURN OLD;
END 
$BODY$ 
LANGUAGE plpgsql;

CREATE TRIGGER delete_ticket AFTER DELETE ON ticket
EXECUTE PROCEDURE _delete_ticket();

CREATE TRIGGER create_ticket BEFORE INSERT ON ticket
EXECUTE PROCEDURE _create_ticket();

