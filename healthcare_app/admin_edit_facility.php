<?php
session_start();
if(!isset($_SESSION['is_admin'])) {
    header("Location: admin_login.php");
    exit();
}
include 'config/db.php';

$id = $_GET['id'] ?? 0;
$facility = $conn->query("SELECT * FROM facilities WHERE id = $id")->fetch_assoc();

if(!$facility) {
    header("Location: admin_facilities.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $address = $_POST['address'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $phone = $_POST['phone'];
    $opening_hours = $_POST['opening_hours'];
    
    $stmt = $conn->prepare("UPDATE facilities SET name=?, type=?, address=?, latitude=?, longitude=?, phone=?, opening_hours=? WHERE id=?");
    $stmt->bind_param("sssdsssi", $name, $type, $address, $latitude, $longitude, $phone, $opening_hours, $id);
    $stmt->execute();
    header("Location: admin_facilities.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Facility - Admin</title>
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