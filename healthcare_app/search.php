<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config/db.php';
include 'zipcodes.php';

$results = [];
$error_msg = '';
$search_location = '';
$radius = 5;
$lat = null;
$lng = null;

// CASE 1: GPS Location (GET request with lat/lng from browser)
if(isset($_GET['lat']) && isset($_GET['lng'])) {
    $lat = floatval($_GET['lat']);
    $lng = floatval($_GET['lng']);
    $radius = isset($_GET['radius']) ? intval($_GET['radius']) : 5;
    $search_location = "Your GPS location";
    
    // CORRECTED Haversine formula
    $sql = "SELECT *, 
            (6371 * acos( 
                cos(radians($lat)) * 
                cos(radians(latitude)) * 
                cos(radians(longitude) - radians($lng)) + 
                sin(radians($lat)) * 
                sin(radians(latitude))
            )) AS distance
            FROM facilities
            HAVING distance < $radius
            ORDER BY distance";
    
    $result = $conn->query($sql);
    
    if($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    } else {
        $error_msg = "No healthcare facilities found within $radius km of your location.";
    }
}

// CASE 2: Address/Zip Code Search (POST request from dashboard form)
elseif(isset($_POST['address']) && !empty($_POST['address'])) {
    $user_input = trim($_POST['address']);
    $radius = isset($_POST['radius']) ? intval($_POST['radius']) : 5;
    $search_location = htmlspecialchars($user_input);
    
    // Try to extract zip code from user input (e.g., "2100" or "2100 København")
    preg_match('/(\d{4})/', $user_input, $matches);
    
    if(isset($matches[1])) {
        $zip = $matches[1];
        
        // Look up zip code in our coordinates array
        if(isset($zip_coordinates[$zip])) {
            $lat = $zip_coordinates[$zip]['lat'];
            $lng = $zip_coordinates[$zip]['lng'];
            $search_location = "Zip code " . $zip;
        } else {
            $error_msg = "Zip code $zip not found. Try: 2100, 2200, 2300, 2400, 2500, 2720, 2730, 2750, 2800, 2900, 3000";
        }
    } 
    // If no zip code found, try matching common Copenhagen area names
    else {
        $lower_input = strtolower($user_input);
        
        $area_map = [
            $area_map = [
    'copenhagen' => '2100',
    'københavn' => '2100',
    'kobenhavn' => '2100',
    'city center' => '1000',
    'indre by' => '1000',
    'norrebro' => '2200',
    'nørrebro' => '2200',
    'vesterbro' => '1620',
    'osterbro' => '2100',
    'østerbro' => '2100',
    'nordhavn' => '2150',
    'amager' => '2300',
    'bispebjerg' => '2400',
    'frederiksberg' => '2000',
    'valby' => '2500',
    'vanløse' => '2720',
    'vanlose' => '2720',
    'herlev' => '2730',
    'ballerup' => '2750',
    'lyngby' => '2800',
    'kongens lyngby' => '2800',
    'gentofte' => '2820',
    'hellerup' => '2900',
    'helsingør' => '3000',
    'helsingor' => '3000',
    'hvidovre' => '2650',
    'glostrup' => '2600',
    'rødovre' => '2610',
    'rodovre' => '2610',
    'brønshøj' => '2700',
    'bronshoj' => '2700',
    'søborg' => '2860',
    'sobord' => '2860',
    'bagsværd' => '2880',
    'bagsvaerd' => '2880',
    'charlottenlund' => '2920',
    'klampenborg' => '2930',
    'vedbæk' => '2950',
    'vedbaek' => '2950',
    'rungsted' => '2960',
    'hørsholm' => '2970',
    'horsholm' => '2970'
]
        ];
        
        foreach($area_map as $area => $zip_code) {
            if(strpos($lower_input, $area) !== false) {
                if(isset($zip_coordinates[$zip_code])) {
                    $lat = $zip_coordinates[$zip_code]['lat'];
                    $lng = $zip_coordinates[$zip_code]['lng'];
                    $search_location = ucfirst($area) . " area";
                    break;
                }
            }
        }
        
        if(!$lat) {
            $error_msg = "Could not find location: '$user_input'. Please enter a Copenhagen zip code (e.g., 2100) or area name (e.g., Nørrebro).";
        }
    }
    
    // If we found coordinates, perform the search
    if($lat && $lng) {
        // CORRECTED Haversine formula
        $sql = "SELECT *, 
                (6371 * acos( 
                    cos(radians($lat)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians($lng)) + 
                    sin(radians($lat)) * 
                    sin(radians(latitude))
                )) AS distance
                FROM facilities
                HAVING distance < $radius
                ORDER BY distance";
        
        $result = $conn->query($sql);
        
        if($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
        } else {
            $error_msg = "No healthcare facilities found within $radius km of $search_location.";
        }
    }
}

