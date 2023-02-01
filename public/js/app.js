function addEventListeners() {
    let itemCheckers = document.querySelectorAll(
        "article.card li.item input[type=checkbox]"
    );
    [].forEach.call(itemCheckers, function (checker) {
        checker.addEventListener("change", sendItemUpdateRequest);
    });

    let itemCreators = document.querySelectorAll("article.card form.new_item");
    [].forEach.call(itemCreators, function (creator) {
        creator.addEventListener("submit", sendCreateItemRequest);
    });

    let itemDeleters = document.querySelectorAll("article.card li a.delete");
    [].forEach.call(itemDeleters, function (deleter) {
        deleter.addEventListener("click", sendDeleteItemRequest);
    });

    let cardDeleters = document.querySelectorAll(
        "article.card header a.delete"
    );
    [].forEach.call(cardDeleters, function (deleter) {
        deleter.addEventListener("click", sendDeleteCardRequest);
    });

    let cardCreator = document.querySelector("article.card form.new_card");
    if (cardCreator != null)
        cardCreator.addEventListener("submit", sendCreateCardRequest);
    let notificationText = document.getElementById("notification_text");
    if (notificationText != null) {
        sendAjaxRequest('get', '/api/numberNotifications', null, notificationTextHandler);

    }
}

const notificationTextHandler = function () {
    if (this.status == 200) {
        const count = this.responseText;
        if (count == 0) {
            document.getElementById("notification_text").innerHTML = "";
        }

        else if (count == 1) {
            document.getElementById("notification_text").innerHTML = "<i class='fa fa-bell ml-1'></i>You have 1 new notification!";
        }
        else {
            document.getElementById("notification_text").innerHTML = "<i class='fa fa-bell mr-1'></i>    You have " + count + " new notifications!";
        }
    }
}

function encodeForAjax(data) {
    if (data == null) return null;
    return Object.keys(data)
        .map(function (k) {
            return encodeURIComponent(k) + "=" + encodeURIComponent(data[k]);
        })
        .join("&");
}

function sendAjaxRequest(method, url, data, handler) {
    let request = new XMLHttpRequest();

    request.open(method, url, true);
    request.setRequestHeader(
        "X-CSRF-TOKEN",
        document.querySelector('meta[name="csrf-token"]').content
    );
    request.setRequestHeader(
        "Content-Type",
        "application/x-www-form-urlencoded"
    );
    request.addEventListener("load", function() {
        handler(request);
    });
    //console.log("data ", data, document.querySelector('meta[name="csrf-token"]').content)
    request.send(encodeForAjax(data));
}

function sendItemUpdateRequest() {
    let item = this.closest("li.item");
    let id = item.getAttribute("data-id");
    let checked = item.querySelector("input[type=checkbox]").checked;

    sendAjaxRequest(
        "post",
        "/api/item/" + id,
        { done: checked },
        itemUpdatedHandler
    );
}

function sendDeleteItemRequest() {
    let id = this.closest("li.item").getAttribute("data-id");

    sendAjaxRequest("delete", "/api/item/" + id, null, itemDeletedHandler);
}

function sendCreateItemRequest(event) {
    let id = this.closest("article").getAttribute("data-id");
    let description = this.querySelector("input[name=description]").value;

    if (description != "")
        sendAjaxRequest(
            "put",
            "/api/cards/" + id,
            { description: description },
            itemAddedHandler
        );

    event.preventDefault();
}

function sendDeleteCardRequest(event) {
    let id = this.closest("article").getAttribute("data-id");

    sendAjaxRequest("delete", "/api/cards/" + id, null, cardDeletedHandler);
}

function sendCreateCardRequest(event) {
    let name = this.querySelector("input[name=name]").value;

    if (name != "")
        sendAjaxRequest("put", "/api/cards/", { name: name }, cardAddedHandler);

    event.preventDefault();
}

function itemUpdatedHandler() {
    let item = JSON.parse(this.responseText);
    let element = document.querySelector('li.item[data-id="' + item.id + '"]');
    let input = element.querySelector("input[type=checkbox]");
    element.checked = item.done == "true";
}

