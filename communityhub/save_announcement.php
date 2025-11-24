<?php
require __DIR__ . '/config.php';
require_login();

$title = trim($_POST['title'] ?? '');
$body  = trim($_POST['body'] ?? '');

if ($title === '' || $body === '') {
  exit('Title and body are required.');
}

$stmt = $pdo->prepare("INSERT INTO announcements (title, body, created_by, status) VALUES (?, ?, ?, 'pending')");
$stmt->execute([$title, $body, $_SESSION['uid']]);

header('Location: view.php');
exit;
