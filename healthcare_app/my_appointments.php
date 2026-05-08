<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config/db.php';

$user_id = $_SESSION['user_id'];

// Get regular appointments from appointments table
$regular_sql = "SELECT 
                    a.*, 
                    f.name as facility_name, 
                    f.address, 
                    f.phone,
                    'regular' as booking_type,
                    NULL as resource_name,
                    NULL as duration_minutes
                FROM appointments a 
                JOIN facilities f ON a.facility_id = f.id 
                WHERE a.user_id = $user_id 
                ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$regular_result = $conn->query($regular_sql);

// Get resource bookings from resource_bookings table
$resource_sql = "SELECT 
                    rb.*,
                    f.name as facility_name,
                    f.address,
                    f.phone,
                    r.resource_name,
                    r.duration_minutes,
                    r.resource_type,
                    'resource' as booking_type
                FROM resource_bookings rb
                JOIN resources r ON rb.resource_id = r.id
                JOIN facilities f ON r.facility_id = f.id
                WHERE rb.user_id = $user_id
                ORDER BY rb.booking_date DESC, rb.booking_time DESC";

$resource_result = $conn->query($resource_sql);

// Combine both result sets into one array
$all_appointments = [];

while($row = $regular_result->fetch_assoc()) {
    $all_appointments[] = $row;
}

while($row = $resource_result->fetch_assoc()) {
    $all_appointments[] = $row;
}

// Sort combined array by date (most recent first)
usort($all_appointments, function($a, $b) {
    $date_a = $a['booking_date'] ?? $a['appointment_date'];
    $date_b = $b['booking_date'] ?? $b['appointment_date'];
    return strtotime($date_b) - strtotime($date_a);
});
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Appointments - Healthcare App</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e8f0fe 0%, #f5f7fa 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .header h2 {
            color: #2c5f8a;
            font-weight: 500;
        }
        
        .header a {
            color: #5a6e7a;
            text-decoration: none;
            margin-left: 15px;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .header a:hover {
            background: #e8f0fe;
            color: #2c5f8a;
        }
        
        .appointment {
            background: white;
            padding: 20px;
            margin: 15px 0;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .appointment:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        
        .appointment.regular {
            border-left: 4px solid #2c5f8a;
        }
        
        .appointment.resource {
            border-left: 4px solid #1e8449;
        }
        
        .appointment.cancelled {
            opacity: 0.6;
            border-left-color: #c0392b;
        }
        
        .appointment h3 {
            color: #2c5f8a;
            font-weight: 500;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .booking-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-regular {
            background: #e8f0fe;
            color: #2c5f8a;
        }
        
        .badge-resource {
            background: #e0f5f0;
            color: #1e8449;
        }
        
        .resource-type-icon {
            font-size: 20px;
        }
        
        .appointment p {
            color: #5a6e7a;
            margin: 8px 0;
        }
        
        .status {
            font-weight: bold;
        }
        
        .status-booked {
            color: #1e8449;
        }
        
        .status-cancelled {
            color: #c0392b;
        }
        
        .cancel-btn {
            display: inline-block;
            background: #c0392b;
            color: white;
            padding: 8px 20px;
            text-decoration: none;
            border-radius: 25px;
            margin-top: 12px;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .cancel-btn:hover {
            background: #a93226;
            transform: translateY(-2px);
        }
        
        .no-appointments {
            background: white;
            text-align: center;
            padding: 50px;
            border-radius: 15px;
            color: #7a8e9b;
        }
        
        .duration {
            background: #f8f9fa;
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 5px;
        }
        
        @media (max-width: 600px) {
            .header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            .header a {
                margin: 0 10px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>📋 My Appointments</h2>
        <div>
            <a href="dashboard.php">← Back to Search</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>
    
    <?php if(count($all_appointments) > 0): ?>
        <?php foreach($all_appointments as $appt): ?>
            <?php 
            // Determine if this is a regular appointment or resource booking
            $is_regular = ($appt['booking_type'] == 'regular');
            $is_cancelled = ($appt['status'] == 'cancelled');
            $date = $is_regular ? $appt['appointment_date'] : $appt['booking_date'];
            $time = $is_regular ? $appt['appointment_time'] : $appt['booking_time'];
            $appt_id = $is_regular ? $appt['id'] : $appt['id'];
            ?>
            
            <div class="appointment <?php echo $is_regular ? 'regular' : 'resource'; ?> <?php echo $is_cancelled ? 'cancelled' : ''; ?>">
                <h3>
                    <?php if(!$is_regular): ?>
                        <span class="resource-type-icon">
                            <?php 
                                switch($appt['resource_type']) {
                                    case 'mri': echo '🫀'; break;
                                    case 'ct_scan': echo '📊'; break;
                                    case 'xray': echo '🦴'; break;
                                    case 'ultrasound': echo '👶'; break;
                                    case 'eye_exam': echo '👁️'; break;
                                    case 'blood_test': echo '🩸'; break;
                                    case 'vaccination': echo '💉'; break;
                                    case 'physiotherapy': echo '🏃'; break;
                                    default: echo '🔬';
                                }
                            ?>
                        </span>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($appt['facility_name']); ?>
                    <span class="booking-badge <?php echo $is_regular ? 'badge-regular' : 'badge-resource'; ?>">
                        <?php echo $is_regular ? '📅 Regular Appointment' : '🔬 Resource Booking'; ?>
                    </span>
                </h3>
                
                <?php if(!$is_regular): ?>
                    <p><strong>🔬 Service:</strong> <?php echo htmlspecialchars($appt['resource_name']); ?></p>
                    <?php if($appt['duration_minutes']): ?>
                        <p><span class="duration">⏱️ Duration: <?php echo $appt['duration_minutes']; ?> minutes</span></p>
                    <?php endif; ?>
                    <?php if($appt['patient_notes']): ?>
                        <p><strong>📝 Your Notes:</strong> <?php echo nl2br(htmlspecialchars($appt['patient_notes'])); ?></p>
                    <?php endif; ?>
                <?php endif; ?>
                
                <p>📌 <?php echo htmlspecialchars($appt['address']); ?></p>
                <p>📞 <?php echo htmlspecialchars($appt['phone']); ?></p>
                <p>📅 Date: <?php echo date('F j, Y', strtotime($date)); ?></p>
                <p>⏰ Time: <?php echo date('g:i A', strtotime($time)); ?></p>
                
                <p>Status: 
                    <span class="status status-<?php echo $appt['status']; ?>">
                        <?php echo ucfirst($appt['status']); ?>
                    </span>
                </p>
                
                <?php if($appt['status'] == 'booked'): ?>
                    <?php if($is_regular): ?>
                        <a href="cancel.php?id=<?php echo $appt_id; ?>" 
                           onclick="return confirm('Are you sure you want to cancel this appointment?')"
                           class="cancel-btn">Cancel Appointment</a>
                    <?php else: ?>
                        <a href="cancel_resource.php?id=<?php echo $appt_id; ?>" 
                           onclick="return confirm('Are you sure you want to cancel this resource booking?')"
                           class="cancel-btn">Cancel Resource Booking</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-appointments">
            <p>📭 You have no appointments yet.</p>
            <p style="margin-top: 10px;">
                <a href="dashboard.php" style="color: #2c5f8a;">Click here to search for healthcare facilities</a> 
                or <a href="dashboard.php" style="color: #2c5f8a;">book a specialized resource (MRI, CT scan, etc.)</a>
            </p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>