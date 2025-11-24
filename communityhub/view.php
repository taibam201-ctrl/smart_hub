<?php
// communityhub/view.php — Polished Approved Announcements
require __DIR__ . '/config.php';

/* ---------- Params ---------- */
$search = trim($_GET['q'] ?? '');
$sort   = $_GET['sort'] ?? 'new'; // new | old | title
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

/* ---------- Base SQL ---------- */
$where  = "WHERE a.status='approved'";
$params = [];

if ($search !== '') {
  // simple safe LIKE search
  $where .= " AND (a.title LIKE ? OR a.body LIKE ? OR u.full_name LIKE ?)";
  $needle = '%'.$search.'%';
  $params[] = $needle; $params[] = $needle; $params[] = $needle;
}

$order = "a.id DESC"; // default: newest first
if ($sort === 'old')   $order = "a.id ASC";
if ($sort === 'title') $order = "a.title ASC";

/* ---------- Count for pagination ---------- */
$countSql = "SELECT COUNT(*) FROM announcements a JOIN users u ON u.id=a.created_by $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $limit));

/* ---------- Data ---------- */
$listSql = "
  SELECT a.id, a.title, a.body, a.created_at, u.full_name AS author
  FROM announcements a
  JOIN users u ON u.id=a.created_by
  $where
  ORDER BY $order
  LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($listSql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

include __DIR__ . '/header.php';
?>

<!-- Local UI helpers for inputs (keeps header.css clean) -->
<style>
  .toolbar{display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin:12px 0 18px}
  .input, .select{
    padding:10px 12px; border:1px solid var(--line); border-radius:10px; background:#fff; min-width:220px
  }
  .select{min-width:160px}
  .pill{display:inline-block; padding:6px 10px; background:#fff; border:1px solid var(--line); border-radius:999px; font-weight:600}
  .cards{display:grid; gap:12px}
  .a-card{
    background:#fff; border:1px solid var(--line); border-radius:14px; box-shadow:var(--shadow); padding:16px
  }
  .a-title{font-weight:800; font-size:18px; margin:0 0 6px}
  .a-meta{display:flex; gap:10px; align-items:center; color:var(--muted); font-size:14px; margin-top:6px}
  .meta-dot{width:4px;height:4px;border-radius:50%;background:#cbd5e1;display:inline-block}
  .divider{height:1px;background:var(--line);margin:12px 0}
  .a-body{white-space:pre-wrap; color:#334155}
  .pager{display:flex; gap:8px; align-items:center; justify-content:center; margin-top:16px}
  .pager a, .pager span{
    padding:8px 12px; border:1px solid var(--line); border-radius:10px; background:#fff; text-decoration:none; color:#0f172a; font-weight:600
  }
  .pager .current{background:var(--primary); color:#fff; border-color:transparent}
  .toolbar .btn-link{border:1px solid var(--line); background:#fff; color:#0f172a}
  @media (max-width:720px){ .input{min-width:unset;width:100%} .select{min-width:unset} }
</style>

<div class="page-header">
  <div>
    <div class="title">Approved Announcements</div>
    <div class="subtitle">Only admin-approved announcements are shown to everyone.</div>
  </div>
  <div class="row">
    <?php if (!empty($_SESSION['uid'])): ?>
      <a class="btn secondary" href="add.php">+ New Announcement</a>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <!-- Toolbar -->
  <form class="toolbar" method="get" action="view.php">
    <input class="input" type="text" name="q" placeholder="Search title, details or author…" value="<?= htmlspecialchars($search) ?>">
    <select class="select" name="sort" onchange="this.form.submit()">
      <option value="new"   <?= $sort==='new'?'selected':'' ?>>Newest first</option>
      <option value="old"   <?= $sort==='old'?'selected':'' ?>>Oldest first</option>
      <option value="title" <?= $sort==='title'?'selected':'' ?>>Title A → Z</option>
    </select>
    <button class="btn btn-link" type="submit">Apply</button>
    <?php if ($search !== '' || $sort !== 'new'): ?>
      <a class="btn btn-link" href="view.php">Reset</a>
    <?php endif; ?>
    <span class="spacer"></span>
    <span class="muted">Total: <b><?= $total ?></b></span>
  </form>

  <!-- List -->
  <?php if (!$rows): ?>
    <div class="empty">
      <div class="title" style="font-size:20px;margin-bottom:6px">No announcements found</div>
      <div class="subtitle">Try clearing search or changing the sort.</div>
    </div>
  <?php else: ?>
    <div class="cards">
      <?php foreach ($rows as $r): ?>
        <article class="a-card">
          <h3 class="a-title"><?= htmlspecialchars($r['title']) ?></h3>
          <div class="a-body"><?= htmlspecialchars($r['body']) ?></div>
          <div class="divider"></div>
          <div class="a-meta">
            <span class="pill"><?= htmlspecialchars($r['author']) ?></span>
            <span class="meta-dot"></span>
            <span><?= htmlspecialchars($r['created_at']) ?></span>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
      <nav class="pager" aria-label="Pagination">
        <?php
          // build base query without page
          $qs = $_GET; unset($qs['page']);
          $base = 'view.php?'.http_build_query($qs);
        ?>
        <?php if ($page > 1): ?>
          <a href="<?= $base.'&page='.($page-1) ?>">← Prev</a>
        <?php endif; ?>

        <?php
          // compact page numbers
          $window = 2;
          $start = max(1, $page - $window);
          $end   = min($pages, $page + $window);
          if ($start > 1) echo '<a href="'.$base.'&page=1">1</a><span>…</span>';
          for ($i=$start; $i<=$end; $i++):
            if ($i === $page) echo '<span class="current">'.$i.'</span>';
            else echo '<a href="'.$base.'&page='.$i.'">'.$i.'</a>';
          endfor;
          if ($end < $pages) echo '<span>…</span><a href="'.$base.'&page='.$pages.'">'.$pages.'</a>';
        ?>

        <?php if ($page < $pages): ?>
          <a href="<?= $base.'&page='.($page+1) ?>">Next →</a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
