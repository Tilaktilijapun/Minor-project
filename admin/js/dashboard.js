document.addEventListener('DOMContentLoaded', function() {
    // Get chart data from data attributes
    const salesData = JSON.parse(document.getElementById('sales-data').getAttribute('data-stats'));
    const stockData = JSON.parse(document.getElementById('stock-data').getAttribute('data-stats'));

    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesData.labels,
            datasets: [{
                label: 'Monthly Sales',
                data: salesData.values,
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'रू ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'रू ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Stock Chart
    const stockCtx = document.getElementById('stockChart').getContext('2d');
    new Chart(stockCtx, {
        type: 'doughnut',
        data: {
            labels: ['In Stock', 'Low Stock', 'Out of Stock'],
            datasets: [{
                data: [stockData.inStock, stockData.lowStock, stockData.outStock],
                backgroundColor: ['#4CAF50', '#FFC107', '#F44336']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Delete product functionality
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const productName = this.dataset.name;
            if (confirm(`Are you sure you want to delete "${productName}"?`)) {
                const productId = this.dataset.id;
                fetch('/minor project/admin/api/delete-product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: productId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.closest('tr').remove();
                        showNotification('Product deleted successfully', 'success');
                        updateStockChart();
                    } else {
                        showNotification(data.message || 'Error deleting product', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error deleting product', 'error');
                });
            }
        });
    });

    // Notification function with icons
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(notification);
        
        notification.classList.add('show');
        setTimeout(() => {
            notification.classList.add('hide');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Update stock chart after changes
    function updateStockChart() {
        fetch('/minor project/admin/api/get-stock-stats.php')
            .then(response => response.json())
            .then(data => {
                const stockChart = Chart.getChart(stockCtx);
                if (stockChart) {
                    stockChart.data.datasets[0].data = [
                        data.inStock,
                        data.lowStock,
                        data.outStock
                    ];
                    stockChart.update();
                }
            })
            .catch(error => console.error('Error updating stock chart:', error));
    }
});