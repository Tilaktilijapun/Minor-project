document.addEventListener('DOMContentLoaded', function() {
    // Sort functionality
    const sortSelect = document.getElementById('sort-products');
    const productsGrid = document.querySelector('.products-grid');

    sortSelect.addEventListener('change', function() {
        const products = Array.from(document.querySelectorAll('.product-card'));
        const sortValue = this.value;

        products.sort((a, b) => {
            switch(sortValue) {
                case 'price-asc':
                    return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                case 'price-desc':
                    return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                case 'name-asc':
                    return a.querySelector('h3').textContent.localeCompare(b.querySelector('h3').textContent);
                case 'name-desc':
                    return b.querySelector('h3').textContent.localeCompare(a.querySelector('h3').textContent);
                default:
                    return 0;
            }
        });

        products.forEach(product => productsGrid.appendChild(product));
    });

    // Add to Cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            
            fetch('/minor project/cart/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Product added to cart!', 'success');
                    updateCartCount(data.cartCount);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error adding product to cart', 'error');
            });
        });
    });

    // Notification system
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 300);
        }, 2000);
    }

    // Update cart count in header
    function updateCartCount(count) {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            cartCount.textContent = count;
        }
    }
});