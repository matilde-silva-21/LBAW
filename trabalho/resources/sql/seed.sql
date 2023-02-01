-- create schema if not exists lbaw;
-- set search_path to lbaw;

-- Remove Duplicate Tables --------------
DROP TABLE IF EXISTS user_ CASCADE;
DROP TABLE IF EXISTS event CASCADE;
DROP TABLE IF EXISTS event_host CASCADE;
DROP TABLE IF EXISTS invited CASCADE;
DROP TABLE IF EXISTS ticket CASCADE;
DROP TABLE IF EXISTS review CASCADE;
DROP TABLE IF EXISTS comment CASCADE;
DROP TABLE IF EXISTS upvote_comment CASCADE;
DROP TABLE IF EXISTS report CASCADE;
DROP TABLE IF EXISTS photo CASCADE;
DROP TABLE IF EXISTS tag CASCADE;
DROP TABLE IF EXISTS city CASCADE;
DROP TABLE IF EXISTS country CASCADE;
DROP TYPE IF EXISTS Gender CASCADE;
DROP TYPE IF EXISTS Reason CASCADE;
DROP FUNCTION IF EXISTS event_search_update() CASCADE;
DROP FUNCTION IF EXISTS city_search_update() CASCADE;
DROP FUNCTION IF EXISTS country_search_update() CASCADE;
DROP FUNCTION IF EXISTS update_event_rating() CASCADE;
DROP FUNCTION IF EXISTS check_capacity() CASCADE;
DROP FUNCTION IF EXISTS check_date() CASCADE;
DROP FUNCTION IF EXISTS check_price() CASCADE;
DROP FUNCTION IF EXISTS check_ticket() CASCADE;
DROP FUNCTION IF EXISTS _create_ticket() CASCADE;
DROP FUNCTION IF EXISTS _delete_ticket() CASCADE;

DROP TABLE IF EXISTS password_resets CASCADE;


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
    profilePic text DEFAULT 'profile_pictures/generic_pic.jpg',
    admin boolean DEFAULT FALSE,
    isBlocked boolean DEFAULT FALSE,
    remember_token text
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
    read BOOLEAN DEFAULT FALSE, --sempre que eu abrir a tab dos invites, todas as notificacoes com user id auth ficam a read
    status boolean DEFAULT FALSE,
    invitedUserID integer REFERENCES user_ (userID) ON UPDATE CASCADE ON DELETE CASCADE,
    inviterUserID integer REFERENCES user_ (userID) ON UPDATE CASCADE ON DELETE CASCADE,
    eventID integer REFERENCES event (eventID) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (invitedUserID, inviterUserID, eventID)
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

