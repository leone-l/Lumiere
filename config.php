<?php
/**
 * 数据库配置与工具函数
 * 使用 SQLite（可通过切换 PDO DSN 迁移至 MySQL）。
 */

session_start();

define('DB_PATH', __DIR__ . '/data/portfolio.db');
define('DB_DSN', 'sqlite:' . DB_PATH);

// 若需要切换至 MySQL，使用下方 DSN，并建立对应用户/数据库：
// define('DB_DSN', 'mysql:host=127.0.0.1;dbname=portfolio;charset=utf8mb4');
// define('DB_USER', 'root');
// define('DB_PASS', '');

/* ------------------------------------------------------------
   数据库连接
   ------------------------------------------------------------ */
function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    $isSqlite = str_starts_with(DB_DSN, 'sqlite:');
    if ($isSqlite) {
        $dir = dirname(DB_PATH);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $pdo = new PDO(DB_DSN);
    } else {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    if ($isSqlite) $pdo->exec('PRAGMA foreign_keys = ON;');
    return $pdo;
}

/* ------------------------------------------------------------
   辅助函数
   ------------------------------------------------------------ */
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function current_route(): string {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $uri = rtrim($uri, '/');
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
    if ($base !== '' && str_starts_with($uri, $base)) {
        $uri = substr($uri, strlen($base));
    }
    if ($uri === '' || $uri === '/index.php') return '/';
    if (str_ends_with($uri, '.php')) $uri = substr($uri, 0, -4);
    return $uri === '' ? '/' : $uri;
}

function site_url(string $path = '/'): string {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
    if ($path !== '/' && !str_ends_with($path, '.php') && !str_ends_with($path, '.css') && !str_ends_with($path, '.js')) {
        if (file_exists(__DIR__ . $path . '.php')) $path = $path . '.php';
    }
    return ltrim($base, '/') === '' ? $path : rtrim($base, '/') . $path;
}

function inline_css(): string {
    $f = __DIR__ . '/assets/style.css';
    return file_exists($f) ? (string)@file_get_contents($f) : '';
}

function inline_js(): string {
    $f = __DIR__ . '/assets/main.js';
    return file_exists($f) ? (string)@file_get_contents($f) : '';
}

function flash_set(string $msg, string $type = 'info'): void {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function flash_get(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ------------------------------------------------------------
   认证函数
   ------------------------------------------------------------ */
function is_logged_in(): bool {
    return isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0;
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . site_url('/login.php'));
        exit;
    }
}

function login_admin(string $username, string $password): bool {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if (!$user) return false;
    if (!password_verify($password, $user['password_hash'])) return false;
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_name'] = $user['username'];
    return true;
}

function logout_admin(): void {
    unset($_SESSION['admin_id'], $_SESSION['admin_name']);
}

function current_admin(): ?string {
    return $_SESSION['admin_name'] ?? null;
}

/* ------------------------------------------------------------
   站点配置函数（settings 表）
   ------------------------------------------------------------ */
function get_setting(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT key, value FROM settings') as $row) {
            $cache[$row['key']] = $row['value'];
        }
    }
    return $cache[$key] ?? $default;
}

function get_settings(): array {
    $rows = db()->query('SELECT key, value FROM settings')->fetchAll();
    $map = [];
    foreach ($rows as $r) $map[$r['key']] = $r['value'];
    return $map;
}

function set_setting(string $key, string $value): void {
    $pdo = db();
    $stmt = $pdo->prepare('INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)');
    $stmt->execute([$key, $value]);
}

function set_settings(array $kv): void {
    foreach ($kv as $k => $v) set_setting($k, $v);
}

/* ------------------------------------------------------------
   动态站点常量（从数据库读取）
   ------------------------------------------------------------ */
// 基本信息
function SITE_NAME(): string { return get_setting('site_name', '光影'); }
function SITE_TITLE(): string { return get_setting('site_title', '光影 · 建筑师 / 摄影师'); }
function SITE_DESC(): string { return get_setting('site_desc', '极简主义个人摄影作品集——城市建筑、旷野遐想、建筑光影、生活碎片。'); }

