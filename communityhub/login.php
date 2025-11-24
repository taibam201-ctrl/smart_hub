<?php
require __DIR__ . '/config.php';
if (is_logged_in()) { header('Location: view.php'); exit; }

$err = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  if ($email === '' || $pass === '') {
    $err = 'Email and password required.';
  } else {
    $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, role, active FROM users WHERE email=? LIMIT 1");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if (!$u || !(int)$u['active'] || !password_verify($pass, $u['password_hash'])) {
      $err = 'Invalid credentials.';
    } else {
      $_SESSION['uid']  = (int)$u['id'];
      $_SESSION['name'] = $u['full_name'];
      $_SESSION['role'] = $u['role'];
      header('Location: view.php'); exit;
    }
  }
}

$page_title = 'Login';
include __DIR__ . '/header.php';
?>

<style>
/* ======= Scoped styles for the login page ======= */
.login-scene{
  position:relative; isolation:isolate;
  min-height:calc(100vh - 140px); /* minus header+footer */
  display:grid; grid-template-columns:1.05fr .95fr; gap:28px; align-items:center;
  border-radius:22px; overflow:hidden; background:#eef2ff;
  padding:28px; box-shadow:var(--shadow);
}
@media (max-width: 980px){
  .login-scene{grid-template-columns:1fr; padding:18px}
}

/* Decorative bubbles */
.login-scene:before,
.login-scene:after{
  content:""; position:absolute; inset:auto auto -120px -120px; width:380px; height:380px;
  background:radial-gradient(closest-side, rgba(37,99,235,.25), transparent 70%);
  border-radius:50%; z-index:0; filter:blur(2px);
}
.login-scene:after{
  inset:-100px -120px auto auto; width:460px; height:460px;
  background:radial-gradient(closest-side, rgba(124,58,237,.25), transparent 70%);
}

