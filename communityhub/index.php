<?php require __DIR__ . '/config.php'; $page_title = 'Contacts'; include __DIR__ . '/header.php'; ?>

<!-- content begins -->
<h1>Contacts</h1>

<!-- Create -->
<form action="save.php" method="post" autocomplete="off" class="card">
  <div class="grid two">
    <div>
      <label for="full_name">Full name</label>
      <input id="full_name" name="full_name" type="text" required />
    </div>
    <div>
      <label for="email">Email</label>
      <input id="email" name="email" type="email" required />
    </div>
  </div>
  <button type="submit" class="btn">Add Contact</button>
</form>

<!-- Read -->
<?php
  $stmt = $pdo->query("SELECT id, full_name, email, created_at FROM contacts ORDER BY id DESC");
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<table class="table" style="margin-top:16px">
  <thead>
    <tr>
      <th width="60">#</th>
      <th>Name</th>
      <th>Email</th>
      <th width="180">Created</th>
      <th width="120">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($rows): foreach ($rows as $r): ?>
      <tr>
        <td><?php echo (int)$r['id']; ?></td>
        <td><?php echo htmlspecialchars($r['full_name']); ?></td>
        <td><?php echo htmlspecialchars($r['email']); ?></td>
        <td><?php echo htmlspecialchars($r['created_at']); ?></td>
        <td>
          <a class="btn ghost" href="edit.php?id=<?php echo (int)$r['id']; ?>">Edit</a>
        </td>
      </tr>
    <?php endforeach; else: ?>
      <tr><td colspan="5" class="muted">No contacts yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
<!-- content ends -->

<?php include __DIR__ . '/footer.php'; ?>
