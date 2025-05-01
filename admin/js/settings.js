// Form validation and dynamic UI handling
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const validateForm = (formElement) => {
        let isValid = true;
        const inputs = formElement.querySelectorAll('input[required], select[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('error');
            } else {
                input.classList.remove('error');
            }
        });
        
        return isValid;
    };

    // Handle site info form submission
    const siteInfoForm = document.querySelector('#site-info form');
    if (siteInfoForm) {
        siteInfoForm.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showNotification('Please fill in all required fields', 'error');
            }
        });
    }

    // Handle configuration form submission
    const configForm = document.querySelector('#config form');
    if (configForm) {
        configForm.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showNotification('Please fill in all required fields', 'error');
            }
        });
    }

    // Logo preview functionality
    const logoInput = document.getElementById('site_logo');
    if (logoInput) {
        logoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    showNotification('Please select a valid image file (JPG, PNG, or GIF)', 'error');
                    this.value = '';
                    return;
                }

                // Validate file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showNotification('Image size should be less than 2MB', 'error');
                    this.value = '';
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.preview-image') || document.createElement('img');
                    preview.src = e.target.result;
                    preview.classList.add('preview-image');
                    if (!document.querySelector('.preview-image')) {
                        document.querySelector('.file-upload').appendChild(preview);
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Navigation handling
    const navLinks = document.querySelectorAll('.settings-nav .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                
                // Update active state
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                // Show corresponding section
                const targetId = this.getAttribute('href').substring(1);
                document.querySelectorAll('.settings-section').forEach(section => {
                    section.style.display = section.id === targetId ? 'block' : 'none';
                });
            }
        });
    });

    // Notification system
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `message ${type}-message`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
            ${message}
        `;
        
        document.querySelector('.settings-container').insertBefore(
            notification,
            document.querySelector('.settings-nav')
        );

        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    // Handle number input validation
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            const value = parseFloat(this.value);
            const min = parseFloat(this.min);
            const max = parseFloat(this.max);

            if (value < min) this.value = min;
            if (max && value > max) this.value = max;
        });
    });
});