let form_del_acc = document.getElementById("del_acc_modal").querySelector("form");

form_del_acc.addEventListener("submit", (event) => {
    event.preventDefault();
    fetch("deleteUser", {
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        method: "post",
        credentials: "same-origin",
        body: JSON.stringify({
            userid: document.querySelector('meta[name="auth-check-id"]').getAttribute('content')
        })
    }).then((response) => {
        if (response.status === 200) {
            window.location.href = "/home";
        }
        else if (response.status === 401) {

            // close bootstrap 5 modal with vanilla js
            document.getElementById("del_acc_modal").querySelector(".btn.btn-secondary").click();

            Swal.fire({
                title: 'Error!',
                text: 'You are still hosting events. Please delete or transfer their ownership first.',
                icon: 'warning',
                confirmButtonText: 'Continue'
            })
        }
    });
});


// var trs = document.getElementById('eventsCreatedByMe').getElementsByTagName('tr');
// console.log(trs)

// for (var i = 0; i < trs.length; i++) {
//     trs[i].addEventListener('click', function (e) {
//         let t = e.target.parentNode;
//         let id = t.getAttribute('id');
//         console.log(id)
//         window.location.href = '/event' + id;
//     })
// };

function getReports() {
    fetch("getReportedComments", {
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-Token": '{{csrf_token()}}'
        },
        method: "get",
        credentials: "same-origin",
    }).then(function (data) {
        return data.json();
    }
    ).then(function (data) {
        if (data.status == 204) {
            Swal.fire({
                title: 'Error!',
                text: 'There are no reported comments.',
                icon: 'warning',
                confirmButtonText: 'Continue'
            })
        }
        else {
            let table = document.getElementById("viewReports").querySelector("table");
            let tbody = table.querySelector("tbody");
            tbody.innerHTML = "";
            for (let i = 0; i < data.length; i++) {
                let tr = document.createElement("tr");
                tr.setAttribute("id", data[i].commentid);
                let td1 = document.createElement("td");
                let td2 = document.createElement("td");
                let td3 = document.createElement("td");
                let td4 = document.createElement("td");

                td1.innerHTML = data[i].date;
                td2.innerHTML = data[i].user.name;
                td3.innerHTML = data[i].reason;
                td4.innerHTML = data[i].description;

                tr.appendChild(td1);
                tr.appendChild(td2);
                tr.appendChild(td3);
                tr.appendChild(td4);

                tbody.appendChild(tr);

                tr.addEventListener('click', function (e) {
                    getSingleComment(data[i]);
                })
            }
        }
    }).catch(function (error) {
        console.log(error);
    }
    );
}

let form_ban = document.getElementById("view_comment_report").querySelector("form");
form_ban.addEventListener("submit", function (e) {

    e.preventDefault();
    let form = e.target;
    let userid = form.querySelector("input[name='__rep_user_id']").value;

    if (userid == 0) return;

    fetch("banUser", {
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        method: "post",
        credentials: "same-origin",
        body: JSON.stringify({
            userID: userid
        })
    }).then(function (data) {
        return data.json();
    }
    ).then(function (data) {
        if (data.status == 200) {
            Swal.fire({
                title: 'Success!',
                text: 'User banned successfully.',
                icon: 'success',
                confirmButtonText: 'Continue'
            })
        }
        else if (data.status == 401){
            Swal.fire({
                title: 'Error!',
                text: 'User is already banned.',
                icon: 'error',
                confirmButtonText: 'Continue'
            })
        }
        else if (data.status == 403) {
            Swal.fire({
                title: 'Error!',
                text: 'You cannot ban yourself.',
                icon: 'error',
                confirmButtonText: 'Continue'
            })
        }

        getReports();
        document.getElementById("view_comment_report").querySelector(".close").click();

    }).catch(function (error) {
        console.log(error);
    }
    );
});


