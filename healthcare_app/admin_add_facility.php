<?php
session_start();
if(!isset($_SESSION['is_admin'])) {
    header("Location: admin_login.php");
    exit();
}
include 'config/db.php';

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $address = $_POST['address'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $phone = $_POST['phone'];
    $opening_hours = $_POST['opening_hours'];
    
    $stmt = $conn->prepare("INSERT INTO facilities (name, type, address, latitude, longitude, phone, opening_hours) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdsss", $name, $type, $address, $latitude, $longitude, $phone, $opening_hours);
    
    if($stmt->execute()) {
        $success = "Facility added successfully!";
    } else {
        $error = "Error adding facility.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Facility - Admin</title>
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
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 600px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: #2c5f8a;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .submit-btn {
            background: #1e8449;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .success {
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
            <h2>🏥 Add New Healthcare Facility</h2>
            <a href="admin_facilities.php" style="color:#2c5f8a;">← Back to Facilities</a>
        </div>
        
        <div class="form-container">
            <?php if($success): ?>
                <div class="success">✅ <?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Facility Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" required>
                        <option value="hospital">Hospital</option>
                        <option value="clinic">Clinic</option>
                        <option value="doctor_office">Doctor Office</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="2" required></textarea>
                </div>
                <div class="form-group">
                    <label>Latitude (e.g., 55.6999)</label>
                    <input type="text" name="latitude" step="any" required>
                </div>
                <div class="form-group">
                    <label>Longitude (e.g., 12.5660)</label>
                    <input type="text" name="longitude" step="any" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" required>
                </div>
                <div class="form-group">
                    <label>Opening Hours</label>
                    <input type="text" name="opening_hours" placeholder="e.g., 24/7 or 8AM-8PM Mon-Fri" required>
                </div>
                <button type="submit" class="submit-btn">➕ Add Facility</button>
            </form>
        </div>
    </div>
</body>
</html>