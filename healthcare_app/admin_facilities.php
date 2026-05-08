<?php
session_start();
if(!isset($_SESSION['is_admin'])) {
    header("Location: admin_login.php");
    exit();
}
include 'config/db.php';

// Handle delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM facilities WHERE id = $id");
    header("Location: admin_facilities.php");
    exit();
}

// Handle add/edit
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'];
    $type = $_POST['type'];
    $address = $_POST['address'];
    $lat = $_POST['latitude'];
    $lng = $_POST['longitude'];
    $phone = $_POST['phone'];
    $hours = $_POST['opening_hours'];
    
    if($id > 0) {
        $stmt = $conn->prepare("UPDATE facilities SET name=?, type=?, address=?, latitude=?, longitude=?, phone=?, opening_hours=? WHERE id=?");
        $stmt->bind_param("sssdsssi", $name, $type, $address, $lat, $lng, $phone, $hours, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO facilities (name, type, address, latitude, longitude, phone, opening_hours) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdsss", $name, $type, $address, $lat, $lng, $phone, $hours);
    }
    $stmt->execute();
    header("Location: admin_facilities.php");
    exit();
}

$facilities = $conn->query("SELECT * FROM facilities ORDER BY id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Facilities - Admin</title>
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
        .section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .section h3 {
            color: #2c5f8a;
            margin-bottom: 15px;
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
        .add-btn, .edit-btn, .delete-btn {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
        }
        .add-btn { background: #1e8449; color: white; }
        .edit-btn { background: #2c5f8a; color: white; }
        .delete-btn { background: #c0392b; color: white; }
        form input, form select, form textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .form-row {
            margin-bottom: 15px;
        }
        .submit-btn {
            background: #2c5f8a;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
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
            <h2>🏥 Manage Healthcare Facilities</h2>
        </div>
        
        <div class="section">
            <h3>➕ Add New Facility</h3>
            <form method="POST">
                <div class="form-row">
                    <input type="text" name="name" placeholder="Facility Name" required>
                </div>
                <div class="form-row">
                    <select name="type" required>
                        <option value="hospital">Hospital</option>
                        <option value="clinic">Clinic</option>
                        <option value="doctor_office">Doctor Office</option>
                    </select>
                </div>
                <div class="form-row">
                    <input type="text" name="address" placeholder="Address" required>
                </div>
                <div class="form-row">
                    <input type="text" name="latitude" placeholder="Latitude (e.g., 55.6999)" required>
                </div>
                <div class="form-row">
                    <input type="text" name="longitude" placeholder="Longitude (e.g., 12.5660)" required>
                </div>
                <div class="form-row">
                    <input type="text" name="phone" placeholder="Phone Number" required>
                </div>
                <div class="form-row">
                    <input type="text" name="opening_hours" placeholder="Opening Hours" required>
                </div>
                <button type="submit" class="submit-btn">➕ Add Facility</button>
            </form>
        </div>
        
        <div class="section">
            <h3>📋 Existing Facilities</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $facilities->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo ucfirst($row['type']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td>
                                <a href="admin_edit_facility.php?id=<?php echo $row['id']; ?>" class="edit-btn">✏️ Edit</a>
                                <a href="?delete=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Delete this facility?')">🗑️ Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>