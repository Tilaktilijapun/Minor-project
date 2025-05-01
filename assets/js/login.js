document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    const username = document.getElementById('username');
    const password = document.getElementById('password');
    const togglePassword = document.getElementById('toggle-password');
    const loadingSpinner = document.getElementById('loading');
  
    togglePassword.addEventListener('click', () => {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      togglePassword.textContent = type === 'password' ? '👁️' : '🙈';
    });
  
    form.addEventListener('submit', (event) => {
      let valid = true;
      document.querySelectorAll('.error-message').forEach(el => el.remove());
      username.classList.remove('input-error');
      password.classList.remove('input-error');
  
      if (username.value.trim() === '') {
        showError(username, 'Username is required.');
        valid = false;
      }
  
      if (password.value.trim() === '') {
        showError(password, 'Password is required.');
        valid = false;
      }
  
      if (!valid) {
        event.preventDefault();
        return;
      }
  
      loadingSpinner.classList.remove('hidden');
    });
  
    function showError(input, message) {
      const error = document.createElement('div');
      error.textContent = message;
      error.style.color = 'red';
      error.style.marginTop = '5px';
      input.parentElement.appendChild(error);
      input.classList.add('input-error');
    }
  });
  