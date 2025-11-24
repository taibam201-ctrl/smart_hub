<?php
// communityhub/save_post.php
require __DIR__ . '/config.php';
require_login();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Location: hub.php'); exit;
}

$title = trim($_POST['title'] ?? '');
$body  = trim($_POST['body'] ?? '');
$user  = (int)($_SESSION['uid'] ?? 0);

if ($title === '' || $body === '' || $user <= 0) {
  header('Location: new_post.php?e=missing'); exit;
}

try {
  $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, body, status) VALUES (?, ?, ?, 'approved')");
  $stmt->execute([$user, $title, $body]);
  header('Location: hub.php?tab=posts&s=posted'); exit;
} catch (Throwable $e) {
  http_response_code(500);
  echo "<h3>Insert failed</h3><pre>".htmlspecialchars($e->getMessage())."</pre>";
}
