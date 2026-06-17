<?php require_once __DIR__ . '/config.php';

if (is_logged_in()) {
    header('Location: ' . site_url('/admin.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = '请填写用户名和密码。';
    } elseif (login_admin($username, $password)) {
        header('Location: ' . site_url('/admin.php'));
        exit;
    } else {
        $error = '用户名或密码错误，请重试。';
    }
}
?><!doctype html>
<html lang="zh-CN" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>登录 — <?= e(SITE_TITLE()) ?></title>
<script>
  (function() {
    var saved = localStorage.getItem('theme');
    if (saved) document.documentElement.setAttribute('data-theme', saved);
  })();
</script>
<style>
<?= inline_css() ?>

/* ── 登录页专属 ── */
:root {
  --bg-0: #050505; --bg-1: #0d0d0d; --bg-2: #161616;
  --line: rgba(255,255,255,0.08); --line-strong: rgba(255,255,255,0.16);
  --text: #ececec; --text-soft: #b7b7b7; --text-dim: #7d7d7d;
  --accent: #fff; --danger: #ff6b6b;
  --radius: 0; --max: 1360px; --nav-h: 72px;
  --ease: cubic-bezier(.22,.61,.36,1);
}
[data-theme="light"] {
  --bg-0: #ffffff; --bg-1: #f8f8f8; --bg-2: #f0f0f0;
  --line: rgba(0,0,0,0.08); --line-strong: rgba(0,0,0,0.15);
  --text: #1a1a1a; --text-soft: #4a4a4a; --text-dim: #888;
  --accent: #000;
}
* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; }
body {
  font-family: 'Inter', 'Noto Sans SC', -apple-system, BlinkMacSystemFont, sans-serif;
  color: var(--text);
  background:
    radial-gradient(ellipse at 20% 20%, color-mix(in srgb, var(--accent) 6%, transparent) 0%, transparent 55%),
    radial-gradient(ellipse at 80% 80%, color-mix(in srgb, var(--accent) 5%, transparent) 0%, transparent 55%),
    linear-gradient(180deg, var(--bg-0) 0%, var(--bg-1) 50%, var(--bg-2) 100%);
  min-height: 100vh;
  -webkit-font-smoothing: antialiased;
  display: flex;
  align-items: center;
  justify-content: center;
}
a { color: inherit; text-decoration: none; }
img { max-width: 100%; display: block; }

.login-wrap {
  width: 100%;
  max-width: 400px;
  padding: 0 28px;
  animation: rise 0.8s var(--ease) both;
}
@keyframes rise {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.login-brand {
  text-align: center;
  margin-bottom: 52px;
}
.login-brand .logo {
  font-family: 'Cormorant Garamond', serif;
  font-size: 32px;
  letter-spacing: 0.35em;
  font-weight: 500;
  display: block;
}
.login-brand .sub {
  font-size: 10px;
  letter-spacing: 0.5em;
  color: var(--text-dim);
  text-transform: uppercase;
  margin-top: 6px;
}

.login-card {
  border: 1px solid var(--line-strong);
  padding: 40px;
  background: color-mix(in srgb, var(--accent) 2%, transparent);
  backdrop-filter: blur(10px);
}

.login-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: 22px;
  letter-spacing: 0.15em;
  font-weight: 500;
  margin: 0 0 30px;
}

.field { margin-bottom: 22px; }
.field label {
  display: block;
  font-size: 11px;
  letter-spacing: 0.4em;
  color: var(--text-dim);
  text-transform: uppercase;
  margin-bottom: 10px;
}
.field input {
  width: 100%;
  background: transparent;
  border: 0;
  border-bottom: 1px solid var(--line-strong);
  color: var(--text);
  font: inherit;
  padding: 12px 0;
  outline: none;
  transition: border-color .3s var(--ease);
}
.field input:focus { border-color: var(--text); }
.field input::placeholder { color: var(--text-dim); }

.login-btn {
  width: 100%;
  padding: 15px;
  background: var(--accent);
  color: var(--bg-0);
  border: 1px solid var(--accent);
  letter-spacing: 0.35em;
  font-size: 12px;
  text-transform: uppercase;
  font-weight: 500;
  cursor: pointer;
  font-family: inherit;
  margin-top: 10px;
  transition: background .3s var(--ease), color .3s var(--ease);
}
.login-btn:hover {
  background: transparent;
  color: var(--accent);
}

.error-msg {
  padding: 12px 16px;
  border: 1px solid color-mix(in srgb, var(--danger) 30%, transparent);
  color: var(--danger);
  font-size: 13px;
  margin-bottom: 20px;
  letter-spacing: 0.05em;
}

.login-footer {
  text-align: center;
  margin-top: 30px;
  font-size: 12px;
  color: var(--text-dim);
}
.login-footer a:hover { color: var(--text-soft); }
</style>
</head>
<body>

<div class="login-wrap">
  <div class="login-brand">
    <span class="logo"><?= e(SITE_NAME()) ?></span>
    <div class="sub">Admin Panel</div>
  </div>

  <div class="login-card">
    <div class="login-title">管理员登录</div>

    <?php if ($error): ?>
      <div class="error-msg"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <div class="field">
        <label for="u">用户名</label>
        <input id="u" name="username" type="text" placeholder="admin" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
      </div>
      <div class="field">
        <label for="p">密码</label>
        <input id="p" name="password" type="password" placeholder="••••••••" required>
      </div>
      <button class="login-btn" type="submit">登录</button>
    </form>
  </div>

  <div class="login-footer">
    <a href="<?= site_url('/') ?>">← 返回网站首页</a>
  </div>
</div>

</body>
</html>
