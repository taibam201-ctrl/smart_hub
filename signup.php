<?php
include 'config.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (!$username || !$email || !$password || !$confirmPassword) {
        $message = 'Please fill all fields!';
        $messageType = 'error';
    } elseif (strlen($username) < 3) {
        $message = 'Username must be at least 3 characters!';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters!';
        $messageType = 'error';
    } elseif ($password !== $confirmPassword) {
        $message = 'Passwords do not match!';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format!';
        $messageType = 'error';
    } else {
        // Check if username already exists
        $checkUsername = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($checkUsername->num_rows > 0) {
            $message = 'Username already taken! Choose another.';
            $messageType = 'error';
        } else {
            // Check if email already exists
            $checkEmail = $conn->query("SELECT id FROM users WHERE email = '$email'");
            if ($checkEmail->num_rows > 0) {
                $message = 'Email already registered! Try logging in.';
                $messageType = 'error';
            } else {
                // Insert new user (password stored as plain text for demo - use password_hash() in production)
                $sql = "INSERT INTO users (username, email, password, is_admin) VALUES ('$username', '$email', '$password', FALSE)";
                
                if ($conn->query($sql) === TRUE) {
                    $message = 'Account created successfully! Redirecting to login...';
                    $messageType = 'success';
                    header('Refresh: 2; url=login.php');
                } else {
                    $message = 'Error creating account: ' . $conn->error;
                    $messageType = 'error';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Smart Community Hub</title>
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
                <li><a href="signup.php" class="active">Sign Up</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="hero">
            <h1>Join Our Community</h1>
            <p>Create an account to organize events, register for activities, and stay updated with announcements</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="form-container" style="max-width: 600px;">
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" placeholder="Choose a unique username" required>
                    <small style="color: rgba(226, 232, 240, 0.6); font-size: 0.85rem; margin-top: 0.3rem; display: block;">Minimum 3 characters</small>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" placeholder="your@email.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                    <small style="color: rgba(226, 232, 240, 0.6); font-size: 0.85rem; margin-top: 0.3rem; display: block;">Minimum 6 characters</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
                </div>

                <button type="submit" class="btn" style="width: 100%; margin-bottom: 1rem;">Create Account</button>
            </form>

            <div style="text-align: center; padding-top: 1.5rem; border-top: 1px solid rgba(124, 58, 237, 0.2);">
                <p style="color: rgba(226, 232, 240, 0.7);">Already have an account?</p>
                <a href="login.php" class="btn btn-small" style="margin-top: 0.5rem;">Login Here</a>
            </div>
        </div>
    </div>
</body>
</html>