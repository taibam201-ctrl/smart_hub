<?php
// Community Hub — dual tab (Posts / Announcements) with search, cards, likes
require __DIR__ . '/config.php';

$tab    = ($_GET['tab'] ?? 'posts') === 'ann' ? 'ann' : 'posts';
$q      = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

function build_base_qs($extra = []) {
  $qs = $_GET; unset($qs['page']); $qs = array_merge($qs, $extra);
  return http_build_query($qs);
}

include __DIR__ . '/header.php';
?>
<style>
  /* scoped styles */
  .hub-hero{
    border-radius:22px; overflow:hidden; padding:28px; color:#fff;
    background: radial-gradient(900px 500px at 85% -10%, rgba(124,58,237,.25), transparent 60%),
                radial-gradient(700px 400px at -10% 0%, rgba(37,99,235,.25), transparent 55%),
                linear-gradient(180deg,#0f172a,#111827);
    box-shadow:var(--shadow);
  }
  .hub-hero h1{margin:0;font-size:34px;line-height:1.15;font-weight:900}
  .hub-hero p{margin:10px 0 0;opacity:.86}
  .tabs-lg{display:flex;gap:8px;margin-top:16px;flex-wrap:wrap}
  .tabs-lg a{padding:10px 16px;border-radius:999px;border:1px solid var(--line);background:#fff;color:#0f172a;text-decoration:none;font-weight:800}
  .tabs-lg a.active{background:var(--primary);color:#fff;border-color:transparent}
  .toolbar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin:16px 0}
  .input{padding:10px 12px;border:1px solid var(--line);border-radius:10px;background:#fff;min-width:240px}
  .cards{display:grid;gap:12px}
  .card-post{background:#fff;border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:16px}
  .p-title{font-weight:900;font-size:18px;margin:0}
  .p-meta{display:flex;gap:10px;align-items:center;color:var(--muted);font-size:14px;margin:6px 0 10px}
  .badge{display:inline-block;padding:6px 10px;border-radius:999px;background:#eef2ff;color:#3730a3;font-weight:700;border:1px solid #e0e7ff}
  .muted{color:var(--muted)}
  .act{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
  .likebtn{border:1px solid var(--line);background:#fff;border-radius:10px;padding:8px 12px;font-weight:700;cursor:pointer}
  .pilllink{border:1px solid var(--line);background:#fff;border-radius:10px;padding:8px 12px;font-weight:700;text-decoration:none;color:#0f172a}
  .pager{display:flex;gap:8px;justify-content:center;margin-top:16px}
  .pager a,.pager span{padding:8px 12px;border:1px solid var(--line);border-radius:10px;background:#fff;text-decoration:none;color:#0f172a;font-weight:700}
  .pager .current{background:var(--primary);color:#fff;border-color:transparent}
  @media (max-width:720px){.input{min-width:unset;width:100%}}
</style>

<div class="hub-hero">
  <h1>Community Hub</h1>
  <p>Real people, real updates. Posts, comments, likes — plus official announcements in one clean feed.</p>
  <div class="tabs-lg">
    <a href="?<?= build_base_qs(['tab'=>'posts']) ?>" class="<?= $tab==='posts'?'active':'' ?>">Posts</a>
    <a href="?<?= build_base_qs(['tab'=>'ann']) ?>" class="<?= $tab==='ann'?'active':'' ?>">Announcements</a>
	<a class="btn" href="new_post.php">+ New Post</a>

  </div>
</div>

<div class="toolbar">
  <form method="get" action="hub.php" style="display:flex;gap:10px;flex-wrap:wrap">
    <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
    <input class="input" type="text" name="q" placeholder="Search…" value="<?= htmlspecialchars($q) ?>">
    <button class="btn secondary" type="submit">Search</button>
    <?php if ($q !== ''): ?>
      <a class="btn secondary" href="hub.php?<?= build_base_qs(['q'=>'']) ?>">Reset</a>
    <?php endif; ?>
  </form>
  <span class="spacer"></span>
  <?php if (!empty($_SESSION['uid'])): ?>
    <?php if ($tab==='posts'): ?>
      <a class="btn" href="new_post.php">+ New Post</a>
    <?php else: ?>
      <a class="btn" href="add.php">+ New Announcement</a>
    <?php endif; ?>
  <?php else: ?>
    <a class="btn secondary" href="login.php">Login to contribute</a>
  <?php endif; ?>
</div>

<?php
/* --------- Data loaders --------- */
if ($tab === 'posts') {
  // Posts list
  $where = "WHERE p.status='approved'";
  $params = [];
  if ($q !== '') {
    $where .= " AND (p.title LIKE ? OR p.body LIKE ? OR u.full_name LIKE ?)";
    $needle = '%'.$q.'%'; $params = [$needle,$needle,$needle];
  }

  try {
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM posts p JOIN users u ON u.id=p.user_id $where");
    $cnt->execute($params);
    $total = (int)$cnt->fetchColumn();
    $pages = max(1, (int)ceil($total / $limit));

    $sql = "
      SELECT p.id,p.title,p.body,p.created_at,u.full_name,
             (SELECT COUNT(*) FROM comments c WHERE c.post_id=p.id) AS comments_count,
             (SELECT COUNT(*) FROM post_likes l WHERE l.post_id=p.id) AS likes_count
      FROM posts p
      JOIN users u ON u.id=p.user_id
      $where
      ORDER BY p.id DESC
      LIMIT $limit OFFSET $offset";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll();
  } catch (Throwable $e) {
    $rows = [];
    $total = 0; $pages = 1;
    echo '<div class="card" style="margin-top:10px"><b>Posts table missing?</b> Add it using the SQL I shared earlier.</div>';
  }

  echo '<div class="cards">';
  if (!$rows) {
    echo '<div class="card"><div class="empty">No posts found.</div></div>';
  } else {
    foreach ($rows as $r):
?>
  <article class="card-post">
    <h3 class="p-title"><?= htmlspecialchars($r['title']) ?></h3>
    <div class="p-meta">
      <span class="badge"><?= htmlspecialchars($r['full_name']) ?></span>
      <span class="muted"><?= htmlspecialchars($r['created_at']) ?></span>
    </div>
    <div class="muted" style="white-space:pre-wrap;margin-bottom:10px">
      <?= htmlspecialchars(mb_strimwidth($r['body'],0,260,'…')) ?>
    </div>
    <div class="act">
      <a class="pilllink" href="post.php?id=<?= (int)$r['id'] ?>">View</a>
      <form action="toggle_like.php" method="post" style="display:inline">
        <input type="hidden" name="post_id" value="<?= (int)$r['id'] ?>">
        <button class="likebtn" type="submit">♡ Like (<?= (int)$r['likes_count'] ?>)</button>
      </form>
      <span class="muted">💬 <?= (int)$r['comments_count'] ?></span>
    </div>
  </article>
<?php
    endforeach;
  }
  echo '</div>';

  // pager
  if ($pages > 1) {
    $base = 'hub.php?' . build_base_qs();
    echo '<nav class="pager">';
    if ($page > 1) echo '<a href="'.$base.'&page='.($page-1).'">← Prev</a>';
    $win=2; $start=max(1,$page-$win); $end=min($pages,$page+$win);
    if ($start>1) echo '<a href="'.$base.'&page=1">1</a><span>…</span>';
    for($i=$start;$i<=$end;$i++){
      echo $i===$page ? '<span class="current">'.$i.'</span>' : '<a href="'.$base.'&page='.$i.'">'.$i.'</a>';
    }
    if ($end<$pages) echo '<span>…</span><a href="'.$base.'&page='.$pages.'">'.$pages.'</a>';
    if ($page < $pages) echo '<a href="'.$base.'&page='.($page+1).'">Next →</a>';
    echo '</nav>';
  }

} else {
  // Announcements list (approved)
  $where = "WHERE a.status='approved'";
  $params = [];
  if ($q !== '') {
    $where .= " AND (a.title LIKE ? OR a.body LIKE ? OR u.full_name LIKE ?)";
    $needle = '%'.$q.'%'; $params = [$needle,$needle,$needle];
  }
  $cnt = $pdo->prepare("SELECT COUNT(*) FROM announcements a JOIN users u ON u.id=a.created_by $where");
  $cnt->execute($params);
  $total = (int)$cnt->fetchColumn();
  $pages = max(1, (int)ceil($total / $limit));

  $sql = "
    SELECT a.id,a.title,a.body,a.created_at,u.full_name AS author
    FROM announcements a JOIN users u ON u.id=a.created_by
    $where ORDER BY a.id DESC LIMIT $limit OFFSET $offset";
  $st = $pdo->prepare($sql); $st->execute($params); $rows = $st->fetchAll();

  echo '<div class="cards">';
  if (!$rows) {
    echo '<div class="card"><div class="empty">No announcements found.</div></div>';
  } else {
    foreach ($rows as $r):
?>
  <article class="card-post">
    <h3 class="p-title"><?= htmlspecialchars($r['title']) ?></h3>
    <div class="muted" style="white-space:pre-wrap;margin:8px 0 10px">
      <?= htmlspecialchars($r['body']) ?>
    </div>
    <div class="p-meta">
      <span class="badge"><?= htmlspecialchars($r['author']) ?></span>
      <span class="muted"><?= htmlspecialchars($r['created_at']) ?></span>
    </div>
  </article>
<?php
    endforeach;
  }
  echo '</div>';

  if ($pages > 1) {
    $base = 'hub.php?' . build_base_qs();
    echo '<nav class="pager">';
    if ($page > 1) echo '<a href="'.$base.'&page='.($page-1).'">← Prev</a>';
    $win=2; $start=max(1,$page-$win); $end=min($pages,$page+$win);
    if ($start>1) echo '<a href="'.$base.'&page=1">1</a><span>…</span>';
    for($i=$start;$i<=$end;$i++){
      echo $i===$page ? '<span class="current">'.$i.'</span>' : '<a href="'.$base.'&page='.$i.'">'.$i.'</a>';
    }
    if ($end<$pages) echo '<span>…</span><a href="'.$base.'&page='.$pages.'">'.$pages.'</a>';
    if ($page < $pages) echo '<a href="'.$base.'&page='.($page+1).'">Next →</a>';
    echo '</nav>';
  }
}
?>

<?php include __DIR__ . '/footer.php'; ?>