// 作者信息
function AUTHOR_NAME(): string { return get_setting('author_name', '光影'); }
function AUTHOR_ROLE(): string { return get_setting('author_role', 'Architect · Photographer'); }
function AUTHOR_BIO(): string { return get_setting('author_bio', ''); }
function AUTHOR_AVATAR(): string { return get_setting('author_avatar', ''); }

// 社交联系方式
function SOCIAL_QQ(): string { return get_setting('social_qq', '123 456 789'); }
function SOCIAL_WECHAT(): string { return get_setting('social_wechat', 'lumiere_noir'); }
function SOCIAL_BEHANCE(): string { return get_setting('social_behance', 'behance.net/lumiere'); }
function SOCIAL_EMAIL(): string { return get_setting('social_email', 'hello@lumiere.studio'); }
function SOCIAL_STUDIO(): string { return get_setting('social_studio', '上海 · 徐汇'); }

// 首页文案
function HERO_KICKER(): string { return get_setting('hero_kicker', 'Portfolio · Since 2018'); }
function HERO_TITLE(): string { return get_setting('hero_title', '光 影'); }
function HERO_SUBTITLE(): string { return get_setting('hero_subtitle', 'Architect / Photographer'); }
function HOME_SECTION_TITLE(): string { return get_setting('home_section_title', '精选作品集'); }
function HOME_SECTION_DESC(): string { return get_setting('home_section_desc', '四个栏目，二十余幅影像——记录空间的呼吸，与时间留下的微弱痕迹。'); }
function HOME_BTN_TEXT(): string { return get_setting('home_btn_text', '浏览完整作品集'); }

// 作品集页文案
function PORTFOLIO_TITLE(): string { return get_setting('portfolio_title', '作品集'); }
function PORTFOLIO_DESC(): string { return get_setting('portfolio_desc', '以不对称的网格节奏，呈现每一个系列的视觉语言。'); }

// 关于我页文案
function ABOUT_TITLE(): string { return get_setting('about_title', '关于我'); }
function ABOUT_STATS_YEARS(): string { return get_setting('about_stats_years', '07'); }
function ABOUT_STATS_PROJECTS(): string { return get_setting('about_stats_projects', '24'); }
function ABOUT_STATS_FRAMES(): string { return get_setting('about_stats_frames', '∞'); }
function ABOUT_STATS_YEARS_LABEL(): string { return get_setting('about_stats_years_label', 'Years of Practice'); }
function ABOUT_STATS_PROJECTS_LABEL(): string { return get_setting('about_stats_projects_label', 'Projects'); }
function ABOUT_STATS_FRAMES_LABEL(): string { return get_setting('about_stats_frames_label', 'Frames of Light'); }

// 联系页文案
function CONTACT_TITLE(): string { return get_setting('contact_title', '联系方式'); }
function CONTACT_DESC(): string { return get_setting('contact_desc', '若您希望合作、委托拍摄，或仅仅想与我聊聊光影，请留下信息——或通过右侧任一社交方式与我联系。'); }
function CONTACT_FORM_NAME_LABEL(): string { return get_setting('contact_form_name_label', '姓名 · Name'); }
function CONTACT_FORM_EMAIL_LABEL(): string { return get_setting('contact_form_email_label', '邮箱 · Email'); }
function CONTACT_FORM_MSG_LABEL(): string { return get_setting('contact_form_msg_label', '留言 · Message'); }
function CONTACT_FORM_BTN_TEXT(): string { return get_setting('contact_form_btn_text', '发送消息'); }
function CONTACT_SIDE_TITLE(): string { return get_setting('contact_side_title', '其他方式'); }

// 页脚文案
function FOOTER_DESC(): string { return get_setting('footer_desc', '建筑师 / 摄影师 —— 在光与影之间，寻迹生活的质地。'); }
function FOOTER_COPY(): string { return get_setting('footer_copy', '© %Y 光影 LUMIERE. All rights reserved.'); }

// 首页 Hero 背景图片
function HERO_BG_IMAGE(): string { return get_setting('hero_bg_image', ''); }

// 自定义联系方式
function get_contacts(): array {
    $pdo = db();
    $stmt = $pdo->query('SELECT * FROM contacts ORDER BY sort ASC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
