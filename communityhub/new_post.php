<?php
// communityhub/new_post.php
require __DIR__ . '/config.php';
require_login(); // user must be logged in
include __DIR__ . '/header.php';
?>
<style>
  .form-card{background:#fff;border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:18px}
  .label{font-weight:800;margin-bottom:6px;display:block}
  .inp, .ta{
    width:100%;padding:12px;border:1px solid var(--line);border-radius:12px;background:#fff;outline:none
  }
  .inp:focus,.ta:focus{box-shadow:0 0 0 4px rgba(37,99,235,.12)}
  .row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
</style>

<div class="hub-hero" style="margin-bottom:14px">
  <h1>Create a Community Post</h1>
  <p>Share updates, ideas, questions. Admin policy ke mutabiq content clean rakhen.</p>
</div>

<div class="form-card">
  <form method="post" action="save_post.php">
    <label class="label">Title</label>
    <input class="inp" type="text" name="title" maxlength="180" required>

    <div style="height:12px"></div>

    <label class="label">Body</label>
    <textarea class="ta" name="body" rows="8" required></textarea>

    <div style="height:16px"></div>

    <div class="row">
      <button class="btn" type="submit">Publish</button>
      <a class="btn secondary" href="hub.php">Cancel</a>
    </div>
  </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
