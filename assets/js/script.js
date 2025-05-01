document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const cartIcon = document.getElementById('cart-icon');
    const cartDropdown = document.getElementById('cart-dropdown');
    const cartCount = document.getElementById('cart-count');
    const cartItems = document.getElementById('cart-items');
    const emptyCartMessage = document.getElementById('empty-cart-message');
    const cartTotalAmount = document.getElementById('cart-total-amount');
    const checkoutBtn = document.getElementById('checkout-btn');
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    const toastContainer = document.getElementById('toast-container');
    
    // Cart data
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    // Update cart UI based on data
    function updateCartUI() {
        // Update cart count
        const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
        cartCount.textContent = totalItems;
        
        // Update cart items list
        cartItems.innerHTML = '';
        
        if (cart.length === 0) {
            emptyCartMessage.style.display = 'block';
            cartItems.appendChild(emptyCartMessage);
        } else {
            emptyCartMessage.style.display = 'none';
            
            cart.forEach(item => {
                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item';
                
                cartItem.innerHTML = `
                    <img src="${item.image}" alt="${item.name}" class="cart-item-image" onerror="this.src='public/placeholder.svg'">
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">Rs. ${item.price.toLocaleString()}</div>
                    </div>
                    <div class="cart-item-actions">
                        <div class="quantity-btn decrease" data-id="${item.id}">-</div>
                        <span class="quantity-value">${item.quantity}</span>
                        <div class="quantity-btn increase" data-id="${item.id}">+</div>
                        <div class="remove-item" data-id="${item.id}">×</div>
                    </div>
                `;
                
                cartItems.appendChild(cartItem);
                
                // Add event listeners for this specific cart item
                const decreaseBtn = cartItem.querySelector('.decrease');
                const increaseBtn = cartItem.querySelector('.increase');
                const removeBtn = cartItem.querySelector('.remove-item');
                
                decreaseBtn.addEventListener('click', () => updateQuantity(item.id, 'decrease'));
                increaseBtn.addEventListener('click', () => updateQuantity(item.id, 'increase'));
                removeBtn.addEventListener('click', () => removeFromCart(item.id));
            });
        }
        
        // Update total amount
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        cartTotalAmount.textContent = `Rs. ${total.toLocaleString()}`;
        
        // Save cart to localStorage
        localStorage.setItem('cart', JSON.stringify(cart));
    }
    
    // Add item to cart
    function addToCart(name, price, image) {
        const existingItemIndex = cart.findIndex(item => item.name === name);
        
        if (existingItemIndex !== -1) {
            cart[existingItemIndex].quantity += 1;
            showToast(`Increased ${name} quantity in cart`, 'success');
        } else {
            const newItem = {
                id: Date.now().toString(),
                name,
                price,
                image,
                quantity: 1
            };
            cart.push(newItem);
            showToast(`${name} added to cart`, 'success');
        }
        
        updateCartUI();
        cartDropdown.classList.add('active');
    }
    
    // Remove item from cart
    function removeFromCart(id) {
        const itemIndex = cart.findIndex(item => item.id.toString() === id);
        if (itemIndex !== -1) {
            const removedItem = cart[itemIndex];
            cart.splice(itemIndex, 1);
            updateCartUI();
            showToast(`${removedItem.name} removed from cart`, 'info');
        }
    }
    
    // Update item quantity
    function updateQuantity(id, action) {
        const itemIndex = cart.findIndex(item => item.id.toString() === id);
        
        if (itemIndex !== -1) {
            if (action === 'increase') {
                cart[itemIndex].quantity += 1;
            } else if (action === 'decrease') {
                if (cart[itemIndex].quantity > 1) {
                    cart[itemIndex].quantity -= 1;
                } else {
                    removeFromCart(id);
                    return;
                }
            }
            updateCartUI();
        }
    }
    
    // Show toast notification
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        
        toastContainer.appendChild(toast);
        
        // Trigger reflow to ensure animation works
        toast.offsetHeight;
        
        // Make toast visible with animation
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.maxHeight = '100px';
        });
        
        // Remove toast after animation
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.maxHeight = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Toggle cart dropdown
    cartIcon.addEventListener('click', function(event) {
        event.stopPropagation();
        cartDropdown.classList.toggle('active');
    });
    
    // Close cart when clicking outside
    document.addEventListener('click', function(event) {
        if (!cartDropdown.contains(event.target) && event.target !== cartIcon) {
            cartDropdown.classList.remove('active');
        }
    });
    
    // Add event listeners to Add to Cart buttons
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);
            const image = this.dataset.image;
            addToCart(name, price, image);
        });
    });
    
    // Checkout button click event
    checkoutBtn.addEventListener('click', function() {
        if (cart.length > 0) {
            showToast('Order placed successfully!', 'success');
            cart = [];
            updateCartUI();
            cartDropdown.classList.remove('active');
        } else {
            showToast('Your cart is empty!', 'error');
        }
    });
    
    // Initialize cart UI
    updateCartUI();
});