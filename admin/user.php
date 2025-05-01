<?php
include '../includes/dbconn.php';
session_start();

$result = $conn->query("
    SELECT u.id, u.fullname, u.email, u.created_at,
    COALESCE((SELECT COUNT(*) FROM orders WHERE user_id = u.id), 0) as total_orders
    FROM user u
    ORDER BY u.created_at DESC
");

if (!$result) {
    die("Query failed: " . $conn->error);
}

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <link rel="stylesheet" href="/minor project/admin/css/user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="header">
            <h1><i class="fas fa-users"></i> User Management</h1>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h3>Total Users</h3>
                <p><?= count($users) ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-shopping-cart"></i>
                <h3>Total Orders</h3>
                <p><?= array_sum(array_column($users, 'total_orders')) ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-clock"></i>
                <h3>New Users (30 days)</h3>
                <p><?= array_reduce($users, function($carry, $user) {
                    return $carry + (strtotime($user['created_at']) > strtotime('-30 days') ? 1 : 0);
                }, 0) ?></p>
            </div>
        </div>

        <div class="search-bar">
            <input type="text" id="userSearch" placeholder="Search users..." onkeyup="searchUsers()">
        </div>

        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Join Date</th>
                    <th>Orders</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['fullname']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                    <td><?= $user['total_orders'] ?></td>
                    <td>
                        <a href="view_user.php?id=<?= $user['id'] ?>" class="btn btn-view">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-delete" 
                           onclick="return confirm('Are you sure you want to delete this user?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    function searchUsers() {
        const input = document.getElementById('userSearch');
        const filter = input.value.toLowerCase();
        const table = document.querySelector('.user-table');
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                if (cell) {
                    const text = cell.textContent || cell.innerText;
                    if (text.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            
            row.style.display = found ? '' : 'none';
        }
    }
    </script>
</body>
</html>
