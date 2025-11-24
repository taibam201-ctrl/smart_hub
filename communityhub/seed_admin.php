<?php
require __DIR__ . '/config.php';

$name  = 'Site Admin';
$email = 'admin@example.com';
$pass  = 'Admin@123';
$hash  = password_hash($pass, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("INSERT INTO users(full_name,email,password_hash,role)
                       VALUES(?,?,?,'admin')
                       ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), role='admin'");
$stmt->execute([$name, $email, $hash]);

echo "✅ Admin created!<br>Email: admin@example.com<br>Password: Admin@123";
