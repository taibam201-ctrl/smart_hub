<?php
require __DIR__ . '/config.php';
require_login(); require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT id, full_name, email FROM contacts WHERE id=?");
$stmt->execute([$id]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$contact) { header('Location: index.php'); exit; }

$page_title = 'Edit Contact';
include __DIR__ . '/header.php';
?>
<!-- content begins -->
<h1>Edit Contact</h1>

<form action="save.php" method="post" class="card">
  <input type="hidden" name="id" value="<?php echo (int)$contact['id']; ?>">
  <div class="grid two">
    <div>
      <label for="full_name">Full name</label>
      <input id="full_name" name="full_name" type="text" required value="<?php echo htmlspecialchars($contact['full_name']); ?>" />
    </div>
    <div>
      <label for="email">Email</label>
      <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($contact['email']); ?>" />
    </div>
  </div>
  <div style="display:flex; gap:10px; flex-wrap:wrap">
    <button type="submit" name="action" value="update" class="btn">Save</button>
    <button type="submit" name="action" value="delete" class="btn ghost" onclick="return confirm('Delete this contact?')">Delete</button>
  </div>
</form>
<!-- content ends -->
<?php include __DIR__ . '/footer.php'; ?>
