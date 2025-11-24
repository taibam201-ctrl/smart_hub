<?php
require __DIR__ . '/config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int)$_POST['id'];
  $action = $_POST['action'];
  if (in_array($action, ['approved','rejected'])) {
    $stmt = $pdo->prepare("UPDATE announcements SET status=? WHERE id=?");
    $stmt->execute([$action, $id]);
  }
  header('Location: admin.php');
  exit;
}

$stmt = $pdo->query("SELECT a.id,a.title,a.body,a.created_at,u.full_name AS author
                     FROM announcements a
                     JOIN users u ON u.id=a.created_by
                     WHERE a.status='pending'
                     ORDER BY a.id DESC");
$rows = $stmt->fetchAll();

include __DIR__ . '/header.php';
?>
<div class="page-header">
  <div>
    <div class="title">Admin Review</div>
    <div class="subtitle">Approve or reject pending announcements</div>
  </div>
</div>

<div class="card">
<?php if (!$rows): ?>
  <div class="empty">No pending announcements</div>
<?php else: ?>
  <table class="table">
    <thead><tr><th>Title</th><th>Posted By</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td>
          <strong><?php echo htmlspecialchars($r['title']); ?></strong><br>
          <span class="muted"><?php echo htmlspecialchars($r['body']); ?></span>
        </td>
        <td><?php echo htmlspecialchars($r['author']); ?></td>
        <td>
          <form method="post" style="display:inline">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <input type="hidden" name="action" value="approved">
            <button class="btn" type="submit">Approve</button>
          </form>
          <form method="post" style="display:inline">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <input type="hidden" name="action" value="rejected">
            <button class="btn secondary" type="submit">Reject</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