CREATE TABLE upvote_comment (
    userID integer NOT NULL REFERENCES user_ (userID) ON UPDATE CASCADE ON DELETE CASCADE,
    commentID integer NOT NULL REFERENCES comment (commentID) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (userID, commentID)
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

/* CREATE FUNCTION update_event_rating () RETURNS TRIGGER AS
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
	AFTER INSERT OR UPDATE OR DELETE ON review FOR EACH ROW
	EXECUTE PROCEDURE update_event_rating ();    */

-- CREATE TRIGGER TO UPDATE EVENT avg_rating after INSERT, UPDATE, DELETE on review
CREATE OR REPLACE FUNCTION update_event_rating() RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        UPDATE event
        SET avg_rating = (SELECT AVG(rating) FROM review WHERE eventID = NEW.eventID)
        WHERE eventID = NEW.eventID;
    ELSIF TG_OP = 'UPDATE' THEN
        UPDATE event
        SET avg_rating = (SELECT AVG(rating) FROM review WHERE eventID = NEW.eventID)
        WHERE eventID = NEW.eventID;
    ELSIF TG_OP = 'DELETE' THEN
        UPDATE event
        SET avg_rating = (SELECT AVG(rating) FROM review WHERE eventID = OLD.eventID)
        WHERE eventID = OLD.eventID;
    END IF;
    RETURN NULL;
END;

$$ LANGUAGE plpgsql;

CREATE TRIGGER update_event_rating
AFTER INSERT OR UPDATE OR DELETE ON review
FOR EACH ROW EXECUTE PROCEDURE update_event_rating();

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
	BEFORE INSERT ON ticket FOR EACH ROW
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

CREATE TRIGGER delete_ticket AFTER DELETE ON ticket FOR EACH ROW
EXECUTE PROCEDURE _delete_ticket();

CREATE TRIGGER create_ticket BEFORE INSERT ON ticket FOR EACH ROW
EXECUTE PROCEDURE _create_ticket();

/** Users **/
-- Admins
INSERT INTO user_ (userID, name, email, birthDate, PASSWORD, gender, profilePic, admin)
    VALUES (1, 'Zediogo96', 'zediogox@hotmail.com', '2022/12/19', '$2a$12$9efD1sxdJGKrY9Ltr/Mccu6ChlFigRmtLZZ9a8935KHYj9i6SZ.Xe', 'M', 'profile_pictures/1.jpg', TRUE);

INSERT INTO user_ (userID, name, email, birthDate, PASSWORD, gender, profilePic, admin)
    VALUES (2, 'EduSilva', 'edu_silva@hotmail.com', '2022/12/19', '$2a$12$9efD1sxdJGKrY9Ltr/Mccu6ChlFigRmtLZZ9a8935KHYj9i6SZ.Xe','M', 'profile_pictures/2.jpg', TRUE);

INSERT INTO user_ (userID, name, email, birthDate, PASSWORD, gender, profilePic, admin)
    VALUES (3, 'AfonsoFarr', 'af_farroco@hotmail.com', '2022/12/19', '$2a$12$9efD1sxdJGKrY9Ltr/Mccu6ChlFigRmtLZZ9a8935KHYj9i6SZ.Xe', 'M', 'profile_pictures/3.jpg', TRUE);

INSERT INTO user_ (userID, name, email, birthDate, PASSWORD, gender, profilePic, admin)
    VALUES (4, 'MatildeSilva', 'mat_silva@hotmail.com', '2022/12/19', '$2a$12$9efD1sxdJGKrY9Ltr/Mccu6ChlFigRmtLZZ9a8935KHYj9i6SZ.Xe', 'M', 'profile_pictures/4.jpg', TRUE);

/* Users */

INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (5, 'Thomas Orwig', 'Paul_Pak78@hotmail.com', '2020/12/11', '9644949e2bbdf42153ee331ef896b0f6120339bca2027442604af3d9c0c3eb56', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (6, 'Frank Miller', 'Ike_Can93@gmail.com', '2021/12/26', '779dcd499d7988e1939e4dcdb4738f2c57fe953498a9bea2db9682da201828fb', 'M', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (7, 'Fred Cannon', 'Ty_Law93@yahoo.pt', '2021/12/8', '352ea9e27fb3b8e1c16cda3027ddf82cc655ed3ce39c1b302e987cb066fcea32', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (8, 'Alex Nugent', 'Steve_Nug89@hotmail.com', '2022/7/1', 'f13cd9be3e9a8d9701ea5fcd2e4b38c027e03410faba96893fa93967e53275d4', 'F', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (9, 'Tim Knutson', 'George_Nug88@hotmail.com', '2021/12/14', 'bad35a58c5baf00cdf7eca85f29cb160ad58ea9fc3bb5b7e112198f7027fe39e', 'F', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (10, 'Tim Hesch', 'Fred_Dei77@hotmail.com', '2021/11/1', '922aa4976cfd717be112e1cb351d8fefac85f53a56d582aaabbe3de054ffaf2e', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (11, 'George Boyd', 'Steve_McC96@hotmail.com', '2022/8/3', 'a32f293eee304390ed6da23ae4ab0590df1badcc473ae31ac363335dddd1a303', 'F', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (12, 'Fred McCormack', 'Walter_Bat96@yahoo.pt', '2020/5/22', '122d3e811d54aec7dd9c383e28cfe41814cb86de810c5fa9e3d4577df9404d53', 'M', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (13, 'Ty Ebner', 'David_And96@yahoo.pt', '2020/3/26', '922cad714594f58883ceccf409b64673921f2754718a73ffedd5f2a940ec9003', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (14, 'Joe Ashwoon', 'Dan_Hes84@yahoo.pt', '2020/8/20', '139eba792e702794e3bbea815d4a29714ebb334e68704f17976e61c69c562ae8', 'F', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (15, 'Fred Lawicki', 'Roger_Aik88@hotmail.com', '2020/1/11', '445960f22ccade2bbc48dca89574e4b04e378b4be9e671acc9e896e9be4a90ca', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (16, 'Mark Deitz', 'Jack_Aik95@gmail.com', '2022/12/14', '3f8800d771e57f2b4ff96de4abbf003bf174452ea6083b0213a60473947afbe0', 'M', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (17, 'Steve Knutson', 'David_Orw83@hotmail.com', '2020/8/16', 'f6d642c0a06524720de2be173938cff675682f96caa5282deee9a6995bb49b3c', 'M', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (18, 'Matthew Quinn', 'Adam_Qui94@gmail.com', '2020/1/8', '1aa5c97b42d3bc6b4700e4080c801edac54d9ec30f901c4f40ff86ffa509ba1d', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (19, 'Frank Ebner', 'Roger_Dei73@hotmail.com', '2022/4/7', '2f7a3b2a67a6855f4b49feaa7b7e8f2fff24315fc2c1a743168f1db3c5ef51a8', 'M', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (20, 'John Ebner', 'Paul_Bow79@gmail.com', '2020/5/4', 'bd5479f8e2004b5bb78033425ea93d9793806f45f3eb638416564206ceb9abe5', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (21, 'Hal Quinn', 'Monte_Mcc80@hotmail.com', '2022/4/16', '1b5232a2e0bcc326698673978afc2c73e61ef25c35bd030781cf964959f3311f', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (22, 'Aaron Lawless', 'Alex_Mcc77@gmail.com', '2021/7/23', 'c426f6564ff3fe43262fb80919164b628e9cbed0689b2baad181d00bc81fb8f6', 'M', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (23, 'David Paiser', 'Adam_Pet98@hotmail.com', '2021/2/5', '18139f04d65e32e7ba88278ceb04d37ae778919c01b25081c8f5d1e55df8858e', 'M', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (24, 'Roger Ebner', 'Aaron_McC87@yahoo.pt', '2020/10/20', '3d8aad9c272b2deadd95cda08af36258275a06bd16611fb7ff28675aeea77ffc', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (25, 'Walter Paiser', 'Aaron_Orw88@yahoo.pt', '2022/10/29', '2ab13944e0c68d7711827aa5745df82864578e3735660d557bfdf785a6585210', 'M', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (26, 'Aaron Myers', 'Paul_Aik86@gmail.com', '2022/6/24', 'c5a9facb04d5d9a718ca051829e7d14ae95c56a4e49d11a4196a77eb52c7d8d7', 'M', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (27, 'Hank Deitz', 'Roger_Mcc72@yahoo.pt', '2022/7/4', '540e50b6791811ac0d3543a8cffdddcd23b06979b1faebd9c1fe29099832e499', 'F', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (28, 'Ike Orwig', 'Edward_Bat83@gmail.com', '2022/11/6', 'db665c32a8da636aa5ab51c36d0ff13377b78dde38c6c232adb8c50fd833ae87', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (29, 'Carl Ortiz', 'Dan_Aik85@yahoo.pt', '2020/8/20', '710061e0f7a143f23afeefaca6bf03f864406846bb7d07b74c37e5e83d54e460', 'M', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (30, 'Peter Ashwoon', 'Ty_Hof70@hotmail.com', '2020/1/23', '8dba5e63966fb272f559dccdc5cf1c680cdceb9d806db675744112febba740c2', 'M', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (31, 'John Cannon', 'Thomas_Haw83@hotmail.com', '2020/3/2', '606ce7c94159e59dcdf5c5de805b146c6a8860f37b815c5bebb3b74e24e0e4e4', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (32, 'Peter Lawicki', 'Roger_Bat93@gmail.com', '2020/3/1', '56a68e287d32395a30a1e97f31dbb47beaf3f14c6f63fce5858f8fd3a9e39e26', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (33, 'Frank Frick', 'Fred_Qui96@gmail.com', '2021/6/10', '60aa464cd09bcf6343cb5186e88d9b7c2b735c2929379843b670a13bb467ac72', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (34, 'Adam Bateman', 'Edward_Qui81@hotmail.com', '2022/9/26', '319b4a8073489120daf23966dbc4318b9ecf3c7624bbb591e34a106164202535', 'F', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (35, 'Hal Kassing', 'Steve_Mil89@gmail.com', '2021/4/16', '0d73af7d297096e3e9d360c14189c192e664d1383ec3f2aa65bf4da7e9d35b5c', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (36, 'Adam Haworth', 'Roger_Ory87@hotmail.com', '2022/4/23', '69c6a296d7e7fac5ef03893b2da899672640c3a8d978fef3b384fbbba557432e', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (37, 'Carl Orwig', 'Jack_Kas84@gmail.com', '2020/8/22', 'ca9796a5be1c082214027081765562fbe6cd103e5de15fb3688c219edc1b8785', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (38, 'Paul Quinn', 'Ike_Mil74@yahoo.pt', '2020/10/3', '552a8d48c1c0a691f21de1cc04cba4310fab707e0d6cbf409b0e7fb848d43956', 'M', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (39, 'Roger Frick', 'Aaron_Bow72@yahoo.pt', '2022/2/26', 'd79998dd9271329f11e5bb248b274da5b9bff79f9ec9f00c03323feb88fa009e', 'F', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (40, 'Nathan Deitz', 'Fred_Aik90@hotmail.com', '2021/9/5', '7bb368f7f697084d9d682a9694127304eac06f9b42d20cea1250703b9c0ed7bf', 'F', 'profile_pictures/40.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (41, 'Roger McCormack', 'Matthew_Nug97@yahoo.pt', '2021/6/17', '5289899450d8c2b03f89c4a9f6936801c13a05982c0b0f5c8547237a779493af', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (42, 'Ty Boyd', 'Aaron_Pet90@yahoo.pt', '2022/10/5', 'b5df7550709a0cc4e054be75a9046ef4227e493cb885994c381d172e433e552b', 'M', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (43, 'Aaron Pak', 'Peter_Mye96@hotmail.com', '2022/2/9', 'f0cb14d1d2d63197bc93047ddb993fba2661111cdb83b258a7359979dfa6e3c1', 'M', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (44, 'George Nugent', 'Ben_Cas83@gmail.com', '2022/4/28', '4e4b1a50e531380ba48f8cb95927c119558955613e50b86413b1b64de4bf058b', 'M', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (45, 'Carl Kassing', 'Steve_Law89@hotmail.com', '2022/3/15', 'dab6a8176fa718f523c78b9cafd184d398eff4b41dc4ddb60f1e3487989a80c7', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (46, 'David Bowers', 'David_Fri73@hotmail.com', '2020/7/9', '14760187fce728b7316dda6cde9f35896b4eb4716d3944d697ad41461f7b8323', 'F', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (47, 'Alex Haworth', 'Steve_Boy82@gmail.com', '2021/1/12', 'fe14adb6950368a8e1fe764ae31c0dbc86dc6e615c2d48d2960a7f74ff5dc911', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (48, 'Nathan Orwig', 'Ty_Boy93@hotmail.com', '2021/12/5', '06b7fe3d366c8f3394c9a0c2e2c71968ec5eb6c663b7e2f0f0315e851fae72b1', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (49, 'Frank Lawless', 'Hal_Can72@yahoo.pt', '2020/11/4', 'fa52087106f92dbad59ceaab27a40583fe019e69f7db7d1598d67f6349693606', 'O', 'profile_pictures/generic_pic.jpg', False, False);
INSERT INTO user_ (userID, name, email, birthDate, password, gender, profilePic, admin, isBlocked) VALUES (50, 'George Ebner', 'john_McC71@hotmail.com', '2020/3/30', '$2a$12$9efD1sxdJGKrY9Ltr/Mccu6ChlFigRmtLZZ9a8935KHYj9i6SZ.Xe', 'F', 'profile_pictures/generic_pic.jpg', False, False);
/** +#PY'(}N **/

SELECT setval('user__userID_seq', (SELECT MAX(userID) from "user_"));

/** Tags **/

INSERT INTO tag (tagID, name, symbol) VALUES (1, 'music', 'MSC');
INSERT INTO tag (tagID, name, symbol) VALUES (2, 'visual-arts', 'VA');
INSERT INTO tag (tagID, name, symbol) VALUES (3, 'film', 'FLM');
INSERT INTO tag (tagID, name, symbol) VALUES (4, 'fashion', 'FSH');
INSERT INTO tag (tagID, name, symbol) VALUES (5, 'cooking', 'COK');
INSERT INTO tag (tagID, name, symbol) VALUES (6, 'charities', 'CHR');
INSERT INTO tag (tagID, name, symbol) VALUES (7, 'sports', 'SPO');
INSERT INTO tag (tagID, name, symbol) VALUES (8, 'nightlife', 'NGT');
INSERT INTO tag (tagID, name, symbol) VALUES (9, 'family', 'FAM');
INSERT INTO tag (tagID, name, symbol) VALUES (10, 'books', 'BOK');
INSERT INTO tag (tagID, name, symbol) VALUES (11, 'technology', 'TEC');

SELECT setval('tag_tagID_seq', (SELECT MAX(tagID) from "tag"));

/** Countries **/

INSERT INTO country (countryID, name) VALUES (1, 'Bahrain');
INSERT INTO country (countryID, name) VALUES (2, 'Saudi Arabia');
INSERT INTO country (countryID, name) VALUES (3, 'Australia');
INSERT INTO country (countryID, name) VALUES (4, 'Italy');
INSERT INTO country (countryID, name) VALUES (5, 'United States');
INSERT INTO country (countryID, name) VALUES (6, 'Monaco');
INSERT INTO country (countryID, name) VALUES (7, 'Azerbaijan');
INSERT INTO country (countryID, name) VALUES (8, 'Canada');
INSERT INTO country (countryID, name) VALUES (9, 'Great Britain');
INSERT INTO country (countryID, name) VALUES (10, 'Portugal');
INSERT INTO country (countryID, name) VALUES (11, 'Spain');
INSERT INTO country (countryID, name) VALUES (12, 'Scotland');

SELECT setval('country_countryID_seq', (SELECT MAX(countryID) from "country"));

/**
That's how it's supposed to work - next_val('test_id_seq') is only called when the system needs a value for this column and you have not provided one. If you provide value no such call is performed and consequently the sequence is not "updated".

You could work around this by manually setting the value of the sequence after your last insert with explicitly provided values:

SELECT setval('country_countryID_seq', (SELECT MAX(countryID) from "country"));

The name of the sequence is autogenerated and is always tablename_columnname_seq.
**/

/** Cities **/
INSERT INTO city (cityID, name, countryID) VALUES (1, 'Manama', 1);
INSERT INTO city (cityID, name, countryID) VALUES (2, 'Jeddah', 2);
INSERT INTO city (cityID, name, countryID) VALUES (3, 'Melbourne', 3);
INSERT INTO city (cityID, name, countryID) VALUES (4, 'Milan', 4);
INSERT INTO city (cityID, name, countryID) VALUES (5, 'Austin', 5);
INSERT INTO city (cityID, name, countryID) VALUES (6, 'Monte Carlo', 6);
INSERT INTO city (cityID, name, countryID) VALUES (7, 'Baku', 7);
INSERT INTO city (cityID, name, countryID) VALUES (8, 'Montreal', 8);
INSERT INTO city (cityID, name, countryID) VALUES (9, 'Silverstone', 9);
INSERT INTO city (cityID, name, countryID) VALUES (10, 'Portimão', 10);
INSERT INTO city (cityID, name, countryID) VALUES (11, 'Coimbra', 10);
INSERT INTO city (cityID, name, countryID) VALUES (12, 'Lisboa', 10);
INSERT INTO city (cityID, name, countryID) VALUES (13, 'Porto', 10);
INSERT INTO city (cityID, name, countryID) VALUES (14, 'Barcelona', 11);
INSERT INTO city (cityID, name, countryID) VALUES (15, 'Madrid', 11);
INSERT INTO city (cityID, name, countryID) VALUES (16, 'London', 9);
INSERT INTO city (cityID, name, countryID) VALUES (17, 'Edinburgh', 12);

SELECT setval('city_cityID_seq', (SELECT MAX(cityID) from "city"));

/** Events **/

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES (1, 'F1 STC Saudi Arabian Grand Prix 2023', 'The brand new Jeddah Corniche Circuit has once again opened its gates to F1 drivers, teams, and fans for an exhilarating race under the lights', 100000, '2023-03-19', '2022-10-01', 50, 7, 'Jeddah Cornice Circuit, Jeddah 23512, Saudi Arabia', 2, False);
INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES (2, 'Coldplay', 'Music of the Spheres World Tour is the ongoing eighth concert tour currently being undertaken by Coldplay. As always, an unique experience awaits you.', 40000, '2023-05-17', '2022-08-20', 50, 1, 'Estadio Cidade de Coimbra, 3030-320 Coimbra', 11, False);
INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES (3, 'Web Summit 2022', 'The most important conference about internet technology, emerging technologies, and venture capitalism', 35000, '2023-12-19', '2022-01-01', 60, 11, 'Altice Arena 1990-231 Lisbon', 12, False);
INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES (4, 'Porto vs Benfica', 'Porto and Benfica face each other in a thrilling match that can decide the Portuguese Chmpionship', 45000, '2023-12-30', '2022-09-05', 15, 7, 'Estadio do Dragao, 4350-415 Porto', 13, False);
INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES (5, 'Arctic Monkeys', 'The iconic band returns to Portugal and promises to deliver the show their fans have been waiting for for a long time', 65000, '2023-12-05', '2022-07-03', 60, 1, 'Parque da Bela Vista, Lisbon', 12, False);
INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES (6, 'F1 Heineken Australian Grand Prix 2022', 'Formula 1 returns to the streets of Melbourne, where Charles Leclerc won last year. Will Red Bull be capable of stealing the victory this season?', 150000, '2023-04-02', '2022-10-01', 50, 7, 'Albert Park Grand Prix Circuit, Albert Park VIC 3206, Melbourne', 3, False);
INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES (7, 'Slipknot', 'Slipknot is heading back out on tour this Fall for KNOTFEST ROADSHOW. This is your last chance to catch Slipknot on tour in the U.S. for a while', 70000, '2023-11-21', '2022-10-01', 30, 1, 'DKR Texas Memorial Stadium, 2139 San Jacinto Blvd, Austin, TX 78712, USA', 5, False);
INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES (8, 'Rolex Monte-Carlo Masters', 'The Rolex Monte-Carlo Masters, which celebrated its 100th anniversary in 2006, is the first of three ATP Masters 1000 tournaments played on clay', 70000, '2023-09-04', '2022-09-10', 20, 7, 'Monte-Carlo Country Club, 155 Av. Princesse Grace', 6, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES (9, 'Aniversário Zé', 'Venham celebrar comigo esta importante data, amigos!', 100, '2023-12-19', '2022-09-10', 0, 9, 'Porto, Vila Nova de Gaia', 13, True);

-- Family Events Boost

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(10, 'Visita Parque Biológico Gaia', 'Venham visitar o Parque Biológico de Gaia, um dos melhores parques de Portugal!', 300, '2023-05-03', '2022-12-04', 0, 9, 'Porto, Vila Nova de Gaia', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(11, 'Festival Canal Panda', 'O mítico festival do Canal Panda está de volta a Portugal!', 10000, '2023-06-19', '2022-10-01', 50, 9, 'Porto, Vila Nova de Gaia', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(12, 'Festival de Verão', 'O melhor festival de verão está de volta a Portugal!', 10000, '2023-07-19', '2022-10-01', 50, 9, 'Porto, Vila Nova de Gaia', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(13, 'SEA Life Porto', 'Venham visitar o SEA Life Porto, um dos melhores aquários de Portugal!', 500, '2023-05-03', '2022-12-04', 50, 9, 'Porto, Vila Nova de Gaia', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(14, 'Buggy Ride Familiar', 'Venham fazer um passeio de buggy familiar!', 100, '2023-05-03', '2022-12-04', 200, 9, 'Porto, Vila Nova de Gaia', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(15, 'Visita ao Museu do Vinho do Porto', 'Venham visitar o Museu do Vinho do Porto, um dos melhores museus de Portugal!', 300, '2023-05-03', '2022-12-04', 0, 9, 'Porto, Vila Nova de Gaia', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(16, 'Visita ao Museu do Carro Elétrico', 'Venham visitar o Museu do Carro Elétrico, um dos melhores museus de Portugal!', 300, '2023-05-03', '2022-12-04', 0, 9, 'Porto, Vila Nova de Gaia', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(17, 'Zoo Santo Inácio Tour', 'Venham visitar o Zoo Santo Inácio, um dos melhores zoológicos de Portugal!', 300, '2023-05-03', '2022-12-04', 0, 9, 'Zoo de Santo Inácio', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(18, 'Cruzeiro Rio Douro', 'Venham fazer um cruzeiro pelo Rio Douro!', 300, '2023-05-03', '2022-12-04', 250, 9, 'Porto, Vila Nova de Gaia', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(50, 'Visita Vigo', 'Venham visitar Vigo, uma das melhores cidades de Espanha!', 300, '2023-05-03', '2022-12-04', 0, 9, 'Vigo, Galicia', 14, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(51, 'Visita Santiago de Compostela', 'Venham visitar Santiago de Compostela, uma das melhores cidades de Espanha!', 300, '2023-05-03', '2022-12-04', 0, 9, 'Santiago de Compostela, Galicia', 14, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(52, 'Prova de Vinhos', 'Venham fazer uma prova de vinhos!', 300, '2023-05-03', '2022-12-04', 0, 9, 'Santiago de Compostela, Galicia', 14, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(53, 'Visita Museu de Arte Contemporânea', 'Venham visitar o Museu de Arte Contemporânea, um dos melhores museus de Espanha!', 300, '2023-05-03', '2022-12-04', 0, 9, 'Santiago de Compostela, Galicia', 14, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(54, 'Museu dos Computadores', 'Venham visitar o Museu dos Computadores, um dos melhores museus de Espanha!', 300, '2023-05-03', '2022-12-04', 0, 9, 'Santiago de Compostela, Galicia', 14, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(55, 'Museu Mercedes AMG', 'Venham visitar o Museu Mercedes AMG, um dos melhores museus de Espanha!', 300, '2023-05-03', '2022-12-04', 0, 9, 'Santiago de Compostela, Galicia', 14, False);

-- Sports Events Boost

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(19, 'F1 Spain Grand Prix 2022', 'Formula 1 returns to the streets of Barcelona, where Max Verstappen won last year. Will Red Bull be capable of stealing the victory this season?', 150000, '2023-05-01', '2022-10-01', 50, 7, 'Circuit de Barcelona-Catalunya, 08100 Montmeló, Barcelona', 14, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(20, 'F1 Monaco Grand Prix 2022', 'The Monaco Grand Prix is one of the most', 150000, '2023-05-01', '2022-10-01', 50, 7, 'Circuit de Monaco, 98000 Monaco', 6, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(21, 'F1 Portugal Grand Prix 2022', 'The Portuguese Grand Prix is one of the most', 150000, '2023-05-01', '2022-10-01', 50, 7, 'Autódromo Internacional do Algarve, 8005-139 Portimão', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(22, 'F1 Azerbaijan Grand Prix 2022', 'The Azerbaijan Grand Prix is one of the most', 150000, '2023-05-01', '2022-10-01', 50, 7, 'Baku City Circuit, Baku', 7, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(43, 'Sporting vs Porto', 'Sporting vs Porto', 50000, '2023-05-01', '2022-10-01', 50, 7, 'Estádio José Alvalade', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(44, 'Tiger Woods vs Phil Mickelson', 'Tiger Woods vs Phil Mickelson', 50000, '2023-05-01', '2022-10-01', 50, 7, 'Shadow Creek Golf Course', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(45, 'F1 British Grand Prix 2022', 'The British Grand Prix is one of the most', 150000, '2023-05-01', '2022-10-01', 50, 7, 'Silverstone Circuit, Towcester', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(46, 'Roger Federer vs Rafael Nadal', 'Roger Federer vs Rafael Nadal', 50000, '2023-05-01', '2022-10-01', 50, 7, 'Stade de Suisse, Bern', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(47, 'Jogos Olimpicos 2022', 'Jogos Olimpicos 2022', 50000, '2023-05-01', '2022-10-01', 50, 7, 'Tokyo, Japan', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(48, 'Xadrez Mundial 2022', 'Xadrez Mundial 2022', 50000, '2023-05-01', '2022-10-01', 50, 7, 'Porto, Portugal', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(49, 'Magnus Carlsen vs Fabiano Caruana', 'Magnus Carlsen vs Fabiano Caruana', 50000, '2023-05-01', '2022-10-01', 50, 7, 'Porto, Portugal', 13, False);

-- Music Events Boost

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(23, 'Metallica', 'Metallica is an American heavy metal band from Los Angeles, California. The band was formed in 1981 by drummer Lars Ulrich and vocalist/guitarist James Hetfield, and has been based in San Francisco for most of its career.', 50000, '2023-05-01', '2022-10-01', 50, 1, 'Passeio Marítimo de Algés, 1495-038 Algés', 12, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(24, 'Chico Buarque', 'Chico Buarque de Hollanda is a Brazilian singer-songwriter, composer, actor, and politician. He is considered one of the most important Brazilian songwriters of the 20th century.', 50000, '2023-05-01', '2022-10-01', 50, 1, 'Passeio Marítimo de Algés, 1495-038 Algés', 12, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(25, 'Red Hot Chilli Peppers', 'Red Hot Chili Peppers are an American rock band formed in Los Angeles in 1983. The groups musical style primarily consists of rock with an emphasis on funk, as well as elements from other genres such as punk rock and psychedelic rock.', 50000, '2023-05-01', '2022-10-01', 50, 1, 'Passeio Marítimo de Algés, 1495-038 Algés', 12, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(26, 'Tash Sultana', 'Tash Sultana is an Australian singer-songwriter and multi-instrumentalist. She is known for her live looping, which she uses to create complex rhythms and layers of sound.', 50000, '2023-05-01', '2022-10-01', 50, 1, 'Estadio do Dragão', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(27, 'Jorja Smith', 'Jorja Smith is an English singer and songwriter. She is signed to FAMM, a subsidiary of Black Butter Records, and has released two EPs, Project 11 and Lost & Found, and one studio album, Lost & Found.', 50000, '2023-05-01', '2022-10-01', 50, 1, 'Passeio Marítimo de Algés, 1495-038 Algés', 12, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(28, 'Iron Maiden', 'Iron Maiden are an English heavy metal band formed in Leyton, East London, in 1975 by bassist and primary songwriter Steve Harris. The band''s discography has grown to 38 albums, including 16 studio albums, 14 live albums, four EPs, and four compilations.', 50000, '2023-05-01', '2022-10-01', 50, 1, 'Estádio do Dragão, Porto', 13, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(37, 'Post Malone', 'Austin Richard Post, known professionally as Post Malone, is an American rapper, singer, songwriter, and record producer. He first gained major recognition in 2015 following the release of his debut single "White Iverson".', 50000, '2023-05-01', '2022-10-01', 50, 1, 'Passeio Marítimo de Algés, 1495-038 Algés', 12, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(38, 'Tame Impala', 'Tame Impala is an Australian psychedelic rock band formed in Perth in 2007. The band''s current lineup consists of Kevin Parker, Dominic Simper, Jay Watson, Cam Avery, and Julien Barbagallo.', 50000, '2023-05-01', '2022-10-01', 50, 1, 'Passeio Marítimo de Algés, 1495-038 Algés', 12, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(39, 'Jack Harlow', 'Jack Harlow is an American rapper, singer, and songwriter. He is best known for his singles "What''s Poppin" and "Tyler Herro".', 50000, '2023-05-01', '2022-10-01', 50, 1, 'Passeio Marítimo de Algés, 1495-038 Algés', 12, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(40, 'The Weeknd', 'Abel Makkonen Tesfaye, known professionally as The Weeknd, is a Canadian singer, songwriter, and record producer. He first gained recognition in 2011, when he anonymously uploaded several songs to YouTube under the name "The Weeknd".', 50000, '2023-05-01', '2022-10-01', 50, 1, 'Passeio Marítimo de Algés, 1495-038 Algés', 12, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(41, 'Lil Baby', 'Dominique Jones, known professionally as Lil Baby, is an American rapper. He is best known for his singles "My Dawg" and "Woah".', 50000, '2023-05-01', '2022-10-01', 50, 1, 'Passeio Marítimo de Algés, 1495-038 Algés', 12, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(42, 'Lil Uzi Vert', 'Symere Woods, known professionally as Lil Uzi Vert, is an American rapper, singer, and songwriter. He is best known for his singles "XO Tour Llif3" and "Money Longer".', 50000, '2023-05-01', '2022-10-01', 50, 1, 'Passeio Marítimo de Algés, 1495-038 Algés', 12, False);


-- Tech Events Boost
INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(29, 'Global Metaverse Carnival', 'The Global Metaverse Carnival is a 3-day event that will bring together the most influential leaders in the Metaverse, including CEOs, founders, investors, and developers.', 50000, '2023-05-01', '2022-10-01', 50, 11, 'Passeio Marítimo de Algés, 1495-038 Algés', 12, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(30, 'Lisbon Tech Job Fair 2023', 'The Lisbon Tech Job Fair is a 3-day event that will bring together the most influential leaders in the Metaverse, including CEOs, founders, investors, and developers.', 50000, '2023-05-01', '2022-10-01', 50, 11, 'Passeio Marítimo de Algés, 1495-038 Algés', 12, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(31, 'Beer in the Bloq', 'Where the best of the tech world meets the best of the beer world.', 50000, '2023-05-01', '2022-10-01', 50, 11, 'Passeio Marítimo de Algés, 1495-038 Algés', 12, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(32, 'Mobile World Congress', 'The Mobile World Congress is the world''s largest gathering for the mobile industry, organised by the GSMA and held in the Mobile World Capital Barcelona.', 50000, '2023-05-01', '2022-10-01', 50, 11, 'Fira Gran Via', 14, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(33, 'Cloudfest', 'Cloudfest events are a series of conferences and workshops that bring together the best minds in the cloud industry to share their knowledge and experience.', 50000, '2023-05-01', '2022-10-01', 50, 11, 'La Nave de Espana', 12, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(34, 'London Tech Week', 'London Tech Week is a week-long festival of technology, innovation and entrepreneurship, taking place across London in June 2023.', 50000, '2023-05-01', '2022-10-01', 50, 11, 'ExCeL London', 16, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(35, 'Turing Fest 2023', 'Turing Fest is a dedicated conference for developers, by developers, and is the only conference in the world that is 100% focused on the Microsoft Azure cloud platform.', 50000, '2023-05-01', '2022-10-01', 50, 11, 'Scotland Arena', 17, False);

INSERT INTO event (eventID, name, description, capacity, date, creationDate, price, tagID, address, cityID, isPrivate) VALUES
(36, 'AI & Big Data Expo', 'The AI & Big Data Expo is the world''s leading Artificial Intelligence and Big Data event, taking place in London on 21-22 June 2023.', 50000, '2023-05-01', '2022-10-01', 50, 11, 'ExCeL London', 16, False);

SELECT setval('event_eventID_seq', (SELECT MAX(eventID) from "event"));

/** (Event) Photos **/

INSERT INTO photo (photoID, path, eventID) VALUES (1, 'event_photos/1.jpg', 1);
INSERT INTO photo (photoID, path, eventID) VALUES (2, 'event_photos/2.jpg', 2);
INSERT INTO photo (photoID, path, eventID) VALUES (3, 'event_photos/3.jpg', 3);
INSERT INTO photo (photoID, path, eventID) VALUES (4, 'event_photos/4.jpg', 4);
INSERT INTO photo (photoID, path, eventID) VALUES (5, 'event_photos/5.jpg', 5);
INSERT INTO photo (photoID, path, eventID) VALUES (6, 'event_photos/6.jpg', 6);
INSERT INTO photo (photoID, path, eventID) VALUES (7, 'event_photos/7.jpg', 7);
INSERT INTO photo (photoID, path, eventID) VALUES (8, 'event_photos/8.jpg', 8);
INSERT INTO photo (photoID, path, eventID) VALUES (9, 'event_photos/9.jpg', 9);
INSERT INTO photo (photoID, path, eventID) VALUES (10, 'event_photos/10.jpg', 10);
INSERT INTO photo (photoID, path, eventID) VALUES (11, 'event_photos/11.jpg', 11);
INSERT INTO photo (photoID, path, eventID) VALUES (12, 'event_photos/12.jpg', 12);
INSERT INTO photo (photoID, path, eventID) VALUES (13, 'event_photos/13.jpg', 13);
INSERT INTO photo (photoID, path, eventID) VALUES (14, 'event_photos/14.jpg', 14);
INSERT INTO photo (photoID, path, eventID) VALUES (15, 'event_photos/15.jpg', 15);
INSERT INTO photo (photoID, path, eventID) VALUES (16, 'event_photos/16.jpg', 16);
INSERT INTO photo (photoID, path, eventID) VALUES (17, 'event_photos/17.jpg', 17);
INSERT INTO photo (photoID, path, eventID) VALUES (18, 'event_photos/18.jpg', 18);
INSERT INTO photo (photoID, path, eventID) VALUES (19, 'event_photos/19.jpg', 19);
INSERT INTO photo (photoID, path, eventID) VALUES (20, 'event_photos/20.jpg', 20);
INSERT INTO photo (photoID, path, eventID) VALUES (21, 'event_photos/21.jpg', 21);
INSERT INTO photo (photoID, path, eventID) VALUES (22, 'event_photos/22.jpg', 22);
INSERT INTO photo (photoID, path, eventID) VALUES (23, 'event_photos/23.jpg', 23);
INSERT INTO photo (photoID, path, eventID) VALUES (24, 'event_photos/24.jpg', 24);
INSERT INTO photo (photoID, path, eventID) VALUES (25, 'event_photos/25.jpg', 25);
INSERT INTO photo (photoID, path, eventID) VALUES (26, 'event_photos/26.jpg', 26);
INSERT INTO photo (photoID, path, eventID) VALUES (27, 'event_photos/27.jpg', 27);
INSERT INTO photo (photoID, path, eventID) VALUES (28, 'event_photos/28.jpg', 28);
INSERT INTO photo (photoID, path, eventID) VALUES (29, 'event_photos/29.jpg', 29);
INSERT INTO photo (photoID, path, eventID) VALUES (30, 'event_photos/30.jpg', 30);
INSERT INTO photo (photoID, path, eventID) VALUES (31, 'event_photos/31.jpg', 31);
INSERT INTO photo (photoID, path, eventID) VALUES (32, 'event_photos/32.jpg', 32);
INSERT INTO photo (photoID, path, eventID) VALUES (33, 'event_photos/33.jpg', 33);
INSERT INTO photo (photoID, path, eventID) VALUES (34, 'event_photos/34.jpg', 34);
INSERT INTO photo (photoID, path, eventID) VALUES (35, 'event_photos/35.jpg', 35);
INSERT INTO photo (photoID, path, eventID) VALUES (36, 'event_photos/36.jpg', 36);
INSERT INTO photo (photoID, path, eventID) VALUES (37, 'event_photos/37.jpg', 37);
INSERT INTO photo (photoID, path, eventID) VALUES (38, 'event_photos/38.jpg', 38);
INSERT INTO photo (photoID, path, eventID) VALUES (39, 'event_photos/39.jpg', 39);
INSERT INTO photo (photoID, path, eventID) VALUES (40, 'event_photos/40.jpg', 40);
INSERT INTO photo (photoID, path, eventID) VALUES (41, 'event_photos/41.jpg', 41);
INSERT INTO photo (photoID, path, eventID) VALUES (42, 'event_photos/42.jpg', 42);
INSERT INTO photo (photoID, path, eventID) VALUES (43, 'event_photos/43.jpg', 43);
INSERT INTO photo (photoID, path, eventID) VALUES (44, 'event_photos/44.jpg', 44);
INSERT INTO photo (photoID, path, eventID) VALUES (45, 'event_photos/45.jpg', 45);
INSERT INTO photo (photoID, path, eventID) VALUES (46, 'event_photos/46.jpg', 46);
INSERT INTO photo (photoID, path, eventID) VALUES (47, 'event_photos/47.jpg', 47);
INSERT INTO photo (photoID, path, eventID) VALUES (48, 'event_photos/48.jpg', 48);
INSERT INTO photo (photoID, path, eventID) VALUES (49, 'event_photos/49.jpg', 49);
INSERT INTO photo (photoID, path, eventID) VALUES (50, 'event_photos/50.jpg', 50);
INSERT INTO photo (photoID, path, eventID) VALUES (51, 'event_photos/51.jpg', 51);
INSERT INTO photo (photoID, path, eventID) VALUES (52, 'event_photos/52.jpg', 52);
INSERT INTO photo (photoID, path, eventID) VALUES (53, 'event_photos/53.jpg', 53);
INSERT INTO photo (photoID, path, eventID) VALUES (54, 'event_photos/54.jpg', 54);
INSERT INTO photo (photoID, path, eventID) VALUES (55, 'event_photos/55.jpg', 55);


SELECT setval('photo_photoID_seq', (SELECT MAX(photoID) from "photo"));

/** Reviews **/

INSERT INTO review (rating, userID, eventID) VALUES (4, 1, 5);
INSERT INTO review (rating, userID, eventID)
    VALUES (3, 1, 1);
INSERT INTO review (rating, userID, eventID)
    VALUES (5, 2, 1);
INSERT INTO review (rating, userID, eventID)
    VALUES (5, 3, 1);
INSERT INTO review (rating, userID, eventID)
    VALUES (5, 4, 1);
INSERT INTO review (rating, userID, eventID)
    VALUES (5, 5, 1);
INSERT INTO review (rating, userID, eventID)
    VALUES (4, 2, 2);
INSERT INTO review (rating, userID, eventID)
    VALUES (5, 3, 3);

INSERT INTO review (rating, userID, eventID)
    VALUES (4, 4, 4);

INSERT INTO review (rating, userID, eventID)
    VALUES (2, 5, 5);

INSERT INTO review (rating, userID, eventID)
    VALUES (0, 6, 6);

INSERT INTO review (rating, userID, eventID)
    VALUES (2, 7, 7);

INSERT INTO review (rating, userID, eventID)
    VALUES (5, 8, 8);

INSERT INTO review (rating, userID, eventID)
    VALUES (1, 9, 8);

/** Invited **/

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (TRUE, TRUE, 2, 7, 8);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (TRUE, TRUE, 3, 4, 8);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (FALSE, FALSE, 1, 10, 10);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (FALSE, FALSE, 7, 3, 2);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (TRUE, TRUE, 8, 3, 1);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (FALSE, FALSE, 3, 2, 7);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (TRUE, TRUE, 10, 8,  1);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (FALSE, FALSE, 3, 6, 5);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (FALSE, FALSE, 2, 50, 4);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (TRUE, TRUE, 5, 2, 8);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (FALSE, FALSE, 8, 2, 5);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (TRUE, TRUE, 10, 3, 7);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (FALSE, FALSE, 2, 8, 8);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (FALSE, FALSE, 50, 3, 1);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (FALSE, FALSE, 7, 1, 6);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (TRUE, TRUE, 1, 7, 3);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (TRUE, TRUE, 6, 2, 3);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (FALSE, FALSE, 1, 5, 6);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (FALSE, FALSE, 5, 7, 5);

INSERT INTO invited (read, status, invitedUserID, inviterUserID, eventID)
    VALUES (FALSE, FALSE, 2, 6, 4);

/* Event hosts */

INSERT INTO event_host (userID, eventID) VALUES (1, 1);
INSERT INTO event_host (userID, eventID) VALUES (1, 2);
INSERT INTO event_host (userID, eventID) VALUES (1, 3);
INSERT INTO event_host (userID, eventID) VALUES (1, 4);
INSERT INTO event_host (userID, eventID) VALUES (2, 5);
INSERT INTO event_host (userID, eventID) VALUES (2, 6);
INSERT INTO event_host (userID, eventID) VALUES (2, 7);
INSERT INTO event_host (userID, eventID) VALUES (2, 8);
INSERT INTO event_host (userID, eventID) VALUES (1, 9);
INSERT INTO event_host (userID, eventID) VALUES (1, 10);
INSERT INTO event_host (userID, eventID) VALUES (1, 11);
INSERT INTO event_host (userID, eventID) VALUES (1, 12);
INSERT INTO event_host (userID, eventID) VALUES (2, 13);
INSERT INTO event_host (userID, eventID) VALUES (2, 14);
INSERT INTO event_host (userID, eventID) VALUES (2, 15);
INSERT INTO event_host (userID, eventID) VALUES (2, 16);
INSERT INTO event_host (userID, eventID) VALUES (2, 17);
INSERT INTO event_host (userID, eventID) VALUES (3, 18);
INSERT INTO event_host (userID, eventID) VALUES (3, 19);
INSERT INTO event_host (userID, eventID) VALUES (3, 20);
INSERT INTO event_host (userID, eventID) VALUES (3, 21);
INSERT INTO event_host (userID, eventID) VALUES (3, 22);
INSERT INTO event_host (userID, eventID) VALUES (3, 23);
INSERT INTO event_host (userID, eventID) VALUES (3, 24);
INSERT INTO event_host (userID, eventID) VALUES (3, 25);
INSERT INTO event_host (userID, eventID) VALUES (3, 26);
INSERT INTO event_host (userID, eventID) VALUES (4, 27);
INSERT INTO event_host (userID, eventID) VALUES (4, 28);
INSERT INTO event_host (userID, eventID) VALUES (4, 29);
INSERT INTO event_host (userID, eventID) VALUES (4, 30);
INSERT INTO event_host (userID, eventID) VALUES (4, 31);
INSERT INTO event_host (userID, eventID) VALUES (4, 32);
INSERT INTO event_host (userID, eventID) VALUES (4, 33);
INSERT INTO event_host (userID, eventID) VALUES (4, 34);
INSERT INTO event_host (userID, eventID) VALUES (4, 35);
INSERT INTO event_host (userID, eventID) VALUES (4, 36);
INSERT INTO event_host (userID, eventID) VALUES (5, 37);
INSERT INTO event_host (userID, eventID) VALUES (5, 38);
INSERT INTO event_host (userID, eventID) VALUES (5, 39);
INSERT INTO event_host (userID, eventID) VALUES (5, 40);
INSERT INTO event_host (userID, eventID) VALUES (5, 41);
INSERT INTO event_host (userID, eventID) VALUES (5, 42);
INSERT INTO event_host (userID, eventID) VALUES (6, 43);
INSERT INTO event_host (userID, eventID) VALUES (6, 44);
INSERT INTO event_host (userID, eventID) VALUES (6, 45);
INSERT INTO event_host (userID, eventID) VALUES (6, 46);
INSERT INTO event_host (userID, eventID) VALUES (6, 47);
INSERT INTO event_host (userID, eventID) VALUES (6, 48);
INSERT INTO event_host (userID, eventID) VALUES (6, 49);
INSERT INTO event_host (userID, eventID) VALUES (7, 50);
INSERT INTO event_host (userID, eventID) VALUES (7, 51);
INSERT INTO event_host (userID, eventID) VALUES (7, 52);
INSERT INTO event_host (userID, eventID) VALUES (7, 53);
INSERT INTO event_host (userID, eventID) VALUES (7, 54);
INSERT INTO event_host (userID, eventID) VALUES (7, 55);

/* Comments */

INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (1, 'Just cant believe!!', '2021/4/13', '3:20', 1, 6);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (2, 'So excited!', '2020/10/5', '13:35', 2, 2);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (3, 'Too bad I cant go', '2020/2/9', '23:12', 3, 7);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (4, 'This artist is just thrash', '2022/6/25', '6:49', 4, 3);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (5, 'I feel like this pushes too many agenda', '2022/2/1', '21:18', 5, 8);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (6, 'Too bad I cant go', '2020/11/25', '15:28', 6, 6);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (7, 'Too bad I cant go', '2020/12/16', '7:38', 7, 4);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (8, 'Cant wait for it!', '2021/6/30', '23:33', 8, 5);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (9, 'This artist is just thrash', '2022/11/17', '16:19', 9, 8);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (10, 'NOPE LOL', '2020/11/8', '9:15', 10, 8);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (11, 'Price is too high', '2020/5/6', '9:46', 11, 4);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (12, 'NOPE LOL', '2021/6/29', '9:27', 12, 8);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (13, 'Just cant believe!!', '2022/9/13', '0:39', 13, 4);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (14, 'Price is too high', '2020/8/10', '8:7', 14, 8);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (15, 'Too political, LOL', '2021/3/29', '22:41', 15, 2);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (16, 'Price is too high', '2021/10/10', '13:36', 16, 6);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (17, 'So excited!', '2022/9/17', '20:59', 17, 7);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (18, 'I feel like this pushes too many agenda', '2020/2/13', '18:40', 18, 8);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (19, 'Too bad the prices are like this!', '2020/8/23', '9:58', 19, 8);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (20, 'So excited!', '2020/9/9', '10:58', 20, 4);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (21, 'Real fans know Hamilton got robbed in the 2021 WDC!', '2020/9/9', '10:58', 3, 1);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (22, 'So excited for this one!', '2020/9/9', '10:58', 2, 1);
INSERT INTO comment (commentID, text, date, time, userID, eventID) VALUES (23, 'Btw, who yall think will win this year?', '2021/10/12', '10:58', 2, 1);

INSERT INTO upvote_comment (userID, commentID) VALUES (1, 21);
INSERT INTO upvote_comment (userID, commentID) VALUES (1, 22);
INSERT INTO upvote_comment (userID, commentID) VALUES (2, 21);
INSERT INTO upvote_comment (userID, commentID) VALUES (3, 21);

SELECT setval('comment_commentID_seq', (SELECT MAX(commentID) from "comment"));

/* Reports */

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Dangerous/Illegal', 'LOL, cant believe he said that', '2022/10/18', '16:35', 1, 1, 1);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Other', 'Very aggressive behaviour', '2022/10/18', '16:35', 2, 2, 2);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Discriminatory', 'Very aggressive behaviour', '2022/10/18', '16:35', 3, 3, 3);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Dangerous/Illegal', 'LOL, cant believe he said that', '2022/10/18', '16:35', 4, 4, 4);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Dangerous/Illegal', 'This user has been doing homophobic comments', '2022/10/18', '16:35', 5, 5, 5);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Other', 'Thats kinda racist!', '2022/10/18', '16:35', 6, 6, 6);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Disrespectful', 'LOL, cant believe he said that', '2022/10/18', '16:35', 7, 7, 7);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Discriminatory', 'Very aggressive behaviour', '2022/10/18', '16:35', 8, 8, 8);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Disrespectful', 'I just felt attacked by this comment!', '2022/10/18', '16:35', 9, 2, 9);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Disrespectful', 'Very aggressive behaviour', '2022/10/18', '16:35', 10, 3, 10);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Disrespectful', 'Very aggressive behaviour', '2022/10/18', '16:35', 11, 4, 11);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Discriminatory', 'WTF!', '2022/10/18', '16:35', 12, 5, 12);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Misinformation', 'I just felt attacked by this comment!', '2022/10/18', '16:35', 13, 6, 13);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Disrespectful', 'This user has been doing homophobic comments', '2022/10/18', '16:35', 14, 7, 14);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Disrespectful', 'This user has been doing homophobic comments', '2022/10/18', '16:35', 15, 8, 15);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Disrespectful', 'I just felt attacked by this comment!', '2022/10/18', '16:35', 16, 7, 16);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Other', 'Very aggressive behaviour', '2022/10/18', '16:35', 17, 6, 17);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Disrespectful', 'Thats kinda racist!', '2022/10/18', '16:35', 18, 5, 18);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Discriminatory', 'This user has been doing homophobic comments', '2022/10/18', '16:35', 19, 4, 19);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Other', 'I just felt attacked by this comment!', '2022/10/18', '16:35', 20, 3, 18);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Misinformation', 'Thats kinda racist!', '2022/10/18', '16:35', 21, 2, 17);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Misinformation', 'Thats kinda racist!', '2022/10/18', '16:35', 22, 1, 16);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Misinformation', 'Very aggressive behaviour', '2022/10/18', '16:35', 23, 1, 15);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Dangerous/Illegal', 'Thats kinda racist!', '2022/10/18', '16:35', 24, 2, 14);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Dangerous/Illegal', 'Thats kinda racist!', '2022/10/18', '16:35', 25, 3, 13);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Other', 'Thats kinda racist!', '2022/10/18', '16:35', 26, 4, 12);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Misinformation', 'I just felt attacked by this comment!', '2022/10/18', '16:35', 27, 5, 11);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Dangerous/Illegal', 'LOL, cant believe he said that', '2022/10/18', '16:35', 28, 6, 10);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Disrespectful', 'This user has been doing homophobic comments', '2022/10/18', '16:35', 29, 7, 9);

INSERT INTO report (reason, description, date, time, userID, eventID, commentID)
    VALUES ('Other', 'Thats kinda racist!', '2022/10/18', '16:35', 30, 8, 8);

/* Tickets */

INSERT INTO ticket (qr_genstring, userID, eventID) VALUES ('9e25bc6c893e75664de8316a2b24f703c475c7c38a5b6b4057caec6a5922e9ed', 1,1);
INSERT INTO ticket (qr_genstring, userID, eventID) VALUES ('9e25bc6c893e75664de8316a2b24f703c475c7c38a5b6b4057caec6a5922e9ed', 1,2);
INSERT INTO ticket (qr_genstring, userID, eventID) VALUES ('9e25bc6c893e75664de8316a2b24f703c475c7c38a5b6b4057caec6a5922e9ed', 1,3);
INSERT INTO ticket (qr_genstring, userID, eventID) VALUES ('9e25bc6c893e75664de8316a2b24f703c475c7c38a5b6b4057caec6a5922e9ed', 1,4);
INSERT INTO ticket (qr_genstring, userID, eventID) VALUES ('9e25bc6c893e75664de8316a2b24f703c475c7c38a5b6b4057caec6a5922e9ed', 1,5);
INSERT INTO ticket (qr_genstring, userID, eventID) VALUES ('8056a38b89cdd91596af02f941a575dfd5d100ae9fff098e0694275fdd2115e9', 2,2);
INSERT INTO ticket (qr_genstring, userID, eventID) VALUES ('96047ed3338190c0bef12ad3056c209bd697859e3d9fc48cbc0872c9375f6c1e', 3,3);
INSERT INTO ticket (qr_genstring, userID, eventID) VALUES ('b737fdb3af65fb6ca162272339543a14a0bade2502784a3412d31f078d92f092', 4,4);
INSERT INTO ticket (qr_genstring, userID, eventID) VALUES ('dd5081045fc9b5a3116f12dc95ebb97be91fb9e6f8734728f898891dd4e542ca', 5,5);
INSERT INTO ticket (qr_genstring, userID, eventID) VALUES ('b3a3867d2e4ac172810eeb6130b5c4615751ddaddd156337bb7fe3299dd40c28', 6,6);
INSERT INTO ticket (qr_genstring, userID, eventID) VALUES ('45a69dbe2980995f8b9b8aa6065c92a1b5a550bfb74aaf95f85838929ca85b74', 7,7);
INSERT INTO ticket (qr_genstring, userID, eventID) VALUES ('527b93cdc6fcf912f9d9e0f018ab784deb4dc672ac8b6e07dc4a4cf7b00160ea', 8,8);






CREATE TABLE password_resets (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);