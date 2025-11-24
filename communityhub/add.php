<?php
require __DIR__ . '/config.php';
require_login(); // user must be logged in
include __DIR__ . '/header.php';
?>

<div class="page-header">
  <div>
    <div class="title">Add New Announcement</div>
    <div class="subtitle">Your announcement will be sent for admin approval.</div>
  </div>
</div>

<div class="card">
  <form method="post" action="save_announcement.php">
    <label>Title</label><br>
    <input type="text" name="title" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px;"><br><br>

    <label>Details</label><br>
    <textarea name="body" rows="6" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px;"></textarea><br><br>

    <button type="submit" class="btn">Submit for Approval</button>
  </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