function change_view_comment_report(comment, report) {
    let modal = document.getElementById("view_comment_report");
    modal.querySelector("img").setAttribute("src", comment.user_profilePic);
    modal.querySelector("h4.name").innerHTML = comment.user_name;
    modal.querySelector("p.text").innerHTML = comment.text;
    modal.querySelector("li.rep_date").innerHTML = "Date: " + report.date;
    modal.querySelector("li.rep_reason").innerHTML = "Reason: " + report.reason;
    modal.querySelector("li.rep_description").innerHTML = "Description: " + report.description;
    modal.querySelector("input[name='__rep_user_id").setAttribute("value", report.userid);

    document.querySelector("#viewReports").querySelector("button").click();
}

function getSingleComment(report) {

    fetch('getSingleComment' + "?" + new URLSearchParams({
        comment_id: report.commentid
    }), {
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-Token": '{{csrf_token()}}'
        },
        method: "get",
        credentials: "same-origin",
    }).then(function (data) {
        return data.json();
    }).then(function (data) {
        change_view_comment_report(data, report);
    }).catch(function (error) {
        console.log(error);
    })
}

document.querySelector("#viewReportsOption").addEventListener("click", function (e) {
    getReports();
});


const selectOption = function (option) {

    Array.from(document.getElementsByClassName('option')).forEach((element) => {
        element.classList.remove('optionSelected');
    });

    Array.from(document.getElementsByClassName('optionDetails')).forEach((element) => {
        element.classList.add('optionDetailsHidden');
    });

    Array.from(document.getElementsByClassName('submenuActive')).forEach((element) => {
        element.classList.remove('submenuActive');
        element.classList.add('submenuSleep');
    });

    Array.from(document.getElementsByClassName('subOption')).forEach((element) => {
        element.classList.remove('optionSelected');
    });


    switch (option) {
        case 1: {

            document.getElementById('myProfileOption').classList.add('optionSelected');
            document.getElementById('myProfileDetails').classList.remove('optionDetailsHidden');

            break;
        }
        case 2: {

            document.getElementById('myEventsOption').classList.add('optionSelected');
            document.getElementById('myEventsDetails').classList.remove('optionDetailsHidden');

            /*ativar o submenu*/
            document.getElementById('myEventsSubmenu').classList.add('submenuActive');
            document.getElementById('myEventsSubmenu').classList.remove('submenuSleep');

            break;
        }
        case 3: {
            document.getElementById('myInvitesOption').classList.add('optionSelected');
            document.getElementById('myInvitesDetails').classList.remove('optionDetailsHidden');

            /*ativar o submenu*/
            document.getElementById('myInvitesSubmenu').classList.add('submenuActive');
            document.getElementById('myInvitesSubmenu').classList.remove('submenuSleep');

            /*predefenir o received Invites*/
            document.getElementById('receivedInvites').classList.add('submenuActive');
            document.getElementById('receivedInvites').classList.remove('submenuSleep');

            document.getElementById('receivedInvitesOption').classList.add('optionSelected');

            document.getElementById("notification_text").innerHTML = "";

            sendAjaxRequest("put", "/api/clearNotifications", null, readHandler);



            break;
        }

        case 4: {
            document.getElementById('usersSearchOption').classList.add('optionSelected');
            document.getElementById('userSearch').classList.remove('submenuSleep');
            document.getElementById('userSearch').classList.add('submenuActive');

            break;
        }

        case 5: {
            document.getElementById('viewReportsOption').classList.add('optionSelected');
            document.getElementById('viewReports').classList.remove('submenuSleep');
            document.getElementById('viewReports').classList.add('submenuActive');
        }

    }

}

