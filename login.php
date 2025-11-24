<?php
include 'config.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $loginInput = $conn->real_escape_string($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($loginInput && $password) {
        // Check if input is email or username
        $sql = "SELECT * FROM users WHERE email = '$loginInput' OR username = '$loginInput'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // For demo, we're checking password directly. In production, use password_hash()
            // Demo passwords: admin123 for both users
            if ($password == 'admin123' || $password == $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];
                header('Location: index.php');
                exit();
            } else {
                $message = 'Invalid password!';
                $messageType = 'error';
            }
        } else {
            $message = 'User not found! Create an account or use demo credentials.';
            $messageType = 'error';
        }
    } else {
        $message = 'Please enter username/email and password!';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Community Hub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="background">
        <div class="float-shape shape-1"></div>
        <div class="float-shape shape-2"></div>
        <div class="float-shape shape-3"></div>
    </div>

    <nav>
        <div class="nav-container">
            <div class="logo">🚀 SmartHub</div>
            <ul>
                <li><a href="login.php" class="active">Login</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="hero">
            <h1>Welcome Back</h1>
            <p>Login to your Smart Community Hub account and connect with your community</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="form-container" style="max-width: 500px;">
            <form method="POST">
                <div class="form-group">
                    <label for="login">Username or Email *</label>
                    <input type="text" id="login" name="login" placeholder="Enter your username or email" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn" style="width: 100%; margin-bottom: 1rem;">Login</button>
            </form>

            <div style="text-align: center; padding-top: 1.5rem; border-top: 1px solid rgba(124, 58, 237, 0.2); margin-bottom: 1.5rem;">
                <p style="color: rgba(226, 232, 240, 0.7);">Don't have an account?</p>
                <a href="signup.php" class="btn btn-small">Create Account</a>
            </div>

            <div style="text-align: center; padding-top: 1.5rem; border-top: 1px solid rgba(124, 58, 237, 0.2);">
                <p style="color: rgba(226, 232, 240, 0.7); margin-bottom: 1rem;">Demo Credentials:</p>
                <div style="background: rgba(124, 58, 237, 0.1); padding: 1rem; border-radius: 12px; text-align: left;">
                    <p style="margin: 0.5rem 0; font-size: 0.9rem;"><strong>Admin:</strong> admin / admin123</p>
                    <p style="margin: 0.5rem 0; font-size: 0.9rem;"><strong>User:</strong> john_doe / admin123</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>