function itemAddedHandler() {
    if (this.status != 200) window.location = "/";
    let item = JSON.parse(this.responseText);

    // Create the new item
    let new_item = createItem(item);

    // Insert the new item
    let card = document.querySelector(
        'article.card[data-id="' + item.card_id + '"]'
    );
    let form = card.querySelector("form.new_item");
    form.previousElementSibling.append(new_item);

    // Reset the new item form
    form.querySelector("[type=text]").value = "";
}

function itemDeletedHandler() {
    if (this.status != 200) window.location = "/";
    let item = JSON.parse(this.responseText);
    let element = document.querySelector('li.item[data-id="' + item.id + '"]');
    element.remove();
}

function cardDeletedHandler() {
    if (this.status != 200) window.location = "/";
    let card = JSON.parse(this.responseText);
    let article = document.querySelector(
        'article.card[data-id="' + card.id + '"]'
    );
    article.remove();
}

function cardAddedHandler() {
    if (this.status != 200) window.location = "/";
    let card = JSON.parse(this.responseText);

    // Create the new card
    let new_card = createCard(card);

    // Reset the new card input
    let form = document.querySelector("article.card form.new_card");
    form.querySelector("[type=text]").value = "";

    // Insert the new card
    let article = form.parentElement;
    let section = article.parentElement;
    section.insertBefore(new_card, article);

    // Focus on adding an item to the new card
    new_card.querySelector("[type=text]").focus();
}

function createCard(card) {
    let new_card = document.createElement("article");
    new_card.classList.add("card");
    new_card.setAttribute("data-id", card.id);
    new_card.innerHTML = `

  <header>
    <h2><a href="cards/${card.id}">${card.name}</a></h2>
    <a href="#" class="delete">&#10761;</a>
  </header>
  <ul></ul>
  <form class="new_item">
    <input name="description" type="text">
  </form>`;

    let creator = new_card.querySelector("form.new_item");
    creator.addEventListener("submit", sendCreateItemRequest);

    let deleter = new_card.querySelector("header a.delete");
    deleter.addEventListener("click", sendDeleteCardRequest);

    return new_card;
}

function createItem(item) {
    let new_item = document.createElement("li");
    new_item.classList.add("item");
    new_item.setAttribute("data-id", item.id);
    new_item.innerHTML = `
  <label>
    <input type="checkbox"> <span>${item.description}</span><a href="#" class="delete">&#10761;</a>
  </label>
  `;

    new_item
        .querySelector("input")
        .addEventListener("change", sendItemUpdateRequest);
    new_item
        .querySelector("a.delete")
        .addEventListener("click", sendDeleteItemRequest);

    return new_item;
}

addEventListeners();

/* COUNTDOWN TIMER EVENT PAGE */

/* Login Page */

const loginForm = document.querySelector("form.login");

const signupForm = document.querySelector("form.signup");

const loginBtn = document.querySelector("label.login");

const signupBtn = document.querySelector("label.signup");

const signupLink = document.querySelector(".signup-link a");

const loginText = document.querySelector(".title-text .login");

const signupText = document.querySelector(".title-text .signup");


if (document.location.pathname === "/login") {
signupBtn.onclick = () => {
    loginForm.style.marginLeft = "-50%";
    loginText.style.marginLeft = "-50%";
  };

  loginBtn.onclick = () => {
    loginForm.style.marginLeft = "0%";
    loginText.style.marginLeft = "0%";
  };

  signupLink.onclick = () => {
    signupBtn.click();
  };
}


function createInvite(event_id) {
    let invited_user = document.getElementById("sendInvite").value;
    sendAjaxRequest(
        "post",
        "/api/invite",
        {
            invited_user: invited_user,
            event_id: event_id
        }, function(response) {
            inviteHandler(response);
        }
    );
}

