function parseXMLData(xmlString) {
    const parser = new DOMParser();
    const xmlDoc = parser.parseFromString(xmlString, "text/xml");

    const salesData = Array.from(xmlDoc.querySelectorAll('sales month')).map(item => parseInt(item.getAttribute('value')));
    const stockData = Array.from(xmlDoc.querySelectorAll('stock product')).map(item => parseInt(item.getAttribute('value')));

    return { salesData, stockData };
  }

  const updateCharts = (salesData, stockData) => {
    const salesChartData = {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
      datasets: [{
        label: 'Sales (रू)',
        data: salesData,
        backgroundColor: '#ff6700',
        borderColor: '#1a1a2e',
        borderWidth: 2,
      }]
    };

    const stockChartData = {
      labels: ['Product A', 'Product B', 'Product C', 'Product D'],
      datasets: [{
        label: 'Stock Value (रू)',
        data: stockData,
        backgroundColor: 'rgba(255, 103, 0, 0.2)',
        borderColor: '#ff6700',
        borderWidth: 2,
        tension: 0.4,
        pointBackgroundColor: '#1a1a2e',
        pointBorderColor: '#ff6700',
        pointHoverBackgroundColor: '#ff6700',
        pointHoverBorderColor: '#1a1a2e'
      }]
    };

    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
      type: 'bar',
      data: salesChartData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true }
        }
      }
    });

    const stockCtx = document.getElementById('stockChart').getContext('2d');
    new Chart(stockCtx, {
      type: 'line',
      data: stockChartData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true }
        },
        plugins: {
          legend: { display: true }
        }
      }
    });
  };

  fetch('../admin/js/data.xml')
    .then(response => response.text())
    .then(xmlString => {
      const { salesData, stockData } = parseXMLData(xmlString);
      updateCharts(salesData, stockData);
    })
    .catch(error => console.error('Error loading XML:', error));