/* Left panel */
.login-panel{
  position:relative; z-index:1;
  background:#e3eafc; /* light blue card like reference */
  border:1px solid #dce3f7; border-radius:22px; padding:26px 24px;
  box-shadow:0 12px 28px rgba(15,23,42,.06);
}
.login-title{
  font-size:32px; font-weight:900; letter-spacing:.2px; margin:2px 0 14px;
}
.form-stack{display:grid; gap:12px; margin-top:6px}
.input{
  width:100%; padding:12px 14px; border:1px solid #cfd8ee; border-radius:12px; background:#f7f9ff;
  font-size:15px; outline:none;
}
.input:focus{border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.14)}
.login-btn{
  width:100%; justify-content:center; padding:12px 16px; font-weight:800; letter-spacing:.3px;
  background:#1e3a8a; color:#fff; border:0; border-radius:12px; cursor:pointer;
  box-shadow:0 6px 16px rgba(30,58,138,.25);
}
.login-btn:hover{filter:brightness(1.05)}
.hr-or{
  display:flex; align-items:center; gap:10px; color:var(--muted); font-size:13px; margin:14px 0 8px;
}
.hr-or:before, .hr-or:after{content:""; flex:1; height:1px; background:#dbe3ff}

/* Social buttons */
.socials{display:flex; gap:10px; flex-wrap:wrap}
.sbtn{
  flex:1 1 160px; display:flex; align-items:center; justify-content:center; gap:8px;
  padding:10px 12px; border:1px solid #d8e0f7; background:#fff; border-radius:12px;
  font-weight:600; text-decoration:none; color:#0f172a;
}
.sbtn img{width:18px; height:18px}
.helper{
  margin-top:10px; font-size:14px; color:#334155;
}
.helper a{color:#0f172a; font-weight:700; text-decoration:underline}

/* Right art */
.login-art{
  position:relative; z-index:1;
  border-radius:22px; overflow:hidden;
  background:
    radial-gradient(1200px 600px at 90% 10%, rgba(124,58,237,.25), transparent 60%),
    radial-gradient(900px 500px at 10% 0%, rgba(37,99,235,.25), transparent 55%),
    linear-gradient(120deg, #0b3aa9 0%, #0a2f89 40%, #0b1f67 100%);
  min-height:420px; display:flex; align-items:center; justify-content:center; padding:26px;
}
@media (max-width:980px){ .login-art{min-height:300px} }
.art-inner{
  width:min(520px, 100%); aspect-ratio: 16/11; border-radius:20px;
  background:radial-gradient(circle at 78% 36%, rgba(255,255,255,.18), transparent 34%),
             radial-gradient(circle at 24% 64%, rgba(255,255,255,.12), transparent 38%),
             linear-gradient(180deg, rgba(255,255,255,.14), rgba(255,255,255,.02));
  box-shadow: inset 0 0 0 1px rgba(255,255,255,.15), 0 30px 80px rgba(0,0,0,.25);
  position:relative;
}
.flower{
  position:absolute; inset:auto 10% 8% auto; width:56%;
  filter:drop-shadow(0 10px 24px rgba(0,0,0,.25));
}
/* Tiny circles on top for playful vibe */
.dot{position:absolute; width:18px; height:18px; border-radius:50%; background:rgba(255,255,255,.22)}
.dot.d1{inset:10% auto auto 12%}
.dot.d2{inset:24% auto auto 32%; width:14px; height:14px}
.dot.d3{inset:auto 18% 16% auto}
.error{
  background:#fee2e2; color:#991b1b; border:1px solid #fecaca;
  padding:10px 12px; border-radius:12px; margin:8px 0 6px;
}
</style>

<div class="login-scene">
  <!-- Left: Panel -->
  <div class="login-panel">
    <div class="login-title">Login Now</div>

    <?php if ($err): ?>
      <div class="error"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off" class="form-stack">
      <label class="sr-only" for="email">Email</label>
      <input id="email" name="email" type="email" class="input" placeholder="Email or Username" required />

      <label class="sr-only" for="password">Password</label>
      <input id="password" name="password" type="password" class="input" placeholder="Password" required />

      <button type="submit" class="login-btn">LOGIN</button>

      <div class="hr-or"><span>Or login with</span></div>

      <div class="socials">
        <a class="sbtn" href="#" onclick="return false;">
          <!-- Facebook icon -->
          <img alt="" src="data:image/svg+xml;utf8,<?xml version='1.0' encoding='UTF-8'?><svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path fill='%231b4db5' d='M22 12a10 10 0 1 0-11.6 9.87v-6.98H7.9V12h2.5V9.8c0-2.47 1.47-3.83 3.72-3.83 1.08 0 2.2.19 2.2.19v2.42h-1.24c-1.22 0-1.6.76-1.6 1.54V12h2.72l-.43 2.89h-2.29v6.98A10 10 0 0 0 22 12'/></svg>" />
          Facebook
        </a>
        <a class="sbtn" href="#" onclick="return false;">
          <!-- Google icon -->
          <img alt="" src="data:image/svg+xml;utf8,<?xml version='1.0' encoding='UTF-8'?><svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'><path fill='%23FFC107' d='M43.6 20.5H42V20H24v8h11.3A12.1 12.1 0 1 1 24 12a11.9 11.9 0 0 1 8.1 3.1l5.7-5.7A20 20 0 1 0 44 24c0-1.2-.1-2.1-.4-3.5z'/><path fill='%23FF3D00' d='M6.3 14.7l6.6 4.8A12 12 0 0 1 24 12c3.3 0 6.3 1.3 8.5 3.1l5.8-5.8A20 20 0 0 0 4 24c0 3.2.8 6.2 2.3 8.9l6.6-5.1A11.9 11.9 0 0 1 12 24c0-3.5 1.3-6.7 3.3-9.3l-9-0z'/><path fill='%2334A853' d='M24 44a20 20 0 0 0 14-5.4l-6.6-5.4A12 12 0 0 1 12 24h-8a20 20 0 0 0 20 20z'/><path fill='%234285F4' d='M43.6 20.5H42V20H24v8h11.3c-.9 3.6-4.1 6.1-8.3 6.1v8a20 20 0 0 0 16.7-13.6z'/></svg>" />
          Google
        </a>
      </div>

      <div class="helper">Not a member? <a href="#" onclick="return false;">Signup now</a></div>
      <div class="helper" style="opacity:.8">Admin demo: admin@example.com / Admin@123</div>
    </form>
  </div>

  <!-- Right: Illustration -->
  <div class="login-art">
    <div class="art-inner">
      <!-- cute abstract flower-like SVG to match reference vibe -->
      <svg class="flower" viewBox="0 0 600 360" xmlns="http://www.w3.org/2000/svg">
        <g>
          <ellipse cx="300" cy="300" rx="180" ry="28" fill="rgba(0,0,0,.18)"/>
          <path d="M280 260c-40-70 30-120 70-70 25 30-10 70-20 100h-50z" fill="#f59e0b"/>
          <path d="M330 260c10-40 60-70 90-40 18 18 0 48-25 40-10-3-24-2-38 0h-27z" fill="#ef476f"/>
          <path d="M230 260c-10-60 70-90 90-40 8 22-12 38-28 40h-62z" fill="#06d6a0"/>
          <circle cx="365" cy="180" r="26" fill="#ffbe0b"/>
          <circle cx="255" cy="170" r="18" fill="#ffd6a5"/>
          <circle cx="315" cy="150" r="14" fill="#ffd6a5"/>
          <rect x="285" y="260" width="20" height="50" rx="10" fill="#0ea5e9"/>
          <rect x="305" y="260" width="20" height="50" rx="10" fill="#22c55e"/>
          <rect x="265" y="260" width="20" height="50" rx="10" fill="#f97316"/>
        </g>
      </svg>
      <div class="dot d1"></div><div class="dot d2"></div><div class="dot d3"></div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>