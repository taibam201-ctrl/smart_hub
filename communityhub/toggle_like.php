<?php
// communityhub/toggle_like.php
require __DIR__ . '/config.php';
require_login(); // ensure user is logged in

$post_id = (int)($_POST['post_id'] ?? 0);
$uid     = (int)($_SESSION['uid'] ?? 0);

// jahan se aaye ho wapas wahin bhej dena
$back = $_POST['back'] ?? ($_SERVER['HTTP_REFERER'] ?? 'hub.php?tab=posts');

if ($post_id <= 0 || $uid <= 0) {
  header('Location: ' . $back); exit;
}

try {
  // already liked?
  $q = $pdo->prepare("SELECT 1 FROM post_likes WHERE post_id=? AND user_id=?");
  $q->execute([$post_id, $uid]);

  if ($q->fetch()) {
    // unlike
    $pdo->prepare("DELETE FROM post_likes WHERE post_id=? AND user_id=?")->execute([$post_id, $uid]);
  } else {
    // like
    $pdo->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)")->execute([$post_id, $uid]);
  }

  header('Location: ' . $back); exit;

} catch (Throwable $e) {
  http_response_code(500);
  echo "<h3>Like toggle failed</h3><pre>".htmlspecialchars($e->getMessage())."</pre>";
}
