<?php
session_start();
include 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, name, password, is_admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            // Check if user is admin
            if($user['is_admin'] == 1) {
                // Redirect to admin dashboard
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['is_admin'] = true;
                header("Location: admin_dashboard.php");
                exit();
            } else {
                // Redirect to regular user dashboard
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header("Location: dashboard.php");
                exit();
            }
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "No account found with this email";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Healthcare Availability App</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #2c5f8a;
            font-size: 28px;
            font-weight: 500;
        }
        
        .logo p {
            color: #5a6e7a;
            font-size: 14px;
            margin-top: 8px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
        }
        
        .login-card h2 {
            color: #2c5f8a;
            font-size: 24px;
            font-weight: 500;
            margin-bottom: 8px;
            text-align: center;
        }
        
        .login-card .subtitle {
            text-align: center;
            color: #7a8e9b;
            font-size: 14px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e8f0fe;
        }
        
        .form-group {
            margin-bottom: 22px;
        }
        
        .form-group label {
            display: block;
            color: #2c5f8a;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #2c5f8a;
            box-shadow: 0 0 0 3px rgba(44,95,138,0.1);
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
            transition: all 0.3s ease;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        
        .login-btn:hover {
            background: #1e4463;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44,95,138,0.3);
        }
        
        .error {
            background: #ffe0e0;
            color: #c0392b;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c0392b;
        }
        
        .register-link {
            text-align: center;
            color: #5a6e7a;
            font-size: 14px;
        }
        
        .register-link a {
            color: #2c5f8a;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .admin-note {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #7a8e9b;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🏥 Healthcare Availability</h1>
            <p>Find, book, and manage appointments</p>
        </div>
        
        <div class="login-card">
            <h2>Welcome Back</h2>
            <div class="subtitle">Sign in to continue to your dashboard</div>
            
            <?php if($error): ?>
                <div class="error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>📧 Email Address</label>
                    <input type="email" name="email" placeholder="you@example.com" required>
                </div>
                
                <div class="form-group">
                    <label>🔒 Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="login-btn">Sign In →</button>
            </form>
            
            <div class="register-link">
                Don't have an account? <a href="register.php">Create one now</a>
            </div>
        </div>
        
        
    </div>
</body>
</html>