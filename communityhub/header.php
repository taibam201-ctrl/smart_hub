<?php /* communityhub/header.php — modern, clean UI */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . " • AnnounceApp" : "AnnounceApp"; ?></title>
  <link rel="stylesheet" href="assets/app.css" />
  <style>
    :root{
      --bg:#f6f8fb;
      --card:#ffffff;
      --ink:#0f172a;
      --muted:#64748b;
      --line:#e5e7eb;
      --primary:#2563eb;
      --primary-600:#1d4ed8;
      --accent:#7c3aed;
      --success:#16a34a;
      --danger:#dc2626;
      --radius:14px;
      --shadow:0 10px 22px rgba(15,23,42,.06), 0 2px 6px rgba(15,23,42,.04);
      --shadow-sm:0 6px 14px rgba(15,23,42,.05), 0 1px 3px rgba(15,23,42,.04);
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0; background:var(--bg); color:var(--ink);
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
      -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
    }
    a{color:var(--primary); text-decoration:none}
    a:hover{text-decoration:underline}
    .container{max-width:1100px; margin:0 auto; padding:26px 24px 48px}
    /* Top nav */
    .topbar-wrap{background:#0f172a}
    .topbar{
      max-width:1100px; margin:0 auto; padding:14px 24px; color:#e2e8f0;
      display:flex; align-items:center; gap:16px;
    }
    .brand{display:flex; align-items:center; gap:10px; font-weight:800; letter-spacing:.2px}
    .brand .logo {
      width:36px; height:36px; border-radius:10px;
      background:linear-gradient(135deg, #2563eb, #7c3aed);
      box-shadow: inset 0 0 0 2px rgba(255,255,255,.15);
    }
    .brand span{font-size:18px}
    .spacer{flex:1}
    .nav a{color:#cbd5e1; padding:8px 12px; border-radius:10px; display:inline-flex}
    .nav a:hover{background:rgba(255,255,255,.08); color:#fff; text-decoration:none}
    .btn{display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:999px; background:#60a5fa; color:#0b1220; border:0; cursor:pointer; font-weight:700; box-shadow:var(--shadow-sm)}
    .btn.secondary{background:rgba(255,255,255,.1); color:#e2e8f0; border:1px solid rgba(255,255,255,.12)}
    .btn.secondary:hover{background:rgba(255,255,255,.18)}
  </style>
</head>
<body>
  <div class="topbar-wrap">
    <nav class="topbar">
      <div class="brand">
        <div class="logo"></div>
        <span>AnnounceApp</span>
      </div>
      <div class="nav">
        <a href="home.php">Home</a>
        <a href="hub.php">Hub</a>
        <a href="index.php">Contacts</a>
        <a href="post.php">Posts</a>
        <?php if (!empty($_SESSION['is_admin'])): ?>
          <a href="admin.php">Admin</a>
        <?php endif; ?>
      </div>
      <div class="spacer"></div>
      <div class="auth">
        <?php if (!empty($_SESSION['uid'])): ?>
          <form action="logout.php" method="post" style="margin:0">
            <button class="btn secondary" type="submit">
              Logout (<?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>)
            </button>
          </form>
        <?php else: ?>
          <a class="btn secondary" href="login.php">Login</a>
        <?php endif; ?>
      </div>
    </nav>
  </div>
  <div class="container">
