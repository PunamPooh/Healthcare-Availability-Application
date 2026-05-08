<?php
session_start();
include 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, name, password, is_admin FROM users WHERE email = ? AND is_admin = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['is_admin'] = true;
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "No admin account found with this email";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - Healthcare App</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e4463 0%, #2c5f8a 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .login-container {
            max-width: 420px;
            width: 100%;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: white;
            font-size: 28px;
            font-weight: 500;
        }
        .logo p {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            margin-top: 8px;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .login-card h2 {
            color: #2c5f8a;
            font-size: 24px;
            margin-bottom: 25px;
            text-align: center;
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
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e8f0fe;
            border-radius: 12px;
            font-size: 15px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2c5f8a;
        }
        .login-btn {
            width: 100%;
            background: #2c5f8a;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .login-btn:hover { background: #1e4463; }
        .error {
            background: #ffe0e0;
            color: #c0392b;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="login-container">
    <div class="logo">
        <h1>🏥 Healthcare Admin</h1>
        <p>Administrator Access Only</p>
    </div>
    <div class="login-card">
        <h2>🔐 Admin Login</h2>
        <?php if($error): ?>
            <div class="error">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>📧 Email</label>
                <input type="email" name="email" placeholder="admin@example.com" required>
            </div>
            <div class="form-group">
                <label>🔒 Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="login-btn">Login as Admin →</button>
        </form>
    </div>
    <div class="back-link">
        <a href="index.php">← Back to User Portal</a>
    </div>
</div>
</body>
</html>