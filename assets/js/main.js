// Main JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Cart functionality
    const cartButtons = document.querySelectorAll('.add-to-cart');
    cartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            addToCart(productId);
        });
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
});

// Cart functions
function addToCart(productId) {
    fetch('/minor project/cart/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            showNotification('Product added to cart', 'success');
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateCartCount() {
    const cartCount = document.querySelector('.cart-count');
    fetch('/minor project/cart/get-cart-count.php')
        .then(response => response.json())
        .then(data => {
            if (cartCount) {
                cartCount.textContent = data.count;
            }
        });
}

// Utility functions
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('error');
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}