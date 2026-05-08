<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config/db.php';

$facility_id = $_GET['facility_id'] ?? 0;
$error = '';
$success = '';

// Get facility details
$stmt = $conn->prepare("SELECT * FROM facilities WHERE id = ?");
$stmt->bind_param("i", $facility_id);
$stmt->execute();
$facility = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$facility) {
    header("Location: dashboard.php");
    exit();
}

// Handle booking submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $user_id = $_SESSION['user_id'];
    
    // Check if slot is already booked
    $check = $conn->prepare("SELECT * FROM appointments WHERE facility_id = ? AND appointment_date = ? AND appointment_time = ? AND status = 'booked'");
    $check->bind_param("iss", $facility_id, $appointment_date, $appointment_time);
    $check->execute();
    if($check->get_result()->num_rows > 0) {
        $error = "This time slot is already taken. Please choose another time.";
    } else {
        $insert = $conn->prepare("INSERT INTO appointments (user_id, facility_id, appointment_date, appointment_time) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iiss", $user_id, $facility_id, $appointment_date, $appointment_time);
        if($insert->execute()) {
            $success = "✅ Appointment booked successfully!";
        } else {
            $error = "Booking failed. Please try again.";
        }
        $insert->close();
    }
    $check->close();
}

// Get booked times for the selected date (for dynamic disable)
$booked_times = [];
if(isset($_POST['appointment_date'])) {
    $date = $_POST['appointment_date'];
    $check_times = $conn->prepare("SELECT appointment_time FROM appointments WHERE facility_id = ? AND appointment_date = ? AND status = 'booked'");
    $check_times->bind_param("is", $facility_id, $date);
    $check_times->execute();
    $times_result = $check_times->get_result();
    while($row = $times_result->fetch_assoc()) {
        $booked_times[] = $row['appointment_time'];
    }
    $check_times->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment - Healthcare Availability App</title>
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
            max-width: 700px;
            margin: 0 auto;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #5a6e7a;
            padding: 8px 16px;
            background: white;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .back-link:hover {
            background: #2c5f8a;
            color: white;
        }
        
        /* Facility Info Card */
        .facility-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .facility-header {
            background: #2c5f8a;
            padding: 20px 25px;
            color: white;
        }
        
        .facility-header h2 {
            font-weight: 500;
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .facility-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .facility-details {
            padding: 20px 25px;
        }
        
        .detail-row {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e8f0fe;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-icon {
            width: 40px;
            font-size: 20px;
        }
        
        .detail-text {
            flex: 1;
            color: #5a6e7a;
        }
        
        .detail-label {
            font-weight: 600;
            color: #2c5f8a;
            width: 80px;
        }
        
        /* Booking Form Card */
        .booking-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .booking-header {
            background: linear-gradient(135deg, #1e4463 0%, #2c5f8a 100%);
            padding: 18px 25px;
            color: white;
        }
        
        .booking-header h3 {
            font-weight: 500;
            font-size: 20px;
        }
        
        .booking-header p {
            font-size: 13px;
            opacity: 0.85;
            margin-top: 5px;
        }
        
        .booking-form {
            padding: 25px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: #2c5f8a;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .form-group label i {
            margin-right: 8px;
        }
        
        .date-input, .time-select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e8f0fe;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: inherit;
            background: #fafbfc;
        }
        
        .date-input:focus, .time-select:focus {
            outline: none;
            border-color: #2c5f8a;
            background: white;
            box-shadow: 0 0 0 3px rgba(44,95,138,0.1);
        }
        
        .time-select {
            cursor: pointer;
        }
        
        /* Time slots grid */
        .time-slots {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
            margin-top: 10px;
        }
        
        .time-slot {
            background: #f8f9fa;
            border: 2px solid #e8f0fe;
            border-radius: 12px;
            padding: 12px 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            color: #2c5f8a;
        }
        
        .time-slot:hover {
            background: #e8f0fe;
            border-color: #2c5f8a;
            transform: translateY(-2px);
        }
        
        .time-slot.selected {
            background: #2c5f8a;
            border-color: #2c5f8a;
            color: white;
        }
        
        .time-slot.disabled {
            background: #f0f0f0;
            border-color: #ddd;
            color: #bbb;
            cursor: not-allowed;
            text-decoration: line-through;
        }
        
        .time-slot.disabled:hover {
            transform: none;
            background: #f0f0f0;
        }
        
        .selected-time-display {
            margin-top: 15px;
            padding: 12px;
            background: #e8f0fe;
            border-radius: 10px;
            font-size: 14px;
            color: #2c5f8a;
            text-align: center;
        }
        
        .book-btn {
            width: 100%;
            background: #1e8449;
            color: white;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .book-btn:hover {
            background: #145a32;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30,132,73,0.3);
        }
        
        .error {
            background: #ffe0e0;
            color: #c0392b;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #c0392b;
        }
        
        .success {
            background: #e0f5f0;
            color: #1e8449;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #1e8449;
        }
        
        .success a {
            color: #1e8449;
            font-weight: bold;
            text-decoration: underline;
            display: inline-block;
            margin-top: 10px;
        }
        
        .info-note {
            margin-top: 15px;
            padding: 12px;
            background: #fff8e0;
            border-radius: 10px;
            font-size: 12px;
            color: #b8860b;
            text-align: center;
        }
        
        @media (max-width: 600px) {
            .time-slots {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            }
            .facility-header h2 {
                font-size: 20px;
            }
            .booking-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <a href="javascript:history.back()" class="back-link">← Back to Search</a>
    
    <?php if($success): ?>
        <div class="success">
            🎉 <?php echo $success; ?>
            <br>
            <a href="my_appointments.php">→ View My Appointments</a>
        </div>
    <?php else: ?>
        
        <!-- Facility Information Card -->
        <div class="facility-card">
            <div class="facility-header">
                <h2>🏥 <?php echo htmlspecialchars($facility['name']); ?></h2>
                <p>Please confirm your appointment details below</p>
            </div>
            <div class="facility-details">
                <div class="detail-row">
                    <div class="detail-icon">📌</div>
                    <div class="detail-label">Address</div>
                    <div class="detail-text"><?php echo htmlspecialchars($facility['address']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-icon">📞</div>
                    <div class="detail-label">Phone</div>
                    <div class="detail-text"><?php echo htmlspecialchars($facility['phone']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-icon">🕒</div>
                    <div class="detail-label">Hours</div>
                    <div class="detail-text"><?php echo htmlspecialchars($facility['opening_hours']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-icon">🏷️</div>
                    <div class="detail-label">Type</div>
                    <div class="detail-text"><?php echo ucfirst($facility['type']); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Booking Form Card -->
        <div class="booking-card">
            <div class="booking-header">
                <h3>📅 Schedule Your Appointment</h3>
                <p>Select your preferred date and time</p>
            </div>
            
            <div class="booking-form">
                <?php if($error): ?>
                    <div class="error">⚠️ <?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" id="bookingForm">
                    <div class="form-group">
                        <label>📆 Select Date</label>
                        <input type="date" name="appointment_date" id="appointment_date" 
                               class="date-input" 
                               min="<?php echo date('Y-m-d'); ?>"
                               max="<?php echo date('Y-m-d', strtotime('+60 days')); ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label>⏰ Select Time</label>
                        <input type="hidden" name="appointment_time" id="selected_time" required>
                        <div class="time-slots" id="timeSlots">
                            <!-- Time slots will be populated by JavaScript -->
                            <div style="text-align: center; padding: 20px; color: #7a8e9b;">
                                Please select a date first
                            </div>
                        </div>
                        <div class="selected-time-display" id="selectedTimeDisplay">
                            No time selected yet
                        </div>
                    </div>
                    
                    <button type="submit" class="book-btn" id="submitBtn" disabled>📅 Confirm Booking</button>
                </form>
                
                <div class="info-note">
                    💡 Please arrive 10 minutes before your appointment time. 
                    Cancellations can be made up to 24 hours in advance.
                </div>
            </div>
        </div>
        
    <?php endif; ?>
</div>

<script>
    // Time slots available
    const timeSlots = [
        '09:00:00', '10:00:00', '11:00:00', 
        '13:00:00', '14:00:00', '15:00:00', '16:00:00'
    ];
    
    // Format time for display (e.g., "09:00:00" -> "9:00 AM")
    function formatTime(time) {
        let hour = parseInt(time.split(':')[0]);
        let minute = time.split(':')[1];
        let ampm = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12;
        hour = hour ? hour : 12;
        return `${hour}:${minute} ${ampm}`;
    }
    
    // Get booked times from server (you can fetch dynamically)
    let bookedTimes = <?php echo json_encode($booked_times); ?>;
    
    // Populate time slots based on selected date
    document.getElementById('appointment_date').addEventListener('change', function() {
        const selectedDate = this.value;
        const timeSlotsDiv = document.getElementById('timeSlots');
        const selectedTimeInput = document.getElementById('selected_time');
        const submitBtn = document.getElementById('submitBtn');
        
        if (!selectedDate) {
            timeSlotsDiv.innerHTML = '<div style="text-align: center; padding: 20px; color: #7a8e9b;">Please select a date first</div>';
            return;
        }
        
        // Clear previous selection
        selectedTimeInput.value = '';
        document.getElementById('selectedTimeDisplay').innerHTML = 'No time selected yet';
        submitBtn.disabled = true;
        
        // Generate time slot buttons
        let html = '';
        timeSlots.forEach(slot => {
            const isBooked = <?php echo json_encode($booked_times); ?>.includes(slot);
            const formattedTime = formatTime(slot);
            
            if (isBooked) {
                html += `<div class="time-slot disabled" data-time="${slot}">
                            ${formattedTime} ❌
                         </div>`;
            } else {
                html += `<div class="time-slot" data-time="${slot}" onclick="selectTime('${slot}')">
                            ${formattedTime} ✓
                         </div>`;
            }
        });
        
        timeSlotsDiv.innerHTML = html;
    });
    
    function selectTime(time) {
        // Remove selected class from all time slots
        document.querySelectorAll('.time-slot').forEach(slot => {
            slot.classList.remove('selected');
        });
        
        // Add selected class to clicked slot
        const clickedSlot = document.querySelector(`.time-slot[data-time="${time}"]`);
        if (clickedSlot && !clickedSlot.classList.contains('disabled')) {
            clickedSlot.classList.add('selected');
            document.getElementById('selected_time').value = time;
            document.getElementById('selectedTimeDisplay').innerHTML = `✅ Selected: ${formatTime(time)}`;
            document.getElementById('submitBtn').disabled = false;
        }
    }
    
    // Set minimum date to today
    const dateInput = document.getElementById('appointment_date');
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
</script>
</body>
</html>

























