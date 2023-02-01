// pooling every 1 second
setInterval(function () {
    displayCountdownHome();
}, 1000);



function displayCountdownHome() {

    let date = document.querySelector('.carousel-item.active input[name="event-date"]').value;

    let split_ = date.split(" ");
    let date_ = split_[0].split("-");

    let year = date_[0].slice(-2);
    let month = date_[2];
    let day = date_[1];

    let string = day + " " + month + " " + year + " " + split_[1];
    // console.log(string);
    const newDate = Date.parse(string);
    // console.log(newDate);

    const countdown = setInterval(() => {
        clearInterval(countdown);
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
    }, 1000);
}
