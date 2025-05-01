class ProductManager {
    constructor() {
        this.bindEvents();
    }

    bindEvents() {
        // Add to cart buttons
        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', (e) => this.handleAddToCart(e));
        });

        // Cart icon
        const cartIcon = document.getElementById('cart-icon');
        if (cartIcon) {
            cartIcon.addEventListener('click', () => this.toggleCart());
        }
    }

    async handleAddToCart(event) {
        event.preventDefault();
        const button = event.currentTarget;
        const productId = button.querySelector('input[name="product_id"]').value;

        try {
            const response = await fetch('/minor project/product/product.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `product_id=${productId}`
            });

            const result = await response.json();
            if (result.success) {
                this.updateCartCount(result.cart_count);
                this.showNotification('Product added to cart!', 'success');
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to add product', 'error');
        }
    }

    updateCartCount(count) {
        document.querySelectorAll('.cart-count').forEach(element => {
            element.textContent = count;
        });
    }

    toggleCart() {
        const dropdown = document.getElementById('cart-dropdown');
        dropdown.classList.toggle('active');
    }

    showNotification(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.getElementById('toast-container').appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ProductManager();
});
