document.getElementById("search-users").addEventListener("keyup", function (e) {
    if (document.getElementById("search-users").value == '') return;
    let eventid = document.getElementById("eventid").value;
    fetch("searchUsers" + "?" + new URLSearchParams({
        search: document.getElementById("search-users").value,
        event_id: eventid
    }), {
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-Token": '{{ csrf_token() }}'
        },
        method: "get",
        credentials: "same-origin",

    }).then(function (data) {
        return data.json();
    }).then(function (data) {
        let container = document.getElementById("table-user-res");
        container.innerHTML = "";
        data.forEach(function (user) {

            let tr = document.createElement("tr");
            let td1 = document.createElement("td");
            let td2 = document.createElement("td");
            let td3 = document.createElement("td");

            td3.style.textAlign = "center";
            let btn = document.createElement("button");

            if (user.attending_event == true) {
                btn.setAttribute("class", "btn btn-danger");
                btn.innerHTML = "Remove";
                btn.addEventListener('click', function (e) {
                    ajax_remUser(user.userid, eventid);
                    refreshDiv();
                })
            } else {
                btn.setAttribute("class", "btn btn-success");
                btn.innerHTML = "Add to Event";
                btn.addEventListener('click', function (e) {
                    ajax_addUser(user.userid, eventid);
                    refreshDiv();
                })
            }
            td1.innerHTML = user.name;
            td2.innerHTML = user.email;
            td3.appendChild(btn);

            tr.appendChild(td1);
            tr.appendChild(td2);
            tr.appendChild(td3);
            container.appendChild(tr);

        });
    }).catch(function (error) {
        console.log(error);
    });
});

document.querySelector('#outroDiv button').addEventListener('click', function () {
    let d = document.getElementById('outroDiv');
    d.classList.add("animate-out");
    setTimeout(function () {
        d.classList.remove("animate-out");
    }, 500);
    setTimeout(function () {
        d.style.display = "none";
    }, 450);
})

function showOutroDiv() {
    document.getElementById("info-navbar-container").querySelectorAll('#info-navbar-container > div').forEach(n => n.style.display = 'none');
    let d = document.getElementById('outroDiv');
    d.classList.add("animate");
    setTimeout(function () {
        d.classList.remove("animate");
    }, 500);
    d.style.display = "block";
}
