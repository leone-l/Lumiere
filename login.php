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
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>登录 — <?= e(SITE_TITLE()) ?></title>
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
* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; }
body {
  font-family: 'Inter', 'Noto Sans SC', -apple-system, BlinkMacSystemFont, sans-serif;
  color: var(--text);
  background: radial-gradient(ellipse at 20% 20%, rgba(255,255,255,0.06) 0%, transparent 55%),
              radial-gradient(ellipse at 80% 80%, rgba(255,255,255,0.05) 0%, transparent 55%),
              linear-gradient(180deg, #000 0%, #0a0a0a 50%, #050505 100%);
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
  background: rgba(255,255,255,0.01);
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
  background: #fff;
  color: #000;
  border: 1px solid #fff;
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
  color: #fff;
}

.error-msg {
  padding: 12px 16px;
  border: 1px solid rgba(255,107,107,0.3);
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
