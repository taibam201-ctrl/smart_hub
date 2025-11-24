<?php
require __DIR__ . '/config.php';
$page_title = 'Post';
include __DIR__ . '/header.php';

/** ----------------- Helpers ----------------- **/
function fetchPost(PDO $pdo, int $id): ?array {
  // Try 1: join users u.name
  $tries = [
    // a) users.name
    "SELECT p.*, u.name AS _author FROM posts p LEFT JOIN users u ON u.id = p.user_id WHERE p.id = ? LIMIT 1",
    // b) users.username
    "SELECT p.*, u.username AS _author FROM posts p LEFT JOIN users u ON u.id = p.user_id WHERE p.id = ? LIMIT 1",
    // c) posts only (no join)
    "SELECT * FROM posts WHERE id = ? LIMIT 1",
  ];
  foreach ($tries as $sql) {
    try {
      $st = $pdo->prepare($sql);
      $st->execute([$id]);
      $row = $st->fetch(PDO::FETCH_ASSOC);
      if ($row) return $row;
    } catch (Throwable $e) {
      // try next shape
    }
  }
  return null;
}

function fetchLatest(PDO $pdo, int $limit = 8): array {
  // Prefer title + created_at if exists; else fallback to title only
  $tries = [
    "SELECT id, title, created_at FROM posts ORDER BY id DESC LIMIT $limit",
    "SELECT id, title FROM posts ORDER BY id DESC LIMIT $limit",
  ];
  foreach ($tries as $sql) {
    try {
      $st = $pdo->query($sql);
      $rows = $st->fetchAll(PDO::FETCH_ASSOC);
      if ($rows) return $rows;
    } catch (Throwable $e) {}
  }
  return [];
}

/** ----------------- Validate ID ----------------- **/
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
  $latest = fetchLatest($pdo);
  ?>
  <div class="card">
    <h2 style="margin:0 0 6px">Post not found</h2>
    <p class="muted">Link missing or invalid. Choose from the latest posts below.</p>
    <div style="margin-top:12px">
      <a class="btn" href="hub.php">Go to Hub</a>
      <a class="btn ghost" href="home.php">Home</a>
    </div>
  </div>
  <?php if ($latest): ?>
    <h3 style="margin:22px 0 8px">Latest posts</h3>
    <div class="grid two">
      <?php foreach ($latest as $p): ?>
      <div class="card">
        <a href="post.php?id=<?php echo (int)$p['id']; ?>" style="font-weight:700">
          <?php echo htmlspecialchars($p['title'] ?? ('Post #' . (int)$p['id'])); ?>
        </a>
        <?php if (!empty($p['created_at'])): ?>
          <div class="muted" style="margin-top:6px"><?php echo htmlspecialchars($p['created_at']); ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?php include __DIR__ . '/footer.php'; exit;
}

/** ----------------- Fetch & Render ----------------- **/
$post = fetchPost($pdo, $id);

if (!$post) {
  $latest = fetchLatest($pdo);
  ?>
  <div class="card">
    <h2 style="margin:0 0 6px">Post not found</h2>
    <p class="muted">This post may have been deleted or the link is incorrect.</p>
    <div style="margin-top:12px">
      <a class="btn" href="hub.php">Go to Hub</a>
      <a class="btn ghost" href="home.php">Home</a>
    </div>
  </div>
  <?php if ($latest): ?>
    <h3 style="margin:22px 0 8px">Latest posts</h3>
    <div class="grid two">
      <?php foreach ($latest as $p): ?>
      <div class="card">
        <a href="post.php?id=<?php echo (int)$p['id']; ?>" style="font-weight:700">
          <?php echo htmlspecialchars($p['title'] ?? ('Post #' . (int)$p['id'])); ?>
        </a>
        <?php if (!empty($p['created_at'])): ?>
          <div class="muted" style="margin-top:6px"><?php echo htmlspecialchars($p['created_at']); ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?php include __DIR__ . '/footer.php'; exit;
}

/** Resolve author safely (handles many schemas) */
$author = $post['_author']
       ?? $post['author']
       ?? $post['created_by']
       ?? $post['posted_by']
       ?? $post['username']
       ?? $post['user_name']
       ?? null;

$title = $post['title'] ?? ('Post #' . (int)$post['id']);
$created = $post['created_at'] ?? $post['created'] ?? $post['published_at'] ?? null;
$body = $post['body'] ?? $post['content'] ?? $post['description'] ?? '';

?>
<article class="card">
  <h1 style="margin-bottom:6px"><?php echo htmlspecialchars($title); ?></h1>
  <div class="muted" style="margin-bottom:16px">
    <?php if ($created) echo htmlspecialchars($created); ?>
    <?php if ($created && $author) echo " • "; ?>
    <?php if ($author) echo "by " . htmlspecialchars($author); ?>
  </div>
  <div>
    <?php echo nl2br(htmlspecialchars($body)); ?>
  </div>
  <div style="margin-top:16px">
    <a class="btn ghost" href="hub.php">← Back to Hub</a>
  </div>
</article>

<?php include __DIR__ . '/footer.php'; ?>
