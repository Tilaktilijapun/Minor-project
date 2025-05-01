document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');

    form.addEventListener('submit', function(event) {
        if (!validateForm()) {
            event.preventDefault();
        }
    });

    function validateForm() {
        let valid = true;

        resetErrorStyles();

        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        if (password !== confirmPassword) {
            showError('confirm-password', "Passwords do not match!");
            valid = false;
        }

        if (!document.getElementById('terms').checked) {
            showError('terms', "You must agree to the Terms and Conditions.");
            valid = false;
        }

        const email = document.getElementById('email').value;
        if (!validateEmail(email)) {
            showError('email', "Please enter a valid email address.");
            valid = false;
        }

        const phone = document.getElementById('phone').value;
        if (!validatePhone(phone)) {
            showError('phone', "Please enter a valid phone number.");
            valid = false;
        }

        return valid;
    }

    function validateEmail(email) {
        const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return emailPattern.test(email);
    }

    function validatePhone(phone) {
        const phonePattern = /^[0-9]{10}$/;
        return phonePattern.test(phone);
    }

    function showError(inputId, message) {
        const input = document.getElementById(inputId);
        input.classList.add('error');
        const errorMessage = document.createElement('div');
        errorMessage.classList.add('error-message');
        errorMessage.textContent = message;
        input.parentElement.appendChild(errorMessage);
    }

    function resetErrorStyles() {
        const errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach(function(message) {
            message.remove();
        });
        const errorInputs = document.querySelectorAll('.error');
        errorInputs.forEach(function(input) {
            input.classList.remove('error');
        });
    }
});
function validateForm() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    if (password !== confirmPassword) {
        alert("Passwords do not match!");
        return false;
    }

    if (!document.getElementById('terms').checked) {
        alert("You must agree to the Terms and Conditions.");
        return false;
    }

    return true;
}
