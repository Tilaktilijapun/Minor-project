class CartManager {
    constructor() {
        this.initializeEventListeners();
        this.cartItems = new Map();
        this.loadCartItems();
    }

    loadCartItems() {
        document.querySelectorAll('.cart-item').forEach(item => {
            const productId = item.dataset.productId;
            this.cartItems.set(productId, {
                element: item,
                quantity: parseInt(item.querySelector('.quantity').textContent)
            });
        });
    }

    initializeEventListeners() {
        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleQuantityChange(e));
        });

        document.getElementById('deleteSelected')?.addEventListener('click', 
            () => this.deleteSelectedItems());

        document.getElementById('select-all')?.addEventListener('change', 
            (e) => this.toggleSelectAll(e));

        document.querySelectorAll('.item-select input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateSelectAllState());
        });

        document.querySelector('.checkout-button')?.addEventListener('click', 
            (e) => this.handleCheckout(e));
    }

    async handleQuantityChange(event) {
        const btn = event.currentTarget;
        const productId = btn.dataset.productId;
        const isIncrement = btn.classList.contains('plus');
        const quantityElement = btn.parentElement.querySelector('.quantity');
        let currentQuantity = parseInt(quantityElement.textContent);
        let newQuantity = isIncrement ? currentQuantity + 1 : currentQuantity - 1;

        if (newQuantity < 1) return;

        try {
            const response = await this.updateCart(productId, newQuantity);
            if (response.success) {
                quantityElement.textContent = newQuantity;
                this.updateCartUI(response);
            } else {
                this.showNotification(response.message, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to update quantity', 'error');
        }
    }

    async deleteSelectedItems() {
        const selectedItems = document.querySelectorAll('.item-select input[type="checkbox"]:checked');
        for (const checkbox of selectedItems) {
            const productId = checkbox.closest('.cart-item').dataset.productId;
            try {
                const response = await this.removeCartItem(productId);
                if (response.success) {
                    checkbox.closest('.cart-item').remove();
                    this.updateCartUI(response);
                }
            } catch (error) {
                this.showNotification('Failed to remove item', 'error');
            }
        }
        this.checkEmptyCart();
    }

    async handleCheckout(event) {
        event.preventDefault();
        try {
            const response = await this.sendRequest('checkout', {});
            if (response.success) {
                window.location.href = response.redirect;
            } else {
                this.showNotification(response.message, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to process checkout', 'error');
        }
    }

    async updateCart(productId, quantity) {
        return await this.sendRequest('update', { product_id: productId, quantity });
    }

    async removeCartItem(productId) {
        return await this.sendRequest('remove', { product_id: productId });
    }

    async sendRequest(action, data) {
        const response = await fetch('/minor project/cart/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ ...data, action })
        });
        return await response.json();
    }

    toggleSelectAll(event) {
        const isChecked = event.target.checked;
        document.querySelectorAll('.item-select input[type="checkbox"]')
            .forEach(checkbox => checkbox.checked = isChecked);
    }

    updateSelectAllState() {
        const checkboxes = document.querySelectorAll('.item-select input[type="checkbox"]');
        const selectAllCheckbox = document.getElementById('select-all');
        const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
        selectAllCheckbox.checked = allChecked;
    }

    updateCartUI(response) {
        if (response.new_total !== undefined) {
            const subtotalElement = document.querySelector('.summary-item:first-child span:last-child');
            const totalElement = document.querySelector('.summary-total span:last-child');
            const shippingFee = 40;

            subtotalElement.textContent = `Rs ${response.new_total.toFixed(2)}`;
            totalElement.textContent = `Rs ${(response.new_total + shippingFee).toFixed(2)}`;
        }

        if (response.cart_count !== undefined) {
            document.querySelectorAll('.cart-count').forEach(element => {
                element.textContent = response.cart_count;
            });
        }
    }

    checkEmptyCart() {
        const cartItems = document.querySelectorAll('.cart-item');
        if (cartItems.length === 0) {
            const cartContainer = document.querySelector('.cart-items');
            cartContainer.innerHTML = `
                <div class="empty-cart">
                    <p>Your cart is empty</p>
                    <a href="/minor project/index.php" class="continue-shopping">Continue Shopping</a>
                </div>`;
        }
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
    new CartManager();
});