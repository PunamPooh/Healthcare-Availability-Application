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
    $conn->query("DELETE FROM resources WHERE id = $id");
    header("Location: admin_resources.php");
    exit();
}

// Handle add/edit
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_resource'])) {
    $facility_id = $_POST['facility_id'];
    $resource_name = $_POST['resource_name'];
    $resource_type = $_POST['resource_type'];
    $description = $_POST['description'];
    $price_range = $_POST['price_range'];
    $requires_referral = isset($_POST['requires_referral']) ? 1 : 0;
    $available_days = $_POST['available_days'];
    $available_time_start = $_POST['available_time_start'];
    $available_time_end = $_POST['available_time_end'];
    $duration_minutes = $_POST['duration_minutes'];
    
    $stmt = $conn->prepare("INSERT INTO resources (facility_id, resource_name, resource_type, description, price_range, requires_referral, available_days, available_time_start, available_time_end, duration_minutes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssisssi", $facility_id, $resource_name, $resource_type, $description, $price_range, $requires_referral, $available_days, $available_time_start, $available_time_end, $duration_minutes);
    $stmt->execute();
    header("Location: admin_resources.php");
    exit();
}

$resources = $conn->query("
    SELECT r.*, f.name as facility_name 
    FROM resources r 
    JOIN facilities f ON r.facility_id = f.id 
    ORDER BY r.id DESC
");

$facilities = $conn->query("SELECT id, name FROM facilities ORDER BY name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Resources - Admin</title>
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
        .add-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
        }
        .form-row {
            margin-bottom: 15px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        .form-group label {
            display: block;
            color: #2c5f8a;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .submit-btn {
            background: #1e8449;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
        }
        .badge-referral {
            background: #ffe0e0;
            color: #c0392b;
        }
        .badge-no-referral {
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
            <h2>🔬 Manage Healthcare Resources</h2>
            <p>MRI, CT Scans, X-Ray, Blood Tests, etc.</p>
        </div>
        
        <div class="section">
            <h3>➕ Add New Resource</h3>
            <form method="POST" class="add-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Facility</label>
                        <select name="facility_id" required>
                            <option value="">Select Facility</option>
                            <?php while($fac = $facilities->fetch_assoc()): ?>
                                <option value="<?php echo $fac['id']; ?>"><?php echo htmlspecialchars($fac['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Resource Name</label>
                        <input type="text" name="resource_name" placeholder="e.g., MRI - Full Body" required>
                    </div>
                    <div class="form-group">
                        <label>Resource Type</label>
                        <select name="resource_type" required>
                            <option value="mri">MRI Scan</option>
                            <option value="ct_scan">CT Scan</option>
                            <option value="xray">X-Ray</option>
                            <option value="ultrasound">Ultrasound</option>
                            <option value="eye_exam">Eye Examination</option>
                            <option value="blood_test">Blood Test</option>
                            <option value="vaccination">Vaccination</option>
                            <option value="physiotherapy">Physiotherapy</option>
                            <option value="general_consultation">General Consultation</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="2" placeholder="Describe the service..."></textarea>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Price Range (DKK)</label>
                        <input type="text" name="price_range" placeholder="e.g., 500-1000 DKK">
                    </div>
                    <div class="form-group">
                        <label>Duration (minutes)</label>
                        <input type="number" name="duration_minutes" value="30" required>
                    </div>
                    <div class="form-group">
                        <label>Requires Referral?</label>
                        <select name="requires_referral">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Available Days</label>
                        <select name="available_days" required>
                            <option value="Mon,Tue,Wed,Thu,Fri">Mon-Fri</option>
                            <option value="Mon,Tue,Wed,Thu,Fri,Sat">Mon-Sat</option>
                            <option value="Mon,Wed,Fri">Mon, Wed, Fri</option>
                            <option value="Tue,Thu">Tue, Thu</option>
                            <option value="Mon,Tue,Wed,Thu,Fri,Sat,Sun">Every Day</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="time" name="available_time_start" value="09:00" required>
                    </div>
                    <div class="form-group">
                        <label>End Time</label>
                        <input type="time" name="available_time_end" value="17:00" required>
                    </div>
                </div>
                <button type="submit" name="add_resource" class="submit-btn">➕ Add Resource</button>
            </form>
        </div>
        
        <div class="section">
            <h3>📋 Existing Resources</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Facility</th>
                        <th>Resource</th>
                        <th>Type</th>
                        <th>Duration</th>
                        <th>Referral</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $resources->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['facility_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['resource_name']); ?></td>
                            <td><?php echo strtoupper(str_replace('_', ' ', $row['resource_type'])); ?></td>
                            <td><?php echo $row['duration_minutes']; ?> min</td>
                            <td>
                                <?php if($row['requires_referral']): ?>
                                    <span class="badge badge-referral">Required</span>
                                <?php else: ?>
                                    <span class="badge badge-no-referral">Not Required</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?delete=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Delete this resource?')">🗑️ Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>