const showDetails = function (option) {

    Array.from(document.getElementsByClassName('details')).forEach((element) => {
        element.classList.remove('optionSelected');
        element.classList.add('submenuSleep');
        element.classList.remove('submenuActive');

    });

    Array.from(document.getElementsByClassName('subOption')).forEach((element) => {
        element.classList.remove('optionSelected');
    });

    switch (option) {
        case 1: {
            document.getElementById('pastEvents').classList.add('submenuActive');
            document.getElementById('pastEvents').classList.remove('submenuSleep');
            document.getElementById('pastEventsOption').classList.add('optionSelected');
            break;
        }
        case 2: {

            document.getElementById('futureEvents').classList.add('submenuActive');
            document.getElementById('futureEvents').classList.remove('submenuSleep');
            document.getElementById('futureEventsOption').classList.add('optionSelected');

            break;
        }
        case 3: {

            document.getElementById('eventsCreatedByMe').classList.add('submenuActive');
            document.getElementById('eventsCreatedByMe').classList.remove('submenuSleep');
            document.getElementById('eventsCreatedByMeOption').classList.add('optionSelected');


            break;
        }
        case 4: {

            document.getElementById('receivedInvites').classList.add('submenuActive');
            document.getElementById('receivedInvites').classList.remove('submenuSleep');
            document.getElementById('receivedInvitesOption').classList.add('optionSelected');

            break;
        }
        case 5: {

            document.getElementById('sentInvites').classList.add('submenuActive');
            document.getElementById('sentInvites').classList.remove('submenuSleep');
            document.getElementById('sentInvitesOption').classList.add('optionSelected');

            break;
        }
    }
}

/** MODAL EVENT EDIT */

function handleNameChange(id) {
    const modal = document.getElementById('editModal' + id);
    modal.querySelector('#name').addEventListener("keyup", (event) => {
        modal.querySelector('#preview-name').innerHTML = event.target.value;
    });
}

function handleCapacityChange(id) {
    const modal = document.getElementById('editModal' + id);
    modal.querySelector('#capacity').addEventListener("keyup", (event) => {
        modal.querySelector('#preview-capacity').innerHTML = event.target.value + ' people';
    });
}

function handleLocationChange(id) {
    const modal = document.getElementById('editModal' + id);

    modal.querySelector('#city').addEventListener("keyup", (event) => {
        modal.querySelector('#preview-location').innerHTML = modal.querySelector('#country').value + ', ' + event.target.value;
    });

    modal.querySelector('#country').addEventListener("keyup", (event) => {
        modal.querySelector('#preview-location').innerHTML = event.target.value + ', ' + modal.querySelector('#city').value;
    });
}

function handleAddressChange(id) {
    const modal = document.getElementById('editModal' + id);
    modal.querySelector('#address').addEventListener("keyup", (event) => {
        modal.querySelector('#preview-address').innerHTML = event.target.value;
    });
}

/* CREATE MODAL HANDLING */
const c_modal = document.getElementById('createEventModal');

c_modal.querySelector('#name').addEventListener("keyup", (event) => {
    c_modal.querySelector('#preview-name').innerHTML = event.target.value;
});

c_modal.querySelector('#capacity').addEventListener("keyup", (event) => {
    c_modal.querySelector('#preview-capacity').innerHTML = event.target.value + ' people';
});

c_modal.querySelector('#city').addEventListener("keyup", (event) => {
    c_modal.querySelector('#preview-location').innerHTML = c_modal.querySelector('#country').value + ', ' + event.target.value;
});

c_modal.querySelector('#country').addEventListener("keyup", (event) => {
    c_modal.querySelector('#preview-location').innerHTML = event.target.value + ', ' + c_modal.querySelector('#city').value;
});

c_modal.querySelector('#address').addEventListener("keyup", (event) => {
    c_modal.querySelector('#preview-address').innerHTML = event.target.value;
});

function preview_image() {
    c_modal.querySelector("#preview-image").src = URL.createObjectURL(event.target.files[0]);
}

function readHandler() {
    // console.log("result: ", this, this.responseText);
}

