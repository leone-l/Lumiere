<?php require_once dirname(__DIR__) . '/config.php';
$route = current_route();
$active = function ($r) use ($route) { return $route === $r ? 'active' : ''; };
?><!doctype html>
<html lang="zh-CN" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title><?= e(SITE_TITLE()) ?></title>
<meta name="description" content="<?= e(SITE_DESC()) ?>">
<style>
<?= inline_css() ?>
</style>
</head>
<body>
<header class="site-header">
  <div class="container nav-bar">
    <a class="brand" href="<?= site_url('/') ?>">
      <span class="brand-cn">光 影</span>
      <span class="brand-en">LUMIERE</span>
    </a>
    <nav class="nav-links" aria-label="主导航">
      <a class="<?= $active('/') ?>" href="<?= site_url('/') ?>">首页</a>
      <a class="<?= $active('/portfolio') ?>" href="<?= site_url('/portfolio.php') ?>">作品集</a>
      <a class="<?= $active('/about') ?>" href="<?= site_url('/about.php') ?>">关于我</a>
      <a class="<?= $active('/contact') ?>" href="<?= site_url('/contact.php') ?>">联系方式</a>
    </nav>
    <button class="theme-toggle" onclick="toggleTheme()" title="切换主题" aria-label="切换主题">
      <span class="toggle-track">
        <span class="toggle-thumb"></span>
      </span>
    </button>
  </div>
</header>
<main>
<style>
.theme-toggle {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
}
.toggle-track {
  width: 48px;
  height: 26px;
  background: var(--bg-2);
  border: 1px solid var(--line-strong);
  border-radius: 13px;
  position: relative;
  transition: all 0.3s ease;
}
.toggle-thumb {
  position: absolute;
  top: 3px;
  left: 3px;
  width: 18px;
  height: 18px;
  background: var(--text-dim);
  border-radius: 50%;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}
.toggle-thumb::before {
  content: "☾";
  font-size: 10px;
  color: var(--bg-0);
}
[data-theme="light"] .toggle-track {
  background: var(--text-dim);
}
[data-theme="light"] .toggle-thumb {
  left: 25px;
  background: var(--accent);
}
[data-theme="light"] .toggle-thumb::before {
  content: "☀";
}
</style>
<script>
function toggleTheme() {
  var html = document.documentElement;
  var current = html.getAttribute('data-theme');
  var newTheme = current === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-theme', newTheme);
  localStorage.setItem('theme', newTheme);
}
(function() {
  var saved = localStorage.getItem('theme');
  if (saved) document.documentElement.setAttribute('data-theme', saved);
})();
</script>
