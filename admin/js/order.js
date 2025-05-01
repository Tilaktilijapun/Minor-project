document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    initializeSearch();
    initializeExport();
});

function initializeFilters() {
    const statusFilter = document.getElementById('statusFilter');
    const sortOrder = document.getElementById('sortOrder');
    const priceRange = document.getElementById('priceRange');

    [statusFilter, sortOrder, priceRange].forEach(filter => {
        filter.addEventListener('change', filterOrders);
    });
}

function initializeSearch() {
    const searchInput = document.getElementById('orderSearch');
    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            filterOrders();
        }, 300);
    });
}

function filterOrders() {
    const status = document.getElementById('statusFilter').value;
    const sort = document.getElementById('sortOrder').value;
    const price = document.getElementById('priceRange').value;
    const search = document.getElementById('orderSearch').value.toLowerCase();
    const dateRange = document.getElementById('dateRange').value;

    const orders = document.querySelectorAll('.order-card');

    orders.forEach(order => {
        let show = true;
        
        // Status filter
        if (status && order.dataset.status !== status) show = false;
        
        // Price range filter
        if (price) {
            const amount = parseFloat(order.dataset.amount);
            const [min, max] = price.split('-').map(Number);
            if (max && (amount < min || amount > max)) show = false;
            if (!max && amount < min) show = false;
        }

        // Search filter
        if (search) {
            const searchableContent = order.textContent.toLowerCase();
            if (!searchableContent.includes(search)) show = false;
        }

        order.style.display = show ? 'block' : 'none';
    });

    sortOrders(sort);
}

function sortOrders(sortType) {
    const ordersContainer = document.querySelector('.orders-grid');
    const orders = Array.from(ordersContainer.children);

    orders.sort((a, b) => {
        switch(sortType) {
            case 'newest':
                return new Date(b.dataset.date) - new Date(a.dataset.date);
            case 'oldest':
                return new Date(a.dataset.date) - new Date(b.dataset.date);
            case 'highest':
                return parseFloat(b.dataset.amount) - parseFloat(a.dataset.amount);
            case 'lowest':
                return parseFloat(a.dataset.amount) - parseFloat(b.dataset.amount);
            default:
                return 0;
        }
    });

    orders.forEach(order => ordersContainer.appendChild(order));
}

function updateOrderStatus(orderId, currentStatus) {
    const modal = document.getElementById('updateModal');
    const orderIdInput = document.getElementById('orderId');
    const statusSelect = document.getElementById('orderStatus');

    orderIdInput.value = orderId;
    statusSelect.value = currentStatus;
    modal.style.display = 'block';
}

function closeModal() {
    document.getElementById('updateModal').style.display = 'none';
}

document.getElementById('updateOrderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const orderId = document.getElementById('orderId').value;
    const newStatus = document.getElementById('orderStatus').value;
    const note = document.getElementById('statusNote').value;

    // Add AJAX call to update order status
    fetch('/minor project/admin/update_order_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            orderId: orderId,
            status: newStatus,
            note: note
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating order status');
        }
    });
});

function printOrder(orderId) {
    const orderCard = document.querySelector(`[data-order-id="${orderId}"]`);
    const printWindow = window.open('', '', 'width=800,height=600');
    
    printWindow.document.write(`
        <html>
            <head>
                <title>Order #${orderId}</title>
                <link rel="stylesheet" href="/minor project/admin/css/order.css">
                <style>
                    @media print {
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                ${orderCard.outerHTML}
            </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}

function exportOrders(format) {
    const url = `/minor project/admin/export_orders.php?format=${format}`;
    window.location.href = url;
}
 