function inviteHandler(response) {

    if (this.status === 302) {
        window.location.href = this.responseText;
    }
    else if (this.status === 200) {
        let d = document.getElementById('inviteDiv');
        d.classList.add("animate-out");
        setTimeout(function () {
            d.classList.remove("animate-out");
        }, 500);
        setTimeout(function () {
            d.style.display = "none";
        }, 450);

    }
    else if (this.status === 404) {
        console.log("Invite Doesn't Exist");
    }
    else if (this.status === 409) {
        Swal.fire({
            title: 'Error!',
            text: 'User is already invited!',
            icon: 'warning',
            confirmButtonText: 'Continue'
        })
    }
    else if (this.status === 400) {
        Swal.fire({
            title: 'Error!',
            text: 'You cannot invite yourself!',
            icon: 'warning',
            confirmButtonText: 'Continue'
        })
    }
    else if (response.status === 412) {
        Swal.fire({
            title: 'Error!',
            text: 'User is already in this event!',
            icon: 'warning',
            confirmButtonText: 'Continue'
        })
    }
    else if (this.status === 403) {
        Swal.fire({
            title: 'Error!',
            text: 'User is blocked!',
            icon: 'warning',
            confirmButtonText: 'Continue'
        })
    }
}

function rejectInvite(eventID) {
    // console.log("reject");
    sendAjaxRequest("delete", "/api/inviteReject", { event_id: eventID }, inviteHandler(response));
}

function acceptInvite(event_id) {
    // console.log("accept");
    sendAjaxRequest(
        "put",
        "/api/inviteAccept",
        { event_id: event_id },
        inviteHandler,
    );

}

const button = document.querySelector("#event-content button");

function showAlert(type) {
    let myAlert = document.getElementById("myAlert");
    let alertText = document.querySelector(".myAlert-message");
    if (type == "enroll") {
        alertText.innerHTML = "You successfully joined the Event";
        myAlert.querySelector("img").src = "../icons/accept.png";
    }
    else if (type == "leave") {
        alertText.innerHTML = "You left the Event, sad to see you go!";
        myAlert.querySelector("img").src = "../icons/unaccept.png";
    }
    else if (type == "newcomment") {
        alertText.innerHTML = "You successfully posted a comment";
        myAlert.querySelector("img").src = "../icons/accept.png";
    }
    else if (type == "newReport") {
        alertText.innerHTML = "Your report was successfully submitted!";
        myAlert.querySelector("img").src = "../icons/accept.png";
    }


    move();

    myAlert.className = "show";

    setTimeout(function () { hideAlert(); }, 5000);
}

function hideAlert() {
    myAlert.className = myAlert.className.replace("show", "");
}

var i = 0;
function move() {
    if (i == 0) {
        var elem = document.getElementById("myAlertBar");
        var width = 1;
        var interval = setInterval(frame, 50);
        function frame() {
            if (width >= 100) {
                clearInterval(interval);
                interval = 0;
            } else {
                width++;
                elem.style.width = width + "%";
            }
        }
    }
}

function blockHandler(response, userid) {
    if (response.status == 200) {
        document.getElementById("blockStatus" + userid).innerHTML = response.response;
    }
}

function changeBlockStatusUser(userid, blockStatus) {
    sendAjaxRequest("put", "/api/changeBlockStatus", { userID: userid, blockStatus: blockStatus }, function(response) {
        blockHandler(response, userid);
    });
}



// EVENT PAGE COUNTDOWN TIMER
function displayCountdownEvent(info) {

    let split_ = info.split(" ");
    let date_ = split_[0].split("-");

    let year = date_[0].slice(-2);
    let month = date_[2];
    let day = date_[1];

    let string = day + " " + month + " " + year + " " + split_[1];
    const newDate = Date.parse(string);

    const countdown = setInterval(() => {
        const date = new Date().getTime();
        const diff = newDate - date;

        const month = Math.floor(
            (diff % (1000 * 60 * 60 * 24 * (365.25 / 12) * 365)) /
            (1000 * 60 * 60 * 24 * (365.25 / 12))
        );
        const days = Math.floor(
            (diff % (1000 * 60 * 60 * 24 * (365.25 / 12))) / (1000 * 60 * 60 * 24)
        );
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        document.querySelector(".seconds").innerHTML =
            seconds < 10 ? "0" + seconds : seconds;
        document.querySelector(".minutes").innerHTML =
            minutes < 10 ? "0" + minutes : minutes;
        document.querySelector(".hours").innerHTML =
            hours < 10 ? "0" + hours : hours;
        document.querySelector(".days").innerHTML = days < 10 ? "0" + days : days;
        document.querySelector(".months").innerHTML =
            month < 10 ? "0" + month : month;
    });

}
