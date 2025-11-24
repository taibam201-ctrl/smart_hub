<?php
require __DIR__ . '/config.php';
require_login();

$post_id = (int)($_POST['post_id'] ?? 0);
$body    = trim($_POST['body'] ?? '');
$user_id = (int)($_SESSION['uid'] ?? 0);

if ($post_id <= 0 || $body === '') {
  header("Location: post.php?id=" . $post_id);
  exit;
}

try {
  $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, body) VALUES (?, ?, ?)");
  $stmt->execute([$post_id, $user_id, $body]);
  header("Location: post.php?id=" . $post_id);
} catch (Throwable $e) {
  echo "Error saving comment: " . htmlspecialchars($e->getMessage());
}
