<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config/db.php';

$resource_id = $_GET['resource_id'] ?? 0;
$facility_id = $_GET['facility_id'] ?? 0;
$error = '';
$success = '';

// Get resource details
$resource_query = $conn->prepare("SELECT r.*, f.name as facility_name, f.address, f.phone FROM resources r JOIN facilities f ON r.facility_id = f.id WHERE r.id = ?");
$resource_query->bind_param("i", $resource_id);
$resource_query->execute();
$resource = $resource_query->get_result()->fetch_assoc();

if(!$resource) {
    header("Location: dashboard.php");
    exit();
}

// Get booked slots for this resource
$booked_slots = [];
$check_booked = $conn->prepare("SELECT booking_date, booking_time FROM resource_bookings WHERE resource_id = ? AND status = 'booked' AND booking_date >= CURDATE()");
$check_booked->bind_param("i", $resource_id);
$check_booked->execute();
$booked_result = $check_booked->get_result();
while($row = $booked_result->fetch_assoc()) {
    $booked_slots[$row['booking_date']][] = $row['booking_time'];
}

// Handle booking
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    $patient_notes = $_POST['patient_notes'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    // Check if slot is taken
    $check = $conn->prepare("SELECT * FROM resource_bookings WHERE resource_id = ? AND booking_date = ? AND booking_time = ? AND status = 'booked'");
    $check->bind_param("iss", $resource_id, $booking_date, $booking_time);
    $check->execute();
    
    if($check->get_result()->num_rows > 0) {
        $error = "This time slot is already booked. Please select another time.";
    } else {
        $insert = $conn->prepare("INSERT INTO resource_bookings (user_id, resource_id, booking_date, booking_time, patient_notes) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("iisss", $user_id, $resource_id, $booking_date, $booking_time, $patient_notes);
        if($insert->execute()) {
            $success = "✅ Resource booked successfully! Check your appointments.";
        } else {
            $error = "Booking failed. Please try again.";
        }
        $insert->close();
    }
    $check->close();
}

// Generate time slots based on resource availability
function generateTimeSlots($start, $end, $duration, $booked_times = []) {
    $slots = [];
    $current = new DateTime($start);
    $end_time = new DateTime($end);
    
    while($current < $end_time) {
        $slot = $current->format('H:i:s');
        $is_booked = in_array($slot, $booked_times);
        $slots[] = ['time' => $slot, 'booked' => $is_booked];
        $current->modify("+{$duration} minutes");
    }
    return $slots;
}

$available_days_array = explode(',', $resource['available_days']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Resource - <?php echo htmlspecialchars($resource['resource_name']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e8f0fe 0%, #f5f7fa 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 600px; margin: 0 auto; }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #5a6e7a;
            padding: 8px 16px;
            background: white;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .back-link:hover { background: #2c5f8a; color: white; }
        .resource-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        .resource-header {
            background: #2c5f8a;
            padding: 20px;
            color: white;
        }
        .resource-header h2 { font-weight: 500; margin-bottom: 5px; }
        .booking-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            color: #2c5f8a;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .date-input, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e8f0fe;
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
        }
        .date-input:focus, textarea:focus {
            outline: none;
            border-color: #2c5f8a;
        }
        .time-slots {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .time-slot {
            background: #f8f9fa;
            border: 2px solid #e8f0fe;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .time-slot:hover { background: #e8f0fe; border-color: #2c5f8a; }
        .time-slot.selected { background: #2c5f8a; color: white; border-color: #2c5f8a; }
        .time-slot.disabled {
            background: #f0f0f0;
            border-color: #ddd;
            color: #bbb;
            cursor: not-allowed;
            text-decoration: line-through;
        }
        .book-btn {
            width: 100%;
            background: #1e8449;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        .book-btn:hover { background: #145a32; transform: translateY(-2px); }
        .error { background: #ffe0e0; color: #c0392b; padding: 15px; border-radius: 12px; margin-bottom: 20px; }
        .success { background: #e0f5f0; color: #1e8449; padding: 20px; border-radius: 12px; text-align: center; }
        .success a { color: #1e8449; font-weight: bold; }
        textarea { resize: vertical; }
    </style>
</head>
<body>
<div class="container">
    <a href="resources.php?facility_id=<?php echo $facility_id; ?>" class="back-link">← Back to Resources</a>
    
    <div class="resource-card">
        <div class="resource-header">
            <h2>🔬 <?php echo htmlspecialchars($resource['resource_name']); ?></h2>
            <p><?php echo htmlspecialchars($resource['facility_name']); ?></p>
        </div>
        <div style="padding: 20px;">
            <p><?php echo htmlspecialchars($resource['description']); ?></p>
            <p style="margin-top: 10px;">⏱️ Duration: <?php echo $resource['duration_minutes']; ?> minutes</p>
            <?php if($resource['price_range']): ?>
                <p>💰 Price: <?php echo htmlspecialchars($resource['price_range']); ?> DKK</p>
            <?php endif; ?>
            <?php if($resource['requires_referral']): ?>
                <p>📋 Referral required from your general practitioner</p>
            <?php else: ?>
                <p>✅ No referral needed - book directly</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="booking-card">
        <?php if($success): ?>
            <div class="success">✅ <?php echo $success; ?><br><a href="my_appointments.php">View My Appointments →</a></div>
        <?php else: ?>
            <?php if($error): ?>
                <div class="error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" id="bookingForm">
                <div class="form-group">
                    <label>📆 Select Date</label>
                    <input type="date" name="booking_date" id="booking_date" class="date-input" 
                           min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+60 days')); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>⏰ Select Time</label>
                    <input type="hidden" name="booking_time" id="selected_time" required>
                    <div id="timeSlots" class="time-slots">Select a date first</div>
                </div>
                
                <div class="form-group">
                    <label>📝 Notes (Optional)</label>
                    <textarea name="patient_notes" rows="3" placeholder="Any symptoms, referral information, or additional notes for the healthcare provider..."></textarea>
                </div>
                
                <button type="submit" class="book-btn" id="submitBtn" disabled>✅ Confirm Booking</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
const bookedSlots = <?php echo json_encode($booked_slots); ?>;
const availableDays = <?php echo json_encode($available_days_array); ?>;

document.getElementById('booking_date').addEventListener('change', function() {
    const date = this.value;
    const timeSlotsDiv = document.getElementById('timeSlots');
    const selectedTimeInput = document.getElementById('selected_time');
    const submitBtn = document.getElementById('submitBtn');
    
    if (!date) return;
    
    // Check if date is valid based on available days
    const dateObj = new Date(date);
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const dayShort = dayNames[dateObj.getDay()];
    const dayFull = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][dateObj.getDay()];
    
    if (!availableDays.includes(dayShort)) {
        timeSlotsDiv.innerHTML = `<div style="grid-column:1/-1; text-align:center; padding:20px; color:#c0392b;">
            ❌ This service is not available on ${dayFull}.<br>
            Available days: ${availableDays.join(', ')}
        </div>`;
        submitBtn.disabled = true;
        return;
    }
    
    // Generate time slots based on duration
    const startTime = '<?php echo $resource['available_time_start']; ?>';
    const endTime = '<?php echo $resource['available_time_end']; ?>';
    const duration = <?php echo $resource['duration_minutes']; ?>;
    
    const bookedForDate = bookedSlots[date] || [];
    
    // Create time slots
    let slots = [];
    let current = new Date(`2000-01-01T${startTime}`);
    let end = new Date(`2000-01-01T${endTime}`);
    
    while (current < end) {
        let timeStr = current.toTimeString().slice(0, 8);
        slots.push(timeStr);
        current.setMinutes(current.getMinutes() + duration);
    }
    
    let html = '';
    slots.forEach(slot => {
        const isBooked = bookedForDate.includes(slot);
        const displayTime = new Date(`2000-01-01T${slot}`).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
        
        if (isBooked) {
            html += `<div class="time-slot disabled">${displayTime} ❌</div>`;
        } else {
            html += `<div class="time-slot" data-time="${slot}" onclick="selectTime('${slot}')">${displayTime} ✓</div>`;
        }
    });
    
    if (slots.length === 0) {
        html = '<div style="grid-column:1/-1; text-align:center; padding:20px; color:#c0392b;">No available time slots for this date.</div>';
    }
    
    timeSlotsDiv.innerHTML = html;
    selectedTimeInput.value = '';
    submitBtn.disabled = true;
});

function selectTime(time) {
    document.querySelectorAll('.time-slot').forEach(slot => slot.classList.remove('selected'));
    const clickedSlot = document.querySelector(`.time-slot[data-time="${time}"]`);
    if (clickedSlot && !clickedSlot.classList.contains('disabled')) {
        clickedSlot.classList.add('selected');
        document.getElementById('selected_time').value = time;
        document.getElementById('submitBtn').disabled = false;
    }
}
</script>
</body>
</html>