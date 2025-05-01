document.addEventListener('DOMContentLoaded', function() {
    const addStockButtons = document.querySelectorAll('button[name="add_stock"]');
    
    addStockButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const productRow = this.closest('tr');
            const productName = productRow.querySelector('td:nth-child(1)').innerText;
            const quantity = productRow.querySelector('input[name="quantity"]').value;

            if (quantity > 0) {
                alert(`Added ${quantity} of ${productName} to stock.`);
            } else {
                alert('Please enter a valid quantity.');
                event.preventDefault();
            }
        });
    });

    const updateStockButtons = document.querySelectorAll('button[name="update_stock"]');
    
    updateStockButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const productRow = this.closest('tr');
            const productName = productRow.querySelector('td:nth-child(1)').innerText;
            const newQuantity = productRow.querySelector('input[name="quantity"]').value;

            if (newQuantity >= 0) {
                alert(`Updated ${productName} stock to ${newQuantity}.`);
            } else {
                alert('Please enter a valid quantity.');
                event.preventDefault();
            }
        });
    });
});
