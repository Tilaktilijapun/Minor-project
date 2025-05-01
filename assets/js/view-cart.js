class ViewCartManager {
    constructor() {
        this.csrfToken = document.querySelector('input[name="csrf_token"]').value;
        this.cartItems = new Map();
        this.initializeCart();
        this.bindEvents();
    }

    initializeCart() {
        document.querySelectorAll('.cart-item').forEach(item => {
            const productId = item.dataset.productId;
            this.cartItems.set(productId, {
                element: item,
                quantity: parseInt(item.querySelector('.quantity').textContent),
                price: parseFloat(item.dataset.price)
            });
        });
    }

    bindEvents() {
        // Quantity controls
        document.querySelectorAll('.quantity-control').forEach(control => {
            control.addEventListener('click', (e) => this.handleQuantityChange(e));
        });

        // Remove items
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleRemoveItem(e));
        });

        // Coupon code
        const couponForm = document.getElementById('coupon-form');
        if (couponForm) {
            couponForm.addEventListener('submit', (e) => this.handleCouponSubmit(e));
        }

        // Checkout button
        const checkoutBtn = document.querySelector('.checkout-btn');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', () => this.handleCheckout());
        }
    }

    async handleQuantityChange(event) {
        const btn = event.currentTarget;
        const productId = btn.closest('.cart-item').dataset.productId;
        const isIncrement = btn.classList.contains('increment');
        const quantityElement = btn.parentElement.querySelector('.quantity');
        let currentQuantity = parseInt(quantityElement.textContent);
        let newQuantity = isIncrement ? currentQuantity + 1 : currentQuantity - 1;

        if (newQuantity < 1) return;

        try {
            const response = await this.updateCartQuantity(productId, newQuantity);
            if (response.success) {
                quantityElement.textContent = newQuantity;
                this.updateCartUI(response);
                this.cartItems.get(productId).quantity = newQuantity;
            } else {
                this.showNotification(response.message, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to update quantity', 'error');
        }
    }

    async handleRemoveItem(event) {
        const item = event.currentTarget.closest('.cart-item');
        const productId = item.dataset.productId;

        try {
            const response = await this.removeCartItem(productId);
            if (response.success) {
                item.remove();
                this.cartItems.delete(productId);
                this.updateCartUI(response);
                if (this.cartItems.size === 0) {
                    this.showEmptyCartMessage();
                }
            }
        } catch (error) {
            this.showNotification('Failed to remove item', 'error');
        }
    }

    async handleCouponSubmit(event) {
        event.preventDefault();
        const couponCode = document.getElementById('coupon-code').value;

        try {
            const response = await this.applyCoupon(couponCode);
            if (response.success) {
                this.updateCartUI(response);
                this.showNotification('Coupon applied successfully!', 'success');
            } else {
                this.showNotification(response.message, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to apply coupon', 'error');
        }
    }

    handleCheckout() {
        if (this.cartItems.size === 0) {
            this.showNotification('Your cart is empty', 'error');
            return;
        }
        window.location.href = '/minor project/cart/checkout.php';
    }

    async updateCartQuantity(productId, quantity) {
        return await this.sendRequest('update_quantity', { product_id: productId, quantity });
    }

    async removeCartItem(productId) {
        return await this.sendRequest('remove_item', { product_id: productId });
    }

    async applyCoupon(code) {
        return await this.sendRequest('apply_coupon', { code });
    }

    async sendRequest(action, data) {
        const response = await fetch('/minor project/cart/view-cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                ...data,
                action,
                csrf_token: this.csrfToken
            })
        });
        return await response.json();
    }

    updateCartUI(response) {
        // Update totals
        if (response.new_total !== undefined) {
            document.querySelector('.subtotal').textContent = `Rs. ${response.new_total.toFixed(2)}`;
            const total = response.new_total + 40; // Adding shipping fee
            document.querySelector('.total').textContent = `Rs. ${total.toFixed(2)}`;
        }

        // Update cart count
        if (response.cart_count !== undefined) {
            document.querySelectorAll('.cart-count').forEach(element => {
                element.textContent = response.cart_count;
            });
        }
    }

    showEmptyCartMessage() {
        const cartContainer = document.querySelector('.cart-container');
        cartContainer.innerHTML = `
            <div class="empty-cart-message">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added anything to your cart yet.</p>
                <a href="/minor project/index.php" class="btn">Continue Shopping</a>
            </div>
        `;
    }

    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ViewCartManager();
});