document.querySelectorAll('.add-to-cart-btn').forEach(button => {
    button.addEventListener('click', function(event) {
        event.preventDefault();
        event.stopPropagation();

        const productId = this.getAttribute('data-id');
        const quantity = this.closest('.product-card').querySelector('.quantity-input')?.value || 1;
        const button = this;

        // Change button state to loading
        button.disabled = true;
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

        fetch('/minor project/cart/add-to-cart.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart icon count
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = data.cartCount;
                    cartCount.classList.add('bounce');
                    setTimeout(() => cartCount.classList.remove('bounce'), 1000);
                }

                // Show success notification
                showNotification('success', data.message);
                
                // Update button state
                button.innerHTML = '<i class="fas fa-check"></i> Added';
                setTimeout(() => {
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }, 2000);

                // Update mini cart if exists
                updateMiniCart();
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            // Show error notification
            showNotification('error', error.message || 'Failed to add item to cart');
            
            // Reset button state
            button.innerHTML = originalContent;
            button.disabled = false;
        });
    });
});

// Quantity input handling
document.querySelectorAll('.quantity-control').forEach(control => {
    const input = control.querySelector('.quantity-input');
    const minusBtn = control.querySelector('.quantity-minus');
    const plusBtn = control.querySelector('.quantity-plus');

    minusBtn?.addEventListener('click', () => updateQuantity(input, -1));
    plusBtn?.addEventListener('click', () => updateQuantity(input, 1));
    
    input?.addEventListener('change', () => {
        let value = parseInt(input.value);
        const min = parseInt(input.getAttribute('min')) || 1;
        const max = parseInt(input.getAttribute('max')) || 99;
        
        if (isNaN(value) || value < min) value = min;
        if (value > max) value = max;
        
        input.value = value;
    });
});

function updateQuantity(input, change) {
    let value = parseInt(input.value) + change;
    const min = parseInt(input.getAttribute('min')) || 1;
    const max = parseInt(input.getAttribute('max')) || 99;
    
    if (value < min) value = min;
    if (value > max) value = max;
    
    input.value = value;
}

function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Animate notification
    setTimeout(() => notification.classList.add('show'), 100);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function updateMiniCart() {
    const miniCart = document.querySelector('.mini-cart');
    if (!miniCart) return;

    fetch('/minor project/cart/get-mini-cart.php')
        .then(response => response.text())
        .then(html => {
            miniCart.innerHTML = html;
            miniCart.classList.add('updated');
            setTimeout(() => miniCart.classList.remove('updated'), 1000);
        });
}

// Prevent form submission
document.querySelectorAll('.add-to-cart-form').forEach(form => {
    form.addEventListener('submit', event => event.preventDefault());
});