// document.getElementById("search-attendees-teste").addEventListener("keyup", function (e) {
//     fetch("searchUsersAdmin" + "?" + new URLSearchParams({
//         search: e.target.value
//     }), {
//         headers: {
//             "Content-Type": "application/json",
//             "Accept": "application/json",
//             "X-Requested-With": "XMLHttpRequest",
//             "X-CSRF-Token": '{{csrf_token()}}'
//         },
//         method: "get",
//         credentials: "same-origin",
//     }).then(function (data) {
//         return data.json();
//     }).then(function (data) {
//         let container = document.getElementById("search-attendees-response");
//         container.innerHTML = "";
//         data.forEach(function (user) {
//             let row = document.createElement("tr");
//             let name = document.createElement("td");
//             let email = document.createElement("td");
//             let link = document.createElement("a");
//             link.href = "/user/" + user.id;
//             link.classList.add("link-dark");
//             link.innerHTML = user.name;
//             name.appendChild(link);
//             email.innerHTML = user.email;
//             row.appendChild(name);
//             row.appendChild(email);
//             container.appendChild(row);
//         });

//     }).catch(function (error) {
//         console.log(error);
//     });
// });


// VALIDATION FOR EVENT CREATE FORM

let createEventModal = document.getElementById('createEventModal');
let formEvent = createEventModal.querySelector('form');
// get elements that their ID contains the string editModal
let editEventModals = document.querySelectorAll('[id*="editModal"]');

editEventModals.forEach(function (editEventModal) {
    let editEventForm = editEventModal.querySelector('form');

    editEventForm.addEventListener('submit', function (e) {
        e.preventDefault();

        let e_name = editEventForm.querySelector('#name');
        let e_desc = editEventForm.querySelector('#description');
        let e_date = editEventForm.querySelector('#date');
        let e_capacity = editEventForm.querySelector('#capacity');
        let e_city = editEventForm.querySelector('#city');
        let e_country = editEventForm.querySelector('#country');
        let e_price = editEventForm.querySelector('#price');
        let e_address = editEventForm.querySelector('#address');
        let e_tag = editEventForm.querySelector('#tag');
        let e_image = editEventForm.querySelector('#img');

        let val_e_name = validateEventName(e_name);
        let val_e_desc = validateEventDesc(e_desc);
        let val_e_date = validateEventDate(e_date);
        let val_e_capacity = validateEventCapacity(e_capacity);
        let val_e_city = validateEventCity(e_city);
        let val_e_country = validateEventCountry(e_country);
        let val_e_price = validateEventPrice(e_price);
        let val_e_address = validateEventAddress(e_address);
        let val_e_tag = validateEventTag(e_tag);


        if (val_e_name && val_e_desc && val_e_date && val_e_capacity && val_e_city && val_e_country && val_e_price && val_e_address && val_e_tag) {
            editEventForm.submit();
        }

    });
});

formEvent.addEventListener('submit', function (e) {
    e.preventDefault();

    let e_name = formEvent.querySelector('#name');
    let e_desc = formEvent.querySelector('#description');
    let e_date = formEvent.querySelector('#date');
    let e_capacity = formEvent.querySelector('#capacity');
    let e_city = formEvent.querySelector('#city');
    let e_country = formEvent.querySelector('#country');
    let e_price = formEvent.querySelector('#price');
    let e_address = formEvent.querySelector('#address');
    let e_tag = formEvent.querySelector('#tag');
    let e_image = formEvent.querySelector('#img');

    let val_e_name = validateEventName(e_name);
    let val_e_desc = validateEventDesc(e_desc);
    let val_e_date = validateEventDate(e_date);
    let val_e_capacity = validateEventCapacity(e_capacity);
    let val_e_city = validateEventCity(e_city);
    let val_e_country = validateEventCountry(e_country);
    let val_e_price = validateEventPrice(e_price);
    let val_e_address = validateEventAddress(e_address);
    let val_e_tag = validateEventTag(e_tag);
    let val_e_image = validateEventPhoto(e_image);

    if (val_e_name && val_e_desc && val_e_date && val_e_capacity && val_e_city && val_e_country && val_e_price && val_e_address && val_e_tag && val_e_image) {
        formEvent.submit();
    }
});


