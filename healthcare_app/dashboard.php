<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config/db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Healthcare App</title>
<style>
    /* Dropdown Styles */
.dropdown-container {
    display: flex;
    gap: 10px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.zip-dropdown {
    flex: 2;
    min-width: 250px;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 12px;
    font-size: 14px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.zip-dropdown:focus {
    outline: none;
    border-color: #2c5f8a;
    box-shadow: 0 0 0 3px rgba(44,95,138,0.1);
}

.zip-dropdown optgroup {
    font-weight: bold;
    color: #2c5f8a;
}

.zip-dropdown option {
    font-weight: normal;
    color: #5a6e7a;
    padding: 8px;
}

.zip-select-btn {
    background: #2c5f8a;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.zip-select-btn:hover {
    background: #1e4463;
    transform: translateY(-2px);
}
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
    
    /* Header / Navbar */
    .header {
        background: white;
        padding: 20px 25px;
        margin-bottom: 25px;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .header h2 {
        color: #2c5f8a;
        font-weight: 500;
        margin: 0;
    }
    
    .header-links a {
        color: #5a6e7a;
        text-decoration: none;
        margin-left: 20px;
        padding: 8px 12px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .header-links a:hover {
        background: #e8f0fe;
        color: #2c5f8a;
    }
    
    /* Search Box */
    .search-box {
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        text-align: center;
    }
    
    .search-box h3 {
        color: #2c5f8a;
        margin-bottom: 20px;
        font-weight: 500;
        font-size: 1.5rem;
    }
    
    .search-form {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        margin-bottom: 15px;
    }
    
    .search-form input {
        flex: 2;
        min-width: 200px;
        padding: 12px 18px;
        border: 1px solid #ddd;
        border-radius: 12px;
        font-size: 16px;
        transition: all 0.3s ease;
    }
    
    .search-form input:focus {
        outline: none;
        border-color: #2c5f8a;
        box-shadow: 0 0 0 3px rgba(44,95,138,0.1);
    }
    
    .search-form select {
        padding: 12px 18px;
        border: 1px solid #ddd;
        border-radius: 12px;
        background: white;
        font-size: 16px;
        cursor: pointer;
    }
    
    .search-form button {
        background: #2c5f8a;
        color: white;
        padding: 12px 28px;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .search-form button:hover {
        background: #1e4463;
        transform: translateY(-2px);
    }
    
    .location-btn {
        background: #5a6e7a;
        color: white;
        padding: 10px 24px;
        border: none;
        border-radius: 12px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .location-btn:hover {
        background: #3d4f5a;
        transform: translateY(-2px);
    }
    
    /* Info Box */
    .info-box {
        background: rgba(255,255,255,0.9);
        backdrop-filter: blur(10px);
        padding: 20px;
        border-radius: 15px;
        margin-top: 20px;
        border: 1px solid rgba(255,255,255,0.3);
    }
    
    .info-box h4 {
        color: #2c5f8a;
        margin-bottom: 12px;
        font-weight: 500;
    }
    
    .example-zip {
        display: inline-block;
        background: #e8f0fe;
        color: #2c5f8a;
        padding: 6px 14px;
        margin: 4px;
        border-radius: 20px;
        font-family: monospace;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .example-zip:hover {
        background: #2c5f8a;
        color: white;
        transform: scale(1.02);
    }
    
    .results {
        margin-top: 25px;
        padding: 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        text-align: center;
        color: #7a8e9b;
    }
    
    @media (max-width: 600px) {
        .header {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }
        .header-links a {
            margin: 0 10px;
        }
        .search-form {
            flex-direction: column;
        }
        .search-form input, .search-form select, .search-form button {
            width: 100%;
        }
    }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>🏥 Healthcare Availability App</h2>
        <div class="header-links">
            <a href="my_appointments.php">📋 My Appointments</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>
    
    <div class="search-box">
        <h3>🔍 Find Nearby Healthcare Services</h3>
        <form method="POST" action="search.php" class="search-form">
            <input type="text" name="address" id="address" placeholder="Enter zip code (e.g., 2100) or area name" required>
            <select name="radius">
                <option value="2">Within 2 km</option>
                <option value="5" selected>Within 5 km</option>
                <option value="10">Within 10 km</option>
                <option value="20">Within 20 km</option>
            </select>
            <button type="submit">🔍 Search Nearby</button>
        </form>
        <button onclick="getLocation()" class="location-btn">📍 Use My Current Location</button>
    </div>
    
    <div class="info-box">
    <h4>💡 Quick Select Location</h4>
    <div class="dropdown-container">
        <select id="zipDropdown" class="zip-dropdown">
            <option value="">-- Select a Copenhagen area --</option>
            <optgroup label="Copenhagen City Center">
                <option value="1000">1000 - København K (City Center)</option>
                <option value="1300">1300 - København K</option>
                <option value="1400">1400 - København K</option>
            </optgroup>
            <optgroup label="Østerbro &amp; Nordhavn">
                <option value="2100">2100 - Østerbro</option>
                <option value="2150">2150 - Nordhavn</option>
            </optgroup>
            <optgroup label="Nørrebro &amp; Bispebjerg">
                <option value="2200">2200 - Nørrebro</option>
                <option value="2400">2400 - Bispebjerg</option>
            </optgroup>
            <optgroup label="Amager &amp; Sydhavn">
                <option value="2300">2300 - Amager</option>
                <option value="2450">2450 - København SV (Sydhavn)</option>
                <option value="2770">2770 - Kastrup</option>
            </optgroup>
            <optgroup label="Frederiksberg">
                <option value="1800">1800 - Frederiksberg C</option>
                <option value="2000">2000 - Frederiksberg</option>
            </optgroup>
            <optgroup label="West Copenhagen">
                <option value="2500">2500 - Valby</option>
                <option value="2600">2600 - Glostrup</option>
                <option value="2610">2610 - Rødovre</option>
                <option value="2620">2620 - Albertslund</option>
                <option value="2650">2650 - Hvidovre</option>
                <option value="2700">2700 - Brønshøj</option>
                <option value="2720">2720 - Vanløse</option>
                <option value="2730">2730 - Herlev</option>
                <option value="2740">2740 - Skovlunde</option>
                <option value="2750">2750 - Ballerup</option>
                <option value="2760">2760 - Måløv</option>
            </optgroup>
            <optgroup label="North Copenhagen">
                <option value="2800">2800 - Kongens Lyngby</option>
                <option value="2820">2820 - Gentofte</option>
                <option value="2830">2830 - Virum</option>
                <option value="2840">2840 - Holte</option>
                <option value="2850">2850 - Nærum</option>
                <option value="2860">2860 - Søborg</option>
                <option value="2870">2870 - Dyssegård</option>
                <option value="2880">2880 - Bagsværd</option>
                <option value="2900">2900 - Hellerup</option>
                <option value="2920">2920 - Charlottenlund</option>
                <option value="2930">2930 - Klampenborg</option>
                <option value="2950">2950 - Vedbæk</option>
                <option value="2960">2960 - Rungsted</option>
                <option value="2970">2970 - Hørsholm</option>
            </optgroup>
            <optgroup label="Greater Copenhagen">
                <option value="3000">3000 - Helsingør (Elsinore)</option>
            </optgroup>
        </select>
        <button onclick="searchSelectedZip()" class="zip-select-btn">📍 Search This Area</button>
    </div>
    <p style="margin-top: 12px; font-size: 13px; color: #7a8e9b;">✨ Or type any zip code or area name in the search box above</p>
</div>
    
    <div class="results" id="results">
        <p>📌 Enter a zip code above or click "Use My Current Location" to see nearby clinics and hospitals.</p>
    </div>
</div>

<script>
    function getLocation() {
        if (navigator.geolocation) {
            document.getElementById('results').innerHTML = '<p>📍 Getting your location... Please allow permission when prompted.</p>';
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    window.location.href = "search.php?lat=" + position.coords.latitude + "&lng=" + position.coords.longitude + "&radius=5";
                },
                function(error) {
                    let errorMsg = "";
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMsg = "❌ You denied location permission. Please use the search box to enter a zip code manually.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMsg = "❌ Location unavailable. Please enter a zip code manually.";
                            break;
                        case error.TIMEOUT:
                            errorMsg = "❌ Location request timed out. Please try again.";
                            break;
                        default:
                            errorMsg = "❌ An error occurred. Please enter a zip code manually.";
                    }
                    document.getElementById('results').innerHTML = '<p style="color:#c0392b;">' + errorMsg + '</p>';
                }
            );
        } else {
            alert("Geolocation not supported. Please enter zip code manually.");
        }
    }
    
    // Click on example zip codes
    document.querySelectorAll('.example-zip').forEach(function(el) {
        el.style.cursor = 'pointer';
        el.addEventListener('click', function() {
            document.getElementById('address').value = this.textContent;
        });
    });
    function searchSelectedZip() {
    var dropdown = document.getElementById('zipDropdown');
    var selectedZip = dropdown.value;
    
    if (selectedZip === "") {
        document.getElementById('results').innerHTML = '<p style="color:#c0392b;">❌ Please select an area from the dropdown first.</p>';
        return;
    }
    
    // Get the selected option text for display
    var selectedText = dropdown.options[dropdown.selectedIndex].text;
    
    // Show loading message
    document.getElementById('results').innerHTML = '<p>📍 Searching in ' + selectedText + '...</p>';
    
    // Submit the search form programmatically
    document.getElementById('address').value = selectedZip;
    
    // Submit the form
    var form = document.querySelector('.search-form');
    form.submit();
}
</script>
</body>
</html>