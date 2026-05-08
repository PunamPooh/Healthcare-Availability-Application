<?php
session_start();
if(!isset($_SESSION['is_admin'])) {
    header("Location: admin_login.php");
    exit();
}
include 'config/db.php';

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_facilities = $conn->query("SELECT COUNT(*) as count FROM facilities")->fetch_assoc()['count'];
$total_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'];
$total_resources = $conn->query("SELECT COUNT(*) as count FROM resources")->fetch_assoc()['count'];
$total_resource_bookings = $conn->query("SELECT COUNT(*) as count FROM resource_bookings")->fetch_assoc()['count'];

// Recent appointments
$recent_appointments = $conn->query("
    SELECT a.*, u.name as user_name, f.name as facility_name 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    JOIN facilities f ON a.facility_id = f.id 
    ORDER BY a.created_at DESC LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Healthcare App</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background: #1e4463;
            color: white;
            position: fixed;
            height: 100vh;
            padding: 20px 0;
        }
        .sidebar h2 {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
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
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .stat-card h3 {
            color: #7a8e9b;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #2c5f8a;
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
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e8f0fe;
        }
        th {
            color: #2c5f8a;
            font-weight: 600;
        }
        .logout-btn {
            background: #c0392b;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
        }
        .logout-btn:hover {
            background: #a93226;
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
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></h2>
            <a href="admin_logout.php" class="logout-btn">Logout</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>👥 Total Users</h3>
                <div class="number"><?php echo $total_users; ?></div>
            </div>
            <div class="stat-card">
                <h3>🏥 Facilities</h3>
                <div class="number"><?php echo $total_facilities; ?></div>
            </div>
            <div class="stat-card">
                <h3>📅 Appointments</h3>
                <div class="number"><?php echo $total_appointments; ?></div>
            </div>
            <div class="stat-card">
                <h3>🔬 Resources</h3>
                <div class="number"><?php echo $total_resources; ?></div>
            </div>
            <div class="stat-card">
                <h3>📋 Resource Bookings</h3>
                <div class="number"><?php echo $total_resource_bookings; ?></div>
            </div>
        </div>
        
        <div class="section">
            <h3>📅 Recent Appointments</h3>
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Facility</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $recent_appointments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['facility_name']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($row['appointment_date'])); ?></td>
                            <td><?php echo date('H:i', strtotime($row['appointment_time'])); ?></td>
                            <td><?php echo ucfirst($row['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>