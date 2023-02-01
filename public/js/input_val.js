// GENERIC FUNCTION TO DISPLAY ERROR MESSAGE
function displayError(element, message) {

    element.classList.add('is-invalid');
    element.nextElementSibling.innerHTML = message;
}

// VALIDATION OF REGISTER FORM IN LOGIN/REGISTER PAGE

let registerForm = document.getElementById('__registerUserForm');

if (window.location.href.includes('login')) {

    registerForm.addEventListener('submit', function (e) {
        e.preventDefault();

        let name = registerForm.querySelector('input[name="name"]');
        let email = registerForm.querySelector('input[name="email"]');
        let birthdate = registerForm.querySelector('input[name="birthdate"]');
        let password = registerForm.querySelector('input[name="password"]');
        let password_confirmation = registerForm.querySelector('input[name="password_confirmation"]');
        let photo = registerForm.querySelector('input[name="profilePic"]');

        let validName = validateName(name);
        let validEmail = validateEmail(email);
        let validBirthdate = validateBirthdate(birthdate);
        let validPassword = validatePassword(password);
        let validPasswordConfirmation = validatePasswordConfirmation(password, password_confirmation);
        let validPhoto = validatePhoto(photo);

        if (validName && validEmail && validBirthdate && validPassword && validPasswordConfirmation && validPhoto) {
            registerForm.submit();
        }
    });
}



// VALIDATION FOR USER EDIT FORM

let edit_userProfilePorm = document.getElementById('profileDetailsForm');

if (window.location.href.includes('user')) {

    edit_userProfilePorm.addEventListener('submit', function (e) {
        e.preventDefault();

        let name = edit_userProfilePorm.querySelector('input[name="name"]');
        let email = edit_userProfilePorm.querySelector('input[name="email"]');
        let birthdate = edit_userProfilePorm.querySelector('input[name="birthdate"]');
        let password = edit_userProfilePorm.querySelector('input[name="password"]');
        let photo = edit_userProfilePorm.querySelector('input[name="profilePic"]');

        let validName = validateName(name);
        let validEmail = validateEmail(email);
        let validBirthdate = validateBirthdate(birthdate);
        let validPassword = validatePassword(password);
        let validPhoto = validatePhoto(photo);

        if (validName && validEmail && validBirthdate && validPassword && validPhoto) {
            edit_userProfilePorm.submit();
        }
    });

    // VALIDATION FOR USER CREATE FORM

    let form = document.getElementById('_formCreateUser');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        let name = form.querySelector('#name');
        let email = form.querySelector('#email');
        let birthdate = form.querySelector('#birthdate');
        let password = form.querySelector('#password');

        /*
        MUST BE CALLED LIKE THIS OTHERWISE THEY ARE ONLY CALLED IN A SEQUENCIAL FASHION:
        MEANING THAT IF NAME AND PASSWORD ARE NOT VALID, ONLY NAME WILL BE DISPLAYED AS INVALID!
        */

        let validName = validateName(name);
        let validEmail = validateEmail(email);
        let validBirthdate = validateBirthdate(birthdate);
        let validPassword = validatePassword(password);

        if (validName && validEmail && validBirthdate && validPassword) {
            form.submit();
        }

    });

}

function validateName(name) {
    if (name.value.length < 8 || name.value.length > 15) {
        displayError(name, 'Name must be at least 8 characters long and no more than 15 characters');
        return false;
    } /* check if name has atleast one uppercase */
    else if (!/[A-Z]/.test(name.value)) {
        displayError(name, 'Name must contain at least one uppercase letter');
        return false;
    } else if (!/[a-z]/.test(name.value)) {
        displayError(name, 'Name must contain at least one lowercase letter');
        return false;
    } else {
        name.classList.remove('is-invalid');
        name.classList.add('is-valid');
        return true;
    }
}

function validateEmail(email) {
    if (email.value === '') {
        displayError(email, 'Email is required');
        return false;
    } else if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/.test(email.value)) {
        displayError(email, 'Please enter a valid email address');
        return false;
    } else {
        email.classList.remove('is-invalid');
        email.classList.add('is-valid');
        return true;
    }
}

function validateBirthdate(birthdate) {
    if (birthdate.value === '') {
        displayError(birthdate, 'Birthdate is required');
        return false;
    } /* check if birthdate value is not greater than current date */
    else if (new Date(birthdate.value) > new Date()) {
        displayError(birthdate, 'Are you from the future? ;)');
        return false;
    } else {
        birthdate.classList.remove('is-invalid');
        birthdate.classList.add('is-valid');
        return true;
    }
}

function validatePassword(password) {
    if (password.value === '') {
        displayError(password, 'Password is required');
        return false;
    } else if (password.value.length < 8) {
        displayError(password, 'Password must be at least 8 characters long');
        return false;
    } else if (!/[A-Z]/.test(password.value)) {
        displayError(password, 'Password must contain at least one uppercase letter');
        return false;
    } else if (!/[a-z]/.test(password.value)) {
        displayError(password, 'Password must contain at least one lowercase letter');
        return false;
    } else if (!/[0-9]/.test(password.value)) {
        displayError(password, 'Password must contain at least one number');
        return false;
    } else if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password.value)) {
        displayError(password, 'Password must contain at least one special character');
        return false;
    } else {
        password.classList.remove('is-invalid');
        password.classList.add('is-valid');
        return true;
    }
}

function validatePasswordConfirmation(password, passwordConfirmation) {
    if (passwordConfirmation.value === '') {
        displayError(passwordConfirmation, 'Password confirmation is required');
        return false;
    } else if (passwordConfirmation.value !== password.value) {
        displayError(passwordConfirmation, 'Password confirmation does not match password');
        return false;
    } else {
        passwordConfirmation.classList.remove('is-invalid');
        passwordConfirmation.classList.add('is-valid');
        return true;
    }
}


function validatePhoto(photo) {

    if (photo.value !== '') {
        if (!/(\.jpg|\.jpeg|\.png|\.gif)$/i.test(photo.value)) {
            displayError(photo, 'Please choose a valid photo format');
            return false;
        }
        else {
            photo.classList.remove('is-invalid');
            photo.classList.add('is-valid');
            return true;
        }
    }

    return true;
}
