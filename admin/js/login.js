document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('form');
    const togglePassword = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');

    // Handle form submission
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('/minor project/admin/login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            try {
                const result = JSON.parse(data);
                if (result.success) {
                    window.location.href = '/minor project/admin/dashboard.php';
                } else {
                    showError(result.message || 'Login failed');
                }
            } catch (e) {
                // If response is not JSON, check if we were redirected
                if (data.includes('/minor project/admin/dashboard.php')) {
                    window.location.href = '/minor project/admin/dashboard.php';
                } else {
                    showError('Login Successful.');
                }
            }
        })
        .catch(error => {
            showError('An error occurred. Please try again.');
        });
    });

    // Toggle password visibility
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
    });

    // Show error message
    function showError(message) {
        let errorDiv = document.querySelector('.error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('p');
            errorDiv.className = 'error-message';
            loginForm.insertBefore(errorDiv, loginForm.firstChild);
        }
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
});