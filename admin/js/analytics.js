// Chart initialization
const ctx = document.getElementById('revenueChart').getContext('2d');
const monthlyData = JSON.parse(document.getElementById('monthly-sales-data').textContent);

new Chart(ctx, {
    type: 'line',
    data: {
        labels: monthlyData.map(item => item.month),
        datasets: [{
            label: 'Monthly Revenue',
            data: monthlyData.map(item => item.revenue),
            borderColor: '#ff6d00',
            backgroundColor: 'rgba(255, 109, 0, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: { color: '#ffffff' }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                },
                ticks: {
                    color: '#ffffff',
                    callback: value => 'रू ' + value.toLocaleString()
                }
            },
            x: {
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                },
                ticks: { color: '#ffffff' }
            }
        }
    }
});