// CASE 3: No search performed
else {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Results - Healthcare App</title>
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
        max-width: 900px;
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
    
    .results-header {
        background: white;
        padding: 20px 25px;
        border-radius: 15px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .results-header h2 {
        color: #2c5f8a;
        font-weight: 500;
        margin-bottom: 10px;
    }
    
    .results-header p {
        color: #5a6e7a;
        margin: 5px 0;
    }
    
    .results-count {
        font-size: 14px;
        color: #2c5f8a;
        font-weight: bold;
    }
    
    .facility {
        background: white;
        padding: 20px;
        margin: 15px 0;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        border-left: 4px solid #2c5f8a;
    }
    
    .facility:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .facility h3 {
        color: #2c5f8a;
        font-weight: 500;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .facility-type {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .type-hospital {
        background: #ffe0e0;
        color: #c0392b;
    }
    
    .type-clinic {
        background: #e0f5f0;
        color: #1e8449;
    }
    
    .type-doctor_office {
        background: #e8f0fe;
        color: #2c5f8a;
    }
    
    .facility p {
        color: #5a6e7a;
        margin: 8px 0;
        line-height: 1.5;
    }
    
    .distance {
        color: #1e8449;
        font-weight: bold;
        font-size: 16px;
        margin: 12px 0 !important;
    }
    
    .book-btn {
        display: inline-block;
        background: #2c5f8a;
        color: white;
        padding: 10px 24px;
        text-decoration: none;
        border-radius: 25px;
        margin-top: 10px;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .book-btn:hover {
        background: #1e4463;
        transform: translateY(-2px);
    }
    .resources-btn {
    display: inline-block;
    background: #5a6e7a;
    color: white;
    padding: 10px 24px;
    text-decoration: none;
    border-radius: 25px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.resources-btn:hover {
    background: #3d4f5a;
    transform: translateY(-2px);
}
    .error {
        background: #ffe0e0;
        color: #c0392b;
        padding: 20px;
        border-radius: 15px;
        margin: 20px 0;
        border-left: 4px solid #c0392b;
    }
    
    .no-results {
        background: white;
        text-align: center;
        padding: 50px;
        border-radius: 15px;
        color: #7a8e9b;
    }
    
    @media (max-width: 600px) {
        body {
            padding: 15px;
        }
        .facility h3 {
            flex-direction: column;
            align-items: flex-start;
        }
        .book-btn {
            display: block;
            text-align: center;
        }
    }
</style>
</head>
<body>
<div class="container">
    <a href="dashboard.php" class="back-link">← Back to Search</a>
    
    <div class="results-header">
        <h2>🏥 Nearby Healthcare Facilities</h2>
        <p>📍 Searching near: <strong><?php echo $search_location; ?></strong></p>
        <p>📏 Radius: <strong><?php echo $radius; ?> km</strong></p>
        <p class="results-count">📊 Found: <strong><?php echo count($results); ?> facilities</strong></p>
    </div>
    
    <?php if($error_msg): ?>
        <div class="error"><?php echo $error_msg; ?></div>
    <?php endif; ?>
    
    <?php if(count($results) > 0): ?>
        <?php foreach($results as $facility): ?>
            <div class="facility">
                <h3>
                    <?php echo htmlspecialchars($facility['name']); ?>
                    <span class="facility-type type-<?php echo $facility['type']; ?>">
                        <?php 
                            switch($facility['type']) {
                                case 'hospital': echo '🏥 Hospital'; break;
                                case 'clinic': echo '🏪 Clinic'; break;
                                case 'doctor_office': echo '👨‍⚕️ Doctor'; break;
                            }
                        ?>
                    </span>
                </h3>
                <p>📌 <?php echo htmlspecialchars($facility['address']); ?></p>
                <p>📞 <?php echo htmlspecialchars($facility['phone']); ?></p>
                <p>🕒 <?php echo htmlspecialchars($facility['opening_hours']); ?></p>
                <p class="distance">📏 <?php echo round($facility['distance'], 1); ?> km away</p>
                <a <div style="display: flex; gap: 12px; margin-top: 15px; flex-wrap: wrap;">
    <a href="book.php?facility_id=<?php echo $facility['id']; ?>" class="book-btn">📅 Book Appointment</a>
    <a href="resources.php?facility_id=<?php echo $facility['id']; ?>" class="resources-btn">🔬 View Resources (MRI, CT, etc.)</a>
</div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if(count($results) == 0 && !$error_msg): ?>
        <div class="no-results">
            <p>No results found. Try increasing your search radius or a different location.</p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>