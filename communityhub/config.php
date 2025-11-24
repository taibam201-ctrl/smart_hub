<?php
/* announceapp/config.php */
$DB_HOST = '127.0.0.1';
$DB_NAME = 'community_db';
$DB_USER = 'root';  // XAMPP default
$DB_PASS = '';      // XAMPP default

$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO(
    "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
    $DB_USER, $DB_PASS, $options
  );
} catch (PDOException $e) {
  http_response_code(500);
  exit('DB connect failed: ' . htmlspecialchars($e->getMessage()));
}

if (session_status() === PHP_SESSION_NONE) { session_start(); }

/* Small helpers */
function is_logged_in(): bool { return !empty($_SESSION['uid']); }
function is_admin(): bool { return ($_SESSION['role'] ?? '') === 'admin'; }
function require_login() {
  if (!is_logged_in()) { header('Location: login.php'); exit; }
}
function require_admin() {
  if (!is_admin()) { http_response_code(403); exit('Forbidden'); }
}
