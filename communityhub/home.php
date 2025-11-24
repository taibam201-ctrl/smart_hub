<?php
require __DIR__ . '/config.php';
include __DIR__ . '/header.php';
?>
<style>
  /* --- Landing page skin (scoped) --- */
  .hero {
    position: relative;
    border-radius: 22px;
    overflow: hidden;
    background:
      radial-gradient(1200px 600px at 85% -10%, rgba(124,58,237,.25), transparent 60%),
      radial-gradient(900px 500px at -10% 0%, rgba(37,99,235,.25), transparent 55%),
      linear-gradient(180deg, #0f172a 0%, #111827 100%);
    color: #ffffff;
    padding: 56px 28px 0 28px;
    box-shadow: var(--shadow);
    min-height: 360px;
  }
  .hero-inner {max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1.2fr .8fr; gap: 22px; align-items: center}
  .hero h1 {font-size: 44px; line-height: 1.1; margin: 0 0 10px; font-weight: 900; letter-spacing: .2px}
  .hero p {opacity: .9; font-size: 18px; margin: 0 0 18px}
  .cta {display:flex; gap:10px; flex-wrap:wrap}
  .cta .btn {background: #60a5fa; border: 0}
  .btn.alt {background:#fff; color:#0f172a; border:1px solid var(--line)}
  .mock {
    border-radius: 18px; background: #0b1220; border: 1px solid rgba(255,255,255,.08);
    box-shadow: 0 20px 40px rgba(2,6,23,.35); padding: 14px; transform: translateY(8px);
  }
  .mock .bar {height: 10px; border-radius: 999px; background: rgba(255,255,255,.08); margin-bottom: 12px}
  .mock .card {background: #111827; border:1px solid rgba(255,255,255,.08); border-radius: 14px; padding: 14px; color:#e5e7eb}
  .mock .tag {display:inline-block; padding: 4px 10px; border-radius: 999px; background: rgba(99,102,241,.2); color:#c7d2fe; font-weight:700; font-size:12px}

  .wave {
    position:absolute; left:0; right:0; bottom:-1px; height:auto; width:100%;
  }

  .section {margin-top: 26px}
  .grid {display:grid; gap:14px; grid-template-columns: repeat(3, minmax(0,1fr))}
  .f-card {background:#fff; border:1px solid var(--line); border-radius:16px; padding:18px; box-shadow:var(--shadow)}
  .f-title{font-weight:800; margin-bottom:6px}
  .f-desc{color:var(--muted)}
  .f-badge{display:inline-block; padding:6px 10px; border-radius:999px; background:#eef2ff; color:#3730a3; font-weight:700; font-size:12px; border:1px solid #e0e7ff}

  .strip {
    margin-top: 18px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;
    background:#fff; border:1px solid var(--line); border-radius:16px; padding:12px 16px; box-shadow:var(--shadow-sm)
  }
  .avatar {width:34px;height:34px;border-radius:50%;background:#e0e7ff;display:grid;place-items:center;font-weight:800;color:#3730a3}
  .dot {width:6px;height:6px;border-radius:50%;background:#cbd5e1;display:inline-block;margin:0 6px}

  @media (max-width: 980px){
    .hero-inner {grid-template-columns: 1fr}
  }
  @media (max-width: 720px){
    .grid {grid-template-columns:1fr}
    .hero h1 {font-size: 32px}
  }
</style>

<!-- HERO -->
<section class="hero">
  <div class="hero-inner">
    <div>
      <h1>Get connected with <span style="background:linear-gradient(90deg,#60a5fa,#a78bfa);-webkit-background-clip:text;background-clip:text;color:transparent">your people</span></h1>
      <p>Announcements, community posts, comments and more — sab kuch ek jagah. Admin approval ke saath safe & clean feed.</p>
      <div class="cta">
        <a class="btn" href="view.php">View Announcements</a>
        <?php if (!empty($_SESSION['uid'])): ?>
          <a class="btn alt" href="add.php">+ Add Announcement</a>
        <?php else: ?>
          <a class="btn alt" href="login.php">Login to post</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Mock preview card (purely decorative) -->
    <div class="mock">
      <div class="bar"></div>
      <div class="card">
        <div class="tag">Community Hub</div>
        <h3 style="margin:10px 0 6px">Emaan — Dholki function</h3>
        <p style="margin:0;color:#94a3b8">Approved by Site Admin • 2025-11-02</p>
        <div style="height:10px"></div>
        <div class="strip">
          <div class="avatar">A</div>
          <span class="f-badge">Announcements</span>
          <span class="dot"></span>
          <span class="f-desc">Comments & likes coming next</span>
        </div>
      </div>
    </div>
  </div>

  <!-- wave bottom -->
  <svg class="wave" viewBox="0 0 1440 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
    <path fill="#f6f8fb" d="M0,64L48,69.3C96,75,192,85,288,80C384,75,480,53,576,53.3C672,53,768,75,864,85.3C960,96,1056,96,1152,85.3C1248,75,1344,53,1392,42.7L1440,32L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"/>
  </svg>
</section>

<!-- FEATURES -->
<section class="section container">
  <div class="grid">
    <div class="f-card">
      <div class="f-title">Announcements with Approval</div>
      <div class="f-desc">Users submit → admin approve → public feed me show. Trust-first workflow.</div>
    </div>
    <div class="f-card">
      <div class="f-title">Community Hub</div>
      <div class="f-desc">Posts, comments, likes — simple & fast PHP + MySQL foundation; scale later.</div>
    </div>
    <div class="f-card">
      <div class="f-title">Clean Admin Panel</div>
      <div class="f-desc">Approve/Reject in one click, hide comments, and keep the neighborhood civil.</div>
    </div>
  </div>
</section>

<!-- QUICK LINKS STRIP -->
<section class="section container">
  <div class="strip">
    <strong>Quick links</strong>
    <span class="dot"></span>
    <a class="pill" href="view.php">Approved Announcements</a>
    <a class="pill" href="hub.php">Community Hub</a>
    <?php if (!empty($_SESSION['uid'])): ?>
      <a class="pill" href="add.php">+ Add Announcement</a>
      <?php if (!empty($_SESSION['role']) && $_SESSION['role']==='admin'): ?>
        <a class="pill" href="admin.php">Admin Review</a>
      <?php endif; ?>
    <?php else: ?>
      <a class="pill" href="login.php">Login</a>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
