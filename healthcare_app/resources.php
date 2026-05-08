<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config/db.php';

$facility_id = $_GET['facility_id'] ?? 0;

// Get facility details
$facility_query = $conn->prepare("SELECT * FROM facilities WHERE id = ?");
$facility_query->bind_param("i", $facility_id);
$facility_query->execute();
$facility = $facility_query->get_result()->fetch_assoc();

if(!$facility) {
    header("Location: dashboard.php");
    exit();
}

// Get available resources for this facility
$resources_query = $conn->prepare("SELECT * FROM resources WHERE facility_id = ? AND is_available = TRUE ORDER BY resource_type, resource_name");
$resources_query->bind_param("i", $facility_id);
$resources_query->execute();
$resources = $resources_query->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Healthcare Resources - <?php echo htmlspecialchars($facility['name']); ?></title>
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
            max-width: 1000px;
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
        }
        
        .back-link:hover {
            background: #2c5f8a;
            color: white;
        }
        
        .facility-header {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-left: 5px solid #2c5f8a;
        }
        
        .facility-header h1 {
            color: #2c5f8a;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .facility-header .address {
            color: #5a6e7a;
            margin-bottom: 8px;
        }
        
        .resource-type-group {
            margin-bottom: 30px;
        }
        
        .resource-type-title {
            background: linear-gradient(135deg, #2c5f8a 0%, #1e4463 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
        }
        
        .resource-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .resource-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e8f0fe;
        }
        
        .resource-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .resource-name {
            font-size: 18px;
            font-weight: 600;
            color: #2c5f8a;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .resource-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-referral {
            background: #ffe0e0;
            color: #c0392b;
        }
        
        .badge-no-referral {
            background: #e0f5f0;
            color: #1e8449;
        }
        
        .resource-description {
            color: #5a6e7a;
            font-size: 14px;
            margin-bottom: 12px;
            line-height: 1.5;
        }
        
        .resource-details {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 13px;
        }
        
        .resource-details span {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #7a8e9b;
        }
        
        .availability {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 10px;
            margin: 15px 0;
        }
        
        .availability-title {
            font-weight: 600;
            color: #2c5f8a;
            font-size: 13px;
            margin-bottom: 8px;
        }
        
        .days {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 8px;
        }
        
        .day {
            background: #e8f0fe;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            color: #2c5f8a;
        }
        
        .time {
            color: #1e8449;
            font-weight: 600;
        }
        
        .book-resource-btn {
            width: 100%;
            background: #2c5f8a;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .book-resource-btn:hover {
            background: #1e4463;
            transform: translateY(-2px);
        }
        
        .no-resources {
            background: white;
            text-align: center;
            padding: 60px;
            border-radius: 20px;
            color: #7a8e9b;
        }
        
        @media (max-width: 700px) {
            .resource-grid {
                grid-template-columns: 1fr;
            }
            .facility-header h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <a href="javascript:history.back()" class="back-link">← Back to Search</a>
    
    <div class="facility-header">
        <h1>🏥 <?php echo htmlspecialchars($facility['name']); ?></h1>
        <div class="address">📌 <?php echo htmlspecialchars($facility['address']); ?></div>
        <div class="address">📞 <?php echo htmlspecialchars($facility['phone']); ?></div>
    </div>
    
    <h2 style="color: #2c5f8a; margin-bottom: 15px;">🔬 Available Healthcare Resources</h2>
    
    <?php if($resources->num_rows > 0): ?>
        
        <?php 
        // Group resources by type
        $grouped = [];
        while($row = $resources->fetch_assoc()) {
            $grouped[$row['resource_type']][] = $row;
        }
        
        $type_names = [
            'mri' => '🫀 MRI Scans',
            'ct_scan' => '📊 CT Scans',
            'xray' => '🦴 X-Ray Services',
            'ultrasound' => '👶 Ultrasound',
            'eye_exam' => '👁️ Eye Examinations',
            'blood_test' => '🩸 Blood Tests',
            'vaccination' => '💉 Vaccinations',
            'physiotherapy' => '🏃 Physiotherapy',
            'dental' => '🦷 Dental Services',
            'general_consultation' => '🏥 General Consultation'
        ];
        ?>
        
        <?php foreach($grouped as $type => $resources_list): ?>
            <div class="resource-type-group">
                <div class="resource-type-title">
                    <?php echo $type_names[$type] ?? ucfirst(str_replace('_', ' ', $type)); ?>
                </div>
                <div class="resource-grid">
                    <?php foreach($resources_list as $resource): ?>
                        <div class="resource-card">
                            <div class="resource-name">
                                <?php echo htmlspecialchars($resource['resource_name']); ?>
                                <?php if($resource['requires_referral']): ?>
                                    <span class="resource-badge badge-referral">📋 Referral Required</span>
                                <?php else: ?>
                                    <span class="resource-badge badge-no-referral">✅ No Referral Needed</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="resource-description">
                                <?php echo htmlspecialchars($resource['description']); ?>
                            </div>
                            
                            <div class="resource-details">
                                <span>⏱️ <?php echo $resource['duration_minutes']; ?> min</span>
                                <?php if($resource['price_range']): ?>
                                    <span>💰 <?php echo htmlspecialchars($resource['price_range']); ?> DKK</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="availability">
                                <div class="availability-title">📅 Available Days & Times:</div>
                                <div class="days">
                                    <?php 
                                    $days = explode(',', $resource['available_days']);
                                    foreach($days as $day):
                                        $day_short = [
                                            'Mon' => 'M', 'Tue' => 'T', 'Wed' => 'W', 
                                            'Thu' => 'Th', 'Fri' => 'F', 'Sat' => 'Sa', 'Sun' => 'Su'
                                        ];
                                    ?>
                                        <span class="day"><?php echo $day_short[trim($day)] ?? trim($day); ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="time">
                                    🕒 <?php echo date('g:i A', strtotime($resource['available_time_start'])); ?> - 
                                    <?php echo date('g:i A', strtotime($resource['available_time_end'])); ?>
                                </div>
                            </div>
                            
                            <form method="GET" action="book_resource.php">
                                <input type="hidden" name="resource_id" value="<?php echo $resource['id']; ?>">
                                <input type="hidden" name="facility_id" value="<?php echo $facility_id; ?>">
                                <button type="submit" class="book-resource-btn">📅 Book This Service</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
    <?php else: ?>
        <div class="no-resources">
            <p>🔍 No specialized resources currently available at this facility.</p>
            <p style="margin-top: 10px; font-size: 14px;">Please check back later or contact the facility directly.</p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>