function validateEventName(name) {
if (name.value.length == 0) {
    displayError(name, 'Field name cannot be empty');
    return false;
}
else if (name.value.length < 8 || name.value.length > 25) {
    displayError(name, 'Name must be at least 8 characters long and no more than 25 characters');
    return false;
} /* check if the first character of name is uppercase */
else if (!/^[A-Z]/.test(name.value)) {
    displayError(name, 'Name must start with an uppercase letter');
    return false;
} else {
    name.classList.remove('is-invalid');
    name.classList.add('is-valid');
    return true;
}
}

function validateEventDesc(desc) {
if (desc.value.length == 0) {
    displayError(desc, 'Field name cannot be empty');
    return false;
}
else if (desc.value.length < 15 || desc.value.length > 100) {
    displayError(desc, 'Name must be at least 15 characters long and no more than 100 characters');
    return false;
}
else {
    desc.classList.remove('is-invalid');
    desc.classList.add('is-valid');
    return true;
}
}

function validateEventDate(date) {
if (date.value === '') {
    displayError(date, 'Date is required');
    return false;
}
else if (new Date(date.value) < new Date()) {
    displayError(date, 'Do you wanna travel back in time? ;)');
    return false;
} else {
    date.classList.remove('is-invalid');
    date.classList.add('is-valid');
    return true;
}
}

function validateEventCapacity(capacity) {
if (capacity.value === '') {
    displayError(capacity, 'Capacity is required');
    return false;
} else if (capacity.value < 5 || capacity.value > 100000) {
    displayError(capacity, 'Capacity must be greater than 5 and less than 100000');
    return false;
} else {
    capacity.classList.remove('is-invalid');
    capacity.classList.add('is-valid');
    return true;
}
}


function validateEventCity(city) {
if (city.value === '') {
    displayError(city, 'City is required');
    return false;
} /* REGEX FOR CITY NAME */
else if (!/^[a-zA-Z]+(?:[\s-][a-zA-Z]+)*$/.test(city.value)) {
    displayError(city, 'Please enter a valid city name');
    return false;
} else {
    city.classList.remove('is-invalid');
    city.classList.add('is-valid');
    return true;
}
}

function validateEventCountry(country) {
if (country.value === '') {
    displayError(country, 'Country is required');
    return false;
} else if (!/^[a-zA-Z]+(?:[\s-][a-zA-Z]+)*$/.test(country.value)) {
    displayError(country, 'Please enter a valid country name');
    return false;
} else {
    country.classList.remove('is-invalid');
    country.classList.add('is-valid');
    return true;
}
}

function validateEventPrice(price) {
if (price.value === '') {
    displayError(price, 'Price is required');
    return false;
} else if (price.value < 0 || price.value > 100000) {
    displayError(price, 'Price must be greater than 0 and less than 100000');
    return false;
} else {
    price.classList.remove('is-invalid');
    price.classList.add('is-valid');
    return true;
}
}

function validateEventAddress(address) {
if (address.value === '') {
    displayError(address, 'Address is required');
    return false;
}
else if (!/^[a-zA-Z0-9\s,'-]*$/.test(address.value)) {
    displayError(address, 'Please enter a valid address');
    return false;
}
else {
    address.classList.remove('is-invalid');
    address.classList.add('is-valid');
    return true;
}
}

function validateEventTag(tag) {
if (tag.value === '') {
    displayError(tag, 'Tag is required');
    return false;
}
else if (!/^(sports|music|family|tech)$/.test(tag.value)) {
    displayError(tag, 'Please choose a valid tag');
    return false;
}
else {
    tag.classList.remove('is-invalid');
    tag.classList.add('is-valid');
    return true;
}
}

function validateEventPhoto(photoFile) {
if (photoFile.value === '') {
    displayError(photoFile, 'Photo is required');
    return false;
} /* validate if photo format is good */
else if (!/(\.jpg|\.jpeg|\.png|\.gif)$/i.test(photoFile.value)) {
    displayError(photoFile, 'Please choose a valid photo format');
    return false;
}
else {
    photoFile.classList.remove('is-invalid');
    photoFile.classList.add('is-valid');
    return true;
}
}






