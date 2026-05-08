<?php
session_start();
include 'config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);
    
    if ($stmt->execute()) {
        $success = "Registration successful! <a href='login.php'>Login here</a>";
    } else {
        $error = "Email already exists or error occurred";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Healthcare Availability App</title>
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
        
        .register-container {
            max-width: 480px;
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
            letter-spacing: -0.5px;
        }
        
        .logo p {
            color: #5a6e7a;
            font-size: 14px;
            margin-top: 8px;
        }
        
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            transition: all 0.3s ease;
        }
        
        .register-card h2 {
            color: #2c5f8a;
            font-size: 24px;
            font-weight: 500;
            margin-bottom: 8px;
            text-align: center;
        }
        
        .register-card .subtitle {
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
        
        .form-group label i {
            margin-right: 6px;
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
        
        .form-group input::placeholder {
            color: #b0c4d6;
        }
        
        .register-btn {
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
        
        .register-btn:hover {
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
        
        .success {
            background: #e0f5f0;
            color: #1e8449;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #1e8449;
        }
        
        .success a {
            color: #1e8449;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .login-link {
            text-align: center;
            color: #5a6e7a;
            font-size: 14px;
        }
        
        .login-link a {
            color: #2c5f8a;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 500px) {
            .register-card {
                padding: 25px;
            }
            .logo h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>🏥 Healthcare Availability</h1>
            <p>Access quality healthcare near you</p>
        </div>
        
        <div class="register-card">
            <h2>Create Account</h2>
            <div class="subtitle">Join us to book appointments easily</div>
            
            <?php if($error): ?>
                <div class="error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="success">✅ <?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>👤 Full Name</label>
                    <input type="text" name="name" placeholder="Enter your full name" required>
                </div>
                
                <div class="form-group">
                    <label>📧 Email Address</label>
                    <input type="email" name="email" placeholder="you@example.com" required>
                </div>
                
                <div class="form-group">
                    <label>🔒 Password</label>
                    <input type="password" name="password" placeholder="Create a password" required>
                </div>
                
                <button type="submit" class="register-btn">Create Account →</button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Log in</a>
            </div>
        </div>
    </div>
</body>
</html>