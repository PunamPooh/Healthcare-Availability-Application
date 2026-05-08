<?php
session_start();
if(!isset($_SESSION['is_admin'])) {
    header("Location: admin_login.php");
    exit();
}
include 'config/db.php';

$message = '';
$error = '';

// Handle delete user
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Don't allow admin to delete themselves
    if($id == $_SESSION['admin_id']) {
        $error = "You cannot delete your own admin account!";
    } else {
        // First delete all appointments for this user
        $conn->query("DELETE FROM appointments WHERE user_id = $id");
        // Then delete all resource bookings for this user
        $conn->query("DELETE FROM resource_bookings WHERE user_id = $id");
        // Finally delete the user
        if($conn->query("DELETE FROM users WHERE id = $id")) {
            $message = "User deleted successfully!";
        } else {
            $error = "Error deleting user: " . $conn->error;
        }
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
        }
        .sidebar {
            width: 260px;
            background: #1e4463;
            color: white;
            position: fixed;
            height: 100vh;
            padding: 20px 0;
            overflow-y: auto;
        }
        .sidebar h2 {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar a {
            display: block;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 12px 25px;
            transition: all 0.3s ease;
        }
        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            padding-left: 30px;
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .section h3 {
            color: #2c5f8a;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e8f0fe;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            overflow-x: auto;
            display: block;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e8f0fe;
        }
        th {
            color: #2c5f8a;
        }
        .delete-btn {
            background: #c0392b;
            color: white;
            padding: 5px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            border: none;
            cursor: pointer;
        }
        .delete-btn:hover {
            background: #a93226;
        }
        .admin-badge {
            background: #2c5f8a;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            display: inline-block;
        }
        .user-badge {
            background: #7a8e9b;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            display: inline-block;
        }
        .message {
            background: #e0f5f0;
            color: #1e8449;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .error {
            background: #ffe0e0;
            color: #c0392b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 10px;
            }
            .sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🏥 Admin Panel</h2>
        <a href="admin_dashboard.php">📊 Dashboard</a>
        <a href="admin_facilities.php">🏥 Manage Facilities</a>
        <a href="admin_resources.php">🔬 Manage Resources</a>
        <a href="admin_users.php">👥 Manage Users</a>
        <a href="admin_appointments.php">📅 Manage Appointments</a>
        <a href="admin_logout.php">🚪 Logout</a>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h2>👥 Manage Users</h2>
            <p>View and manage all registered users</p>
        </div>
        
        <?php if($message): ?>
            <div class="message">✅ <?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="error">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="section">
            <h3>📋 Registered Users</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <?php if($row['is_admin'] == 1): ?>
                                    <span class="admin-badge">👑 Admin</span>
                                <?php else: ?>
                                    <span class="user-badge">👤 User</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php if($row['id'] != $_SESSION['admin_id']): ?>
                                    <a href="?delete=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user? All their appointments will also be deleted.')">🗑️ Delete</a>
                                <?php else: ?>
                                    <span style="color:#7a8e9b; font-size:12px;">Current Admin</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>