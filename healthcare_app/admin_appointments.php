<?php
session_start();
if(!isset($_SESSION['is_admin'])) {
    header("Location: admin_login.php");
    exit();
}
include 'config/db.php';

// Handle delete appointment
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM appointments WHERE id = $id");
    header("Location: admin_appointments.php");
    exit();
}

// Handle delete resource booking
if(isset($_GET['delete_resource'])) {
    $id = $_GET['delete_resource'];
    $conn->query("DELETE FROM resource_bookings WHERE id = $id");
    header("Location: admin_appointments.php");
    exit();
}

// Get all regular appointments
$appointments = $conn->query("
    SELECT a.*, u.name as user_name, u.email as user_email, f.name as facility_name 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    JOIN facilities f ON a.facility_id = f.id 
    ORDER BY a.created_at DESC
");

// Get all resource bookings
$resource_bookings = $conn->query("
    SELECT rb.*, u.name as user_name, u.email as user_email, 
           f.name as facility_name, r.resource_name, r.resource_type
    FROM resource_bookings rb
    JOIN users u ON rb.user_id = u.id
    JOIN resources r ON rb.resource_id = r.id
    JOIN facilities f ON r.facility_id = f.id
    ORDER BY rb.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Appointments - Admin</title>
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
        }
        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
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
        }
        .section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
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
        }
        .status-booked {
            color: #1e8449;
            font-weight: bold;
        }
        .type-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
        }
        .type-regular {
            background: #e8f0fe;
            color: #2c5f8a;
        }
        .type-resource {
            background: #e0f5f0;
            color: #1e8449;
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
            <h2>📅 Manage Appointments</h2>
            <p>View and manage all user appointments and resource bookings</p>
        </div>
        
        <div class="section">
            <h3>📋 Regular Appointments</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Facility</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $appointments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                            <td><?php echo htmlspecialchars($row['facility_name']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($row['appointment_date'])); ?></td>
                            <td><?php echo date('H:i', strtotime($row['appointment_time'])); ?></td>
                            <td class="status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></td>
                            <td>
                                <a href="?delete=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Delete this appointment?')">🗑️ Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h3>🔬 Resource Bookings</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Resource</th>
                        <th>Facility</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $resource_bookings->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                            <td><?php echo htmlspecialchars($row['resource_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['facility_name']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($row['booking_date'])); ?></td>
                            <td><?php echo date('H:i', strtotime($row['booking_time'])); ?></td>
                            <td class="status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></td>
                            <td>
                                <a href="?delete_resource=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Delete this resource booking?')">🗑️ Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>