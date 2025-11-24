<?php
// Database Configuration
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'smart_hub';

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die('Database Connection Error: ' . $conn->connect_error);
}

$conn->set_charset("utf8");
session_start();

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to get current user
function getCurrentUser() {
    global $conn;
    if (isLoggedIn()) {
        $userId = $_SESSION['user_id'];
        $result = $conn->query("SELECT * FROM users WHERE id = $userId");
        return $result->fetch_assoc();
    }
    return null;
}

// Helper function to check if user is admin
function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['is_admin'];
}

// <CHANGE> New helper function to check if user created the announcement
function isAnnouncementCreator($announcementId) {
    global $conn;
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    $result = $conn->query("SELECT created_by FROM announcements WHERE id = $announcementId");
    if ($result->num_rows > 0) {
        $announcement = $result->fetch_assoc();
        return $announcement['created_by'] == $user['id'];
    }
    return false;
}

// Helper function to redirect to login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}
?>