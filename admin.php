<?php require_once __DIR__ . '/config.php';
require_login();

$pdo = db();
$flash = null;
$tab = $_GET['tab'] ?? 'dashboard';

/* ── 处理 POST 请求 ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 站点信息
    if ($action === 'save_settings') {
        set_settings([
            'site_name' => trim($_POST['site_name'] ?? ''),
            'site_title' => trim($_POST['site_title'] ?? ''),
            'site_desc' => trim($_POST['site_desc'] ?? ''),
            'author_name' => trim($_POST['author_name'] ?? ''),
            'author_role' => trim($_POST['author_role'] ?? ''),
            'author_bio' => trim($_POST['author_bio'] ?? ''),
            'author_avatar' => trim($_POST['author_avatar'] ?? ''),
        ]);
        flash_set('站点信息已保存。', 'ok');
        header('Location: ' . site_url('/admin.php') . '?tab=settings'); exit;
    }

    // 社交链接
    if ($action === 'save_social') {
        set_settings([
            'social_qq' => trim($_POST['social_qq'] ?? ''),
            'social_wechat' => trim($_POST['social_wechat'] ?? ''),
            'social_behance' => trim($_POST['social_behance'] ?? ''),
            'social_email' => trim($_POST['social_email'] ?? ''),
            'social_studio' => trim($_POST['social_studio'] ?? ''),
        ]);
        flash_set('联系方式已保存。', 'ok');
        header('Location: ' . site_url('/admin.php') . '?tab=social'); exit;
    }

    // 修改密码
    if ($action === 'change_password') {
        $old = $_POST['old_password'] ?? '';
        $new1 = $_POST['new_password'] ?? '';
        $new2 = $_POST['new_password2'] ?? '';
        $uid = $_SESSION['admin_id'];
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$uid]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($old, $user['password_hash'])) {
            flash_set('原密码不正确。', 'err');
        } elseif ($new1 === '' || $new1 !== $new2 || strlen($new1) < 6) {
            flash_set('新密码不能为空，且两次输入必须一致，且至少 6 位。', 'err');
        } else {
            $hash = password_hash($new1, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $uid]);
            flash_set('密码已更新。', 'ok');
        }
        header('Location: ' . site_url('/admin.php') . '?tab=account'); exit;
    }

    // 添加分类
    if ($action === 'add_category') {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $sort = (int)($_POST['sort'] ?? 0);
        if ($title && $slug) {
            try {
                $pdo->prepare('INSERT INTO categories (slug, title, description, sort) VALUES (?, ?, ?, ?)')->execute([$slug, $title, $desc, $sort]);
                flash_set('分类已添加：' . $title, 'ok');
            } catch (Throwable $e) {
                flash_set('添加失败：' . $e->getMessage(), 'err');
            }
        } else {
            flash_set('请填写分类标题和 slug。', 'err');
        }
        header('Location: ' . site_url('/admin.php') . '?tab=portfolio'); exit;
    }

    // 删除分类
    if ($action === 'delete_category') {
        $cid = (int)($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM photos WHERE category_id = ?')->execute([$cid]);
        $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$cid]);
        flash_set('分类已删除。', 'ok');
        header('Location: ' . site_url('/admin.php') . '?tab=portfolio'); exit;
    }

    // 添加照片
    if ($action === 'add_photo') {
        $cid = (int)($_POST['category_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $sort = (int)($_POST['sort'] ?? 0);
        if ($cid && $title && $url) {
            try {
                $pdo->prepare('INSERT INTO photos (category_id, title, url, description, sort) VALUES (?, ?, ?, ?, ?)')->execute([$cid, $title, $url, '', $sort]);
                flash_set('照片已添加：' . $title, 'ok');
            } catch (Throwable $e) {
                flash_set('添加失败：' . $e->getMessage(), 'err');
            }
        } else {
            flash_set('请填写完整信息。', 'err');
        }
        header('Location: ' . site_url('/admin.php') . '?tab=portfolio'); exit;
    }

    // 删除照片
    if ($action === 'delete_photo') {
        $pid = (int)($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM photos WHERE id = ?')->execute([$pid]);
        flash_set('照片已删除。', 'ok');
        header('Location: ' . site_url('/admin.php') . '?tab=portfolio'); exit;
    }

    // 删除留言
    if ($action === 'delete_message') {
        $mid = (int)($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([$mid]);
        flash_set('留言已删除。', 'ok');
        header('Location: ' . site_url('/admin.php') . '?tab=messages'); exit;
    }

    // 保存首页文案
    if ($action === 'save_home_text') {
        set_settings([
            'hero_kicker' => trim($_POST['hero_kicker'] ?? ''),
            'hero_title' => trim($_POST['hero_title'] ?? ''),
            'hero_subtitle' => trim($_POST['hero_subtitle'] ?? ''),
            'hero_bg_image' => trim($_POST['hero_bg_image'] ?? ''),
            'home_section_title' => trim($_POST['home_section_title'] ?? ''),
            'home_section_desc' => trim($_POST['home_section_desc'] ?? ''),
            'home_btn_text' => trim($_POST['home_btn_text'] ?? ''),
        ]);
        flash_set('首页文案已保存。', 'ok');
        header('Location: ' . site_url('/admin.php') . '?tab=pages'); exit;
    }

    // 保存作品集页文案
    if ($action === 'save_portfolio_text') {
        set_settings([
            'portfolio_title' => trim($_POST['portfolio_title'] ?? ''),
            'portfolio_desc' => trim($_POST['portfolio_desc'] ?? ''),
        ]);
        flash_set('作品集页文案已保存。', 'ok');
        header('Location: ' . site_url('/admin.php') . '?tab=pages'); exit;
    }

    // 保存关于我页文案
    if ($action === 'save_about_text') {
        set_settings([
            'about_title' => trim($_POST['about_title'] ?? ''),
            'about_stats_years' => trim($_POST['about_stats_years'] ?? ''),
            'about_stats_projects' => trim($_POST['about_stats_projects'] ?? ''),
            'about_stats_frames' => trim($_POST['about_stats_frames'] ?? ''),
            'about_stats_years_label' => trim($_POST['about_stats_years_label'] ?? ''),
            'about_stats_projects_label' => trim($_POST['about_stats_projects_label'] ?? ''),
            'about_stats_frames_label' => trim($_POST['about_stats_frames_label'] ?? ''),
        ]);
        flash_set('关于我页文案已保存。', 'ok');
        header('Location: ' . site_url('/admin.php') . '?tab=pages'); exit;
    }

    // 保存联系页文案
    if ($action === 'save_contact_text') {
        set_settings([
            'contact_title' => trim($_POST['contact_title'] ?? ''),
            'contact_desc' => trim($_POST['contact_desc'] ?? ''),
            'contact_form_name_label' => trim($_POST['contact_form_name_label'] ?? ''),
            'contact_form_email_label' => trim($_POST['contact_form_email_label'] ?? ''),
            'contact_form_msg_label' => trim($_POST['contact_form_msg_label'] ?? ''),
            'contact_form_btn_text' => trim($_POST['contact_form_btn_text'] ?? ''),
            'contact_side_title' => trim($_POST['contact_side_title'] ?? ''),
        ]);
        flash_set('联系页文案已保存。', 'ok');
        header('Location: ' . site_url('/admin.php') . '?tab=pages'); exit;
    }

    // 保存页脚文案
    if ($action === 'save_footer_text') {
        set_settings([
            'footer_desc' => trim($_POST['footer_desc'] ?? ''),
            'footer_copy' => trim($_POST['footer_copy'] ?? ''),
        ]);
        flash_set('页脚文案已保存。', 'ok');
        header('Location: ' . site_url('/admin.php') . '?tab=pages'); exit;
    }

    // 编辑分类
    if ($action === 'edit_category') {
        $cid = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $sort = (int)($_POST['sort'] ?? 0);
        if ($cid && $title && $slug) {
            $pdo->prepare('UPDATE categories SET title = ?, slug = ?, description = ?, sort = ? WHERE id = ?')->execute([$title, $slug, $desc, $sort, $cid]);
            flash_set('分类已更新。', 'ok');
        } else {
            flash_set('请填写完整信息。', 'err');
        }
        header('Location: ' . site_url('/admin.php') . '?tab=portfolio'); exit;
    }

    // 编辑照片
    if ($action === 'edit_photo') {
        $pid = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $sort = (int)($_POST['sort'] ?? 0);
        $cid = (int)($_POST['category_id'] ?? 0);
        if ($pid && $title && $url && $cid) {
            $pdo->prepare('UPDATE photos SET title = ?, url = ?, sort = ?, category_id = ? WHERE id = ?')->execute([$title, $url, $sort, $cid, $pid]);
            flash_set('照片已更新。', 'ok');
        } else {
            flash_set('请填写完整信息。', 'err');
        }
        header('Location: ' . site_url('/admin.php') . '?tab=portfolio'); exit;
    }

    // 批量删除照片
    if ($action === 'batch_delete_photos') {
        $ids_str = $_POST['photo_ids'] ?? '';
        if ($ids_str) {
            $ids = array_filter(array_map('intval', explode(',', $ids_str)));
            if (count($ids) > 0) {
                $stmt = $pdo->prepare('DELETE FROM photos WHERE id = ?');
                foreach ($ids as $pid) {
                    $stmt->execute([$pid]);
                }
                flash_set('已删除 ' . count($ids) . ' 张照片。', 'ok');
            } else {
                flash_set('无效的 ID。', 'err');
            }
        } else {
            flash_set('请选择要删除的照片。', 'err');
        }
        header('Location: ' . site_url('/admin.php') . '?tab=portfolio'); exit;
    }

    // 登出
    if ($action === 'logout') {
        logout_admin();
        header('Location: ' . site_url('/login.php')); exit;
    }

    // 添加联系方式
    if ($action === 'add_contact') {
        $label = trim($_POST['label'] ?? '');
        $value = trim($_POST['value'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $sort = (int)($_POST['sort'] ?? 0);
        if ($label && $value) {
            $pdo->prepare('INSERT INTO contacts (label, value, icon, url, sort) VALUES (?, ?, ?, ?, ?)')->execute([$label, $value, $icon, $url, $sort]);
            flash_set('联系方式已添加。', 'ok');
        } else {
            flash_set('请填写标签和值。', 'err');
        }
        header('Location: ' . site_url('/admin.php') . '?tab=social'); exit;
    }

    // 编辑联系方式
    if ($action === 'edit_contact') {
        $id = (int)($_POST['id'] ?? 0);
        $label = trim($_POST['label'] ?? '');
        $value = trim($_POST['value'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $sort = (int)($_POST['sort'] ?? 0);
        if ($id && $label && $value) {
            $pdo->prepare('UPDATE contacts SET label = ?, value = ?, icon = ?, url = ?, sort = ? WHERE id = ?')->execute([$label, $value, $icon, $url, $sort, $id]);
            flash_set('联系方式已更新。', 'ok');
        }
        header('Location: ' . site_url('/admin.php') . '?tab=social'); exit;
    }

    // 删除联系方式
    if ($action === 'delete_contact') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare('DELETE FROM contacts WHERE id = ?')->execute([$id]);
            flash_set('联系方式已删除。', 'ok');
        }
        header('Location: ' . site_url('/admin.php') . '?tab=social'); exit;
    }
}

$flash = flash_get();

// 数据统计
$stats = [
    'categories' => (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
    'photos' => (int)$pdo->query('SELECT COUNT(*) FROM photos')->fetchColumn(),
    'messages' => (int)$pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn(),
];
$categories = $pdo->query('SELECT * FROM categories ORDER BY sort ASC, id ASC')->fetchAll();
$photos = $pdo->query('SELECT p.*, c.title AS cat_title FROM photos p LEFT JOIN categories c ON c.id = p.category_id ORDER BY c.sort ASC, p.sort ASC, p.id ASC')->fetchAll();
$messages = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();
$settings = get_settings();
$contacts = $pdo->query('SELECT * FROM contacts ORDER BY sort ASC')->fetchAll();
?>
<!doctype html>
<html lang="zh-CN" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>管理后台 — <?= e(SITE_NAME()) ?></title>
<script>
  (function() {
    var saved = localStorage.getItem('theme');
    if (saved) document.documentElement.setAttribute('data-theme', saved);
  })();
  function toggleTheme() {
    var html = document.documentElement;
    var current = html.getAttribute('data-theme');
    var newTheme = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
  }
</script>
<style>
<?= inline_css() ?>

/* ── 后台专属 ── */
:root {
  --bg-0: #050505; --bg-1: #0d0d0d; --bg-2: #161616;
  --line: rgba(255,255,255,0.08); --line-strong: rgba(255,255,255,0.16);
  --text: #ececec; --text-soft: #b7b7b7; --text-dim: #7d7d7d;
  --accent: #fff; --danger: #ff6b6b; --success: #7dd8a8;
  --max: 1400px; --nav-h: 72px;
  --ease: cubic-bezier(.22,.61,.36,1);
}
[data-theme="light"] {
  --bg-0: #ffffff; --bg-1: #f8f8f8; --bg-2: #f0f0f0;
  --line: rgba(0,0,0,0.08); --line-strong: rgba(0,0,0,0.15);
  --text: #1a1a1a; --text-soft: #4a4a4a; --text-dim: #888;
  --accent: #000; --success: #22c55e;
}
* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; }
body {
  font-family: 'Inter', 'Noto Sans SC', -apple-system, BlinkMacSystemFont, sans-serif;
  color: var(--text);
  background:
    radial-gradient(1200px 800px at 85% -10%, color-mix(in srgb, var(--accent) 8%, transparent), transparent 60%),
    linear-gradient(180deg, var(--bg-0) 0%, var(--bg-1) 40%, var(--bg-2) 100%);
  min-height: 100vh;
  -webkit-font-smoothing: antialiased;
  display: flex;
  flex-direction: column;
  transition: background .3s var(--ease), color .3s var(--ease);
}
a { color: inherit; text-decoration: none; transition: opacity .2s; }
a:hover { opacity: .7; }
img { max-width: 100%; display: block; }

/* ── 后台顶部导航 ── */
.admin-topbar {
  position: sticky; top: 0; z-index: 100;
  height: var(--nav-h);
  backdrop-filter: saturate(160%) blur(10px);
  background: color-mix(in srgb, var(--bg-1) 80%, transparent);
  border-bottom: 1px solid var(--line);
  display: flex; align-items: center;
  padding: 0 40px;
}
.admin-brand {
  font-family: 'Cormorant Garamond', serif;
  font-size: 18px; letter-spacing: 0.3em;
  flex: 1;
}
.admin-brand .sub { font-size: 10px; letter-spacing: 0.4em; color: var(--text-dim); }
.admin-topnav { display: flex; gap: 28px; font-size: 12px; letter-spacing: 0.2em; }
.admin-topnav a { color: var(--text-soft); padding: 6px 0; }
.admin-topnav a:hover { color: var(--text); opacity: 1; }
.admin-topnav a.active { color: var(--text); }
.admin-topnav a::after {
  content: ""; position: absolute; left: 0; right: 0; bottom: -2px;
  height: 1px; background: currentColor; transform: scaleX(0); transform-origin: left;
  transition: transform .3s var(--ease);
}
.admin-topnav a:hover::after,
.admin-topnav a.active::after { transform: scaleX(1); }
.admin-topnav li { position: relative; list-style: none; }

/* ── 主布局 ── */
.admin-layout {
  display: flex;
  flex: 1;
  max-width: var(--max);
  width: 100%;
  margin: 0 auto;
  padding: 0 40px;
  gap: 0;
}

/* ── 侧边栏 ── */
.admin-sidebar {
  width: 220px;
  flex-shrink: 0;
  padding: 40px 0;
  border-right: 1px solid var(--line);
}
.sidebar-nav { display: grid; gap: 4px; }
.sidebar-nav a {
  display: flex; align-items: center; gap: 12px;
  padding: 11px 14px;
  font-size: 12px; letter-spacing: 0.2em;
  color: var(--text-soft);
  border-left: 2px solid transparent;
  transition: all .25s var(--ease);
}
.sidebar-nav a:hover { color: var(--text); background: color-mix(in srgb, var(--accent) 5%, transparent); opacity: 1; }
.sidebar-nav a.active {
  color: var(--text);
  background: color-mix(in srgb, var(--accent) 6%, transparent);
  border-left-color: var(--accent);
}
.sidebar-nav a svg { width: 16px; height: 16px; flex-shrink: 0; opacity: .7; }
.msg-badge { background: var(--accent); color: var(--bg-0); font-size: 10px; padding: 2px 7px; border-radius: 0; margin-left: 4px; }

/* ── 主内容 ── */
.admin-main {
  flex: 1;
  padding: 40px 50px;
  min-width: 0;
}

.panel { display: none; }
.panel.is-active { display: block; animation: fadeIn .4s var(--ease) both; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

.panel-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: 32px; letter-spacing: 0.08em; font-weight: 500;
  margin: 0 0 6px;
}
.panel-sub { color: var(--text-dim); font-size: 13px; letter-spacing: 0.2em; text-transform: uppercase; margin-bottom: 36px; }

/* ── Flash 消息 ── */
.flash {
  padding: 14px 18px; margin-bottom: 24px;
  border: 1px solid; font-size: 13px; letter-spacing: 0.05em;
}
.flash.ok { color: var(--success); border-color: color-mix(in srgb, var(--success) 30%, transparent); background: color-mix(in srgb, var(--success) 8%, transparent); }
.flash.err { color: var(--danger); border-color: color-mix(in srgb, var(--danger) 30%, transparent); background: color-mix(in srgb, var(--danger) 8%, transparent); }

/* ── 统计卡片 ── */
.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 50px; }
.stat-card {
  border: 1px solid var(--line-strong); padding: 28px 24px;
  background: color-mix(in srgb, var(--accent) 3%, transparent);
}
.stat-card .num {
  font-family: 'Cormorant Garamond', serif;
  font-size: 52px; font-weight: 500; line-height: 1;
}
.stat-card .lbl { font-size: 11px; letter-spacing: 0.35em; color: var(--text-dim); text-transform: uppercase; margin-top: 8px; }

/* ── 表单 ── */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.form-full { grid-column: 1 / -1; }
.form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 22px; }
.form-group label {
  font-size: 11px; letter-spacing: 0.4em;
  color: var(--text-dim); text-transform: uppercase;
}
.form-group input,
.form-group textarea,
.form-group select {
  background: transparent;
  border: 0; border-bottom: 1px solid var(--line-strong);
  color: var(--text); font: inherit;
  padding: 11px 0; outline: none;
  transition: border-color .3s var(--ease);
}
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus { border-color: var(--text); }
.form-group textarea { min-height: 100px; resize: vertical; }
.form-group input[type="url"] { color: var(--text-soft); font-size: 13px; }
.form-group input::placeholder { color: var(--text-dim); }
.form-group select { appearance: none; cursor: pointer; background: color-mix(in srgb, var(--bg-2) 70%, var(--bg-1)); }

/* ── 按钮 ── */
.btn {
  display: inline-flex; align-items: center; gap: 10px;
  padding: 13px 24px;
  border: 1px solid var(--line-strong); color: var(--text-soft);
  font: inherit; font-size: 12px; letter-spacing: 0.3em; text-transform: uppercase;
  cursor: pointer; transition: all .3s var(--ease); font-weight: 500;
  background: transparent;
}
.btn:hover { background: color-mix(in srgb, var(--accent) 6%, transparent); color: var(--text); border-color: var(--text-soft); }
.btn-primary { background: var(--accent); color: var(--bg-0); border-color: var(--accent); }
.btn-primary:hover { background: transparent; color: var(--accent); }
.btn-sm { padding: 8px 14px; font-size: 11px; }
.btn-danger { color: var(--danger); border-color: color-mix(in srgb, var(--danger) 30%, transparent); }
.btn-danger:hover { background: color-mix(in srgb, var(--danger) 8%, transparent); border-color: var(--danger); }

/* ── 表格 ── */
.table { width: 100%; border-collapse: collapse; font-size: 13px; }
.table th, .table td {
  text-align: left; padding: 13px 12px;
  border-bottom: 1px solid var(--line); vertical-align: middle;
}
.table th {
  font-size: 11px; letter-spacing: 0.3em; color: var(--text-dim);
  text-transform: uppercase; font-weight: 500;
}
.table tr:hover td { background: color-mix(in srgb, var(--accent) 3%, transparent); }
.table td img {
  width: 72px; height: 48px; object-fit: cover;
  border: 1px solid var(--line);
}
.table .small { color: var(--text-dim); font-size: 11px; letter-spacing: .1em; }

/* ── 区块卡片 ── */
.card {
  border: 1px solid var(--line); padding: 28px;
  background: color-mix(in srgb, var(--accent) 2%, transparent); margin-bottom: 24px;
}
.card-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: 20px; letter-spacing: 0.1em; margin: 0 0 20px;
  font-weight: 500;
}
.card + .card { margin-top: 24px; }

/* ── 照片网格 ── */
.photo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 14px; margin-top: 20px; }
.photo-item {
  position: relative; aspect-ratio: 3/2;
  background: var(--bg-2); border: 1px solid var(--line);
  overflow: hidden;
}
.photo-item img { width: 100%; height: 100%; object-fit: cover; }
.photo-item .del {
  position: absolute; top: 6px; right: 6px;
  background: color-mix(in srgb, var(--bg-0) 80%, transparent); border: 1px solid color-mix(in srgb, var(--danger) 40%, transparent);
  color: var(--danger); font-size: 10px; letter-spacing: 0.2em;
  padding: 4px 8px; cursor: pointer; display: none;
  font-family: inherit;
}
.photo-item:hover .del { display: block; }

/* ── 留言卡片 ── */
.msg-item {
  border: 1px solid var(--line); padding: 22px; margin-bottom: 16px;
  background: color-mix(in srgb, var(--accent) 2%, transparent);
}
.msg-item .msg-head {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 10px;
}
.msg-item .msg-name { font-weight: 500; }
.msg-item .msg-date { font-size: 11px; color: var(--text-dim); letter-spacing: .1em; }
.msg-item .msg-email { font-size: 12px; color: var(--text-dim); margin-bottom: 8px; }
.msg-item .msg-content { color: var(--text-soft); line-height: 1.7; font-size: 14px; }

/* ── 分隔线 ── */
.divider { height: 1px; background: var(--line); margin: 36px 0; }

/* ── 响应式 ── */
@media (max-width: 900px) {
  .admin-layout { flex-direction: column; padding: 0 20px; }
  .admin-sidebar { width: 100%; border-right: none; border-bottom: 1px solid var(--line); padding: 16px 0; }
  .sidebar-nav { flex-direction: row; overflow-x: auto; gap: 0; }
  .sidebar-nav a { white-space: nowrap; border-left: 0; border-bottom: 2px solid transparent; padding: 10px 16px; }
  .sidebar-nav a.active { border-left-color: transparent; border-bottom-color: var(--accent); }
  .admin-main { padding: 28px 0; }
  .form-grid { grid-template-columns: 1fr; }
  .stats-grid { grid-template-columns: 1fr 1fr; }
  .admin-topbar { padding: 0 20px; }
}
</style>
</head>
<body>

<!-- 顶部栏 -->
<header class="admin-topbar">
  <div class="admin-brand">
    <div><?= e(SITE_NAME()) ?></div>
    <div class="sub">管理后台</div>
  </div>
  <ul class="admin-topnav" style="display:flex; align-items:center; gap:28px; margin:0; padding:0;">
    <li><a href="<?= site_url('/') ?>" target="_blank">预览网站 →</a></li>
    <li>
      <button type="button" class="admin-theme-toggle" onclick="toggleTheme()" style="background:none;border:1px solid var(--line);color:var(--text-dim);font:inherit;font-size:12px;letter-spacing:.2em;cursor:pointer;padding:6px 12px;transition:all .3s ease;">◐ 主题</button>
    </li>
    <li>
      <form method="post" style="display:inline;">
        <input type="hidden" name="action" value="logout">
        <button type="submit" style="background:none;border:0;color:var(--text-dim);font:inherit;font-size:12px;letter-spacing:.2em;cursor:pointer;padding:0;">退出登录</button>
      </form>
    </li>
  </ul>
</header>

<!-- 主布局 -->
<div class="admin-layout">

  <!-- 侧边栏 -->
  <aside class="admin-sidebar">
    <nav class="sidebar-nav">
      <a href="?tab=dashboard" class="<?= $tab === 'dashboard' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        仪表盘
      </a>
      <a href="?tab=settings" class="<?= $tab === 'settings' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        站点信息
      </a>
      <a href="?tab=social" class="<?= $tab === 'social' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="5" cy="12" r="2"/><circle cx="19" cy="5" r="2"/><circle cx="19" cy="19" r="2"/><path d="M7 12h10M19 7l-14 4M19 13l-14 4"/></svg>
        联系方式
      </a>
      <a href="?tab=portfolio" class="<?= $tab === 'portfolio' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="3" y1="9" x2="21" y2="9"/></svg>
        作品集
      </a>
      <a href="?tab=pages" class="<?= $tab === 'pages' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        页面文案
      </a>
      <a href="?tab=messages" class="<?= $tab === 'messages' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        留言管理 <?php if ($stats['messages']): ?><span class="msg-badge"><?= $stats['messages'] ?></span><?php endif; ?>
      </a>
      <a href="?tab=account" class="<?= $tab === 'account' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        账号安全
      </a>
    </nav>
  </aside>

  <!-- 主内容 -->
  <main class="admin-main">

    <?php if ($flash): ?>
      <div class="flash <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
    <?php endif; ?>

    <!-- ══ 仪表盘 ══ -->
    <div class="panel <?= $tab === 'dashboard' ? 'is-active' : '' ?>">
      <div class="panel-title">仪表盘</div>
      <div class="panel-sub">Overview</div>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="num"><?= $stats['categories'] ?></div>
          <div class="lbl">摄影分类</div>
        </div>
        <div class="stat-card">
          <div class="num"><?= $stats['photos'] ?></div>
          <div class="lbl">作品照片</div>
        </div>
        <div class="stat-card">
          <div class="num"><?= $stats['messages'] ?></div>
          <div class="lbl">收到留言</div>
        </div>
      </div>

      <div class="card">
        <div class="card-title">快捷操作</div>
        <div style="display:flex;gap:14px;flex-wrap:wrap;">
          <a href="?tab=settings" class="btn">编辑站点信息</a>
          <a href="?tab=portfolio" class="btn">管理作品集</a>
          <a href="?tab=messages" class="btn">查看留言</a>
          <a href="<?= site_url('/') ?>" class="btn" target="_blank">预览网站 →</a>
        </div>
      </div>
    </div>

    <!-- ══ 站点信息 ══ -->
    <div class="panel <?= $tab === 'settings' ? 'is-active' : '' ?>">
      <div class="panel-title">站点信息</div>
      <div class="panel-sub">Site Settings</div>
      <form method="post">
        <input type="hidden" name="action" value="save_settings">
        <div class="card">
          <div class="card-title">基本信息</div>
          <div class="form-grid">
            <div class="form-group">
              <label>网站名称</label>
              <input name="site_name" value="<?= e($settings['site_name'] ?? '光影') ?>" placeholder="光影">
            </div>
            <div class="form-group">
              <label>页面标题</label>
              <input name="site_title" value="<?= e($settings['site_title'] ?? '光影 · 建筑师 / 摄影师') ?>" placeholder="光影 · 建筑师 / 摄影师">
            </div>
            <div class="form-group form-full">
              <label>网站描述</label>
              <input name="site_desc" value="<?= e($settings['site_desc'] ?? '') ?>" placeholder="极简主义个人摄影作品集...">
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-title">个人信息</div>
          <div class="form-grid">
            <div class="form-group">
              <label>姓名 / 艺名</label>
              <input name="author_name" value="<?= e($settings['author_name'] ?? '光影') ?>" placeholder="光影">
            </div>
            <div class="form-group">
              <label>身份描述</label>
              <input name="author_role" value="<?= e($settings['author_role'] ?? 'Architect · Photographer') ?>" placeholder="Architect · Photographer">
            </div>
            <div class="form-group form-full">
              <label>头像图片 URL</label>
              <input name="author_avatar" type="url" value="<?= e($settings['author_avatar'] ?? '') ?>" placeholder="https://...">
            </div>
            <div class="form-group form-full">
              <label>个人简介（每行一段）</label>
              <textarea name="author_bio" placeholder="我是...&#10;我的作品...&#10;..."><?= e($settings['author_bio'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
        <button class="btn btn-primary" type="submit">保存信息</button>
      </form>
    </div>

    <!-- ══ 联系方式 ══ -->
    <div class="panel <?= $tab === 'social' ? 'is-active' : '' ?>">
      <div class="panel-title">联系方式</div>
      <div class="panel-sub">Contact Management</div>
      
      <div class="card">
        <div class="card-title">自定义联系方式</div>
        <p style="color:var(--text-dim);font-size:13px;margin:0 0 16px;">可添加自定义联系方式，如微博、小红书、Instagram 等。</p>
        
        <form method="post" style="display:grid;grid-template-columns:100px 1fr 1fr 80px;gap:12px;align-end;margin-bottom:24px;">
          <input type="hidden" name="action" value="add_contact">
          <div class="form-group" style="margin-bottom:0;">
            <label>标签</label>
            <input name="label" placeholder="微信" required>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label>值</label>
            <input name="value" placeholder="your_id" required>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label>链接（可选）</label>
            <input name="url" type="url" placeholder="https://...">
          </div>
          <button class="btn btn-primary" type="submit" style="align-self:end;">添加</button>
        </form>

        <?php if (empty($contacts)): ?>
          <p style="color:var(--text-dim);font-size:13px;">暂无联系方式。</p>
        <?php else: ?>
          <table class="table">
            <thead><tr><th>排序</th><th>标签</th><th>值</th><th>链接</th><th style="text-align:right;">操作</th></tr></thead>
            <tbody>
              <?php foreach ($contacts as $c): ?>
                <tr>
                  <td style="width:60px;"><?= (int)$c['sort'] ?></td>
                  <td><?= e($c['label']) ?></td>
                  <td><?= e($c['value']) ?></td>
                  <td class="small" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;"><?= $c['url'] ? e($c['url']) : '-' ?></td>
                  <td style="text-align:right;">
                    <button type="button" class="btn btn-sm" onclick="openContactModal(<?= (int)$c['id'] ?>, '<?= e(addslashes($c['label'])) ?>', '<?= e(addslashes($c['value'])) ?>', '<?= e(addslashes($c['url'] ?? '')) ?>', <?= (int)$c['sort'] ?>)">编辑</button>
                    <form method="post" style="display:inline;">
                      <input type="hidden" name="action" value="delete_contact">
                      <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('确定删除？');">删除</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <div id="contactModal" class="modal" style="display:none;">
        <div class="modal-content">
          <div class="modal-header">
            <span class="modal-title">编辑联系方式</span>
            <button type="button" class="modal-close" onclick="closeContactModal()">×</button>
          </div>
          <form method="post">
            <input type="hidden" name="action" value="edit_contact">
            <input type="hidden" name="id" id="contact_id">
            <div class="form-grid">
              <div class="form-group">
                <label>标签</label>
                <input name="label" id="contact_label" required>
              </div>
              <div class="form-group">
                <label>排序</label>
                <input name="sort" type="number" id="contact_sort" value="0">
              </div>
              <div class="form-group form-full">
                <label>值</label>
                <input name="value" id="contact_value" required>
              </div>
              <div class="form-group form-full">
                <label>链接（可选）</label>
                <input name="url" id="contact_url" type="url">
              </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:16px;">
              <button type="submit" class="btn btn-primary">保存</button>
              <button type="button" class="btn" onclick="closeContactModal()">取消</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- ══ 作品集 ══ -->
    <div class="panel <?= $tab === 'portfolio' ? 'is-active' : '' ?>">
      <div class="panel-title">作品集</div>
      <div class="panel-sub">Portfolio Management</div>

      <!-- 添加分类 -->
      <div class="card">
        <div class="card-title">添加分类</div>
        <form method="post" style="display:grid;grid-template-columns:1fr 1fr 2fr 80px;gap:16px;align-end;">
          <input type="hidden" name="action" value="add_category">
          <div class="form-group" style="margin-bottom:0;">
            <label>Slug</label>
            <input name="slug" placeholder="city" required>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label>标题</label>
            <input name="title" placeholder="城市建筑" required>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label>描述</label>
            <input name="description" placeholder="简要描述...">
          </div>
          <button class="btn btn-primary" type="submit" style="align-self:end;">添加</button>
        </form>
      </div>

      <!-- 分类列表 -->
      <div class="card">
        <div class="card-title">已有分类（<?= count($categories) ?> 个）</div>
        <?php if (empty($categories)): ?>
          <p style="color:var(--text-dim);font-size:13px;">暂无分类，请先添加。</p>
        <?php else: ?>
          <table class="table">
            <thead><tr><th>排序</th><th>标题</th><th>Slug</th><th>照片数</th><th style="text-align:right;">操作</th></tr></thead>
            <tbody>
              <?php foreach ($categories as $c):
                $cntStmt = $pdo->prepare('SELECT COUNT(*) FROM photos WHERE category_id = ?');
                $cntStmt->execute([$c['id']]);
                $cnt = $cntStmt->fetchColumn();
              ?>
                <tr>
                  <td style="width:60px;"><?= (int)$c['sort'] ?></td>
                  <td><?= e($c['title']) ?></td>
                  <td class="small"><?= e($c['slug']) ?></td>
                  <td class="small"><?= $cnt ?> 张</td>
                  <td style="text-align:right;">
                    <form method="post" style="display:inline;" onsubmit="return confirm('删除后该分类下的所有照片也会被删除，确定？');">
                      <input type="hidden" name="action" value="delete_category">
                      <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm">删除</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <!-- 添加照片 -->
      <div class="card">
        <div class="card-title">添加照片</div>
        <form method="post" style="display:grid;grid-template-columns:1fr 2fr 2fr 80px;gap:16px;align-end;">
          <input type="hidden" name="action" value="add_photo">
          <div class="form-group" style="margin-bottom:0;">
            <label>所属分类</label>
            <select name="category_id" required>
              <option value="">— 选择分类 —</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= (int)$c['id'] ?>"><?= e($c['title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label>照片标题</label>
            <input name="title" placeholder="天际线" required>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label>图片 URL</label>
            <input name="url" type="url" placeholder="https://..." required>
          </div>
          <button class="btn btn-primary" type="submit" style="align-self:end;">添加</button>
        </form>
      </div>

      <!-- 照片列表 -->
      <div class="card">
        <div class="card-title">照片列表（<?= count($photos) ?> 张）</div>
        <?php if (empty($photos)): ?>
          <p style="color:var(--text-dim);font-size:13px;">暂无照片。</p>
        <?php else: ?>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <label style="font-size:12px;color:var(--text-dim);display:flex;align-items:center;gap:8px;cursor:pointer;">
              <input type="checkbox" id="selectAll">
              <span>全选</span>
            </label>
            <button type="button" class="btn btn-danger btn-sm" onclick="submitBatchDelete()">批量删除</button>
          </div>
          <div class="photo-grid" id="photoGrid">
            <?php foreach ($photos as $p): ?>
              <div class="photo-item selectable" onclick="toggleSelect(this, <?= (int)$p['id'] ?>)" data-id="<?= (int)$p['id'] ?>">
                <img src="<?= e($p['url']) ?>" alt="<?= e($p['title']) ?>" loading="lazy" onerror="this.style.display='none'">
                <div class="photo-overlay">
                  <span class="check-icon">✓</span>
                </div>
                <div style="padding:6px 8px;">
                  <div style="font-size:11px;color:var(--text-soft);letter-spacing:.1em;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($p['title']) ?></div>
                  <div style="font-size:10px;color:var(--text-dim);margin-top:2px;"><?= e($p['cat_title']) ?> · 排序:<?= (int)$p['sort'] ?></div>
                </div>
                <div class="photo-actions">
                  <button type="button" class="btn-edit" onclick="event.stopPropagation();openEditModal(<?= (int)$p['id'] ?>, '<?= e(addslashes($p['title'])) ?>', '<?= e(addslashes($p['url'])) ?>', <?= (int)$p['sort'] ?>, <?= (int)$p['category_id'] ?>)">编辑</button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <form method="post" id="batchDeleteForm" style="display:none;">
            <input type="hidden" name="action" value="batch_delete_photos">
            <input type="hidden" name="photo_ids" id="batchIds">
          </form>
        <?php endif; ?>
      </div>

      <!-- 编辑照片弹窗 -->
      <div id="editModal" class="modal" style="display:none;">
        <div class="modal-content">
          <div class="modal-header">
            <span class="modal-title">编辑照片</span>
            <button type="button" class="modal-close" onclick="closeEditModal()">×</button>
          </div>
          <form method="post" id="editForm">
            <input type="hidden" name="action" value="edit_photo">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-grid">
              <div class="form-group">
                <label>所属分类</label>
                <select name="category_id" id="edit_category_id" required>
                  <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id'] ?>"><?= e($c['title']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label>排序（数字越小越靠前）</label>
                <input name="sort" type="number" id="edit_sort" value="0" placeholder="0">
              </div>
              <div class="form-group form-full">
                <label>照片标题</label>
                <input name="title" id="edit_title" required placeholder="照片标题">
              </div>
              <div class="form-group form-full">
                <label>图片 URL</label>
                <input name="url" type="url" id="edit_url" required placeholder="https://...">
              </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:16px;">
              <button type="submit" class="btn btn-primary">保存修改</button>
              <button type="button" class="btn" onclick="closeEditModal()">取消</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- ══ 留言管理 ══ -->
    <div class="panel <?= $tab === 'messages' ? 'is-active' : '' ?>">
      <div class="panel-title">留言管理</div>
      <div class="panel-sub">Visitor Messages</div>
      <?php if (empty($messages)): ?>
        <div class="card">
          <p style="color:var(--text-dim);font-size:13px;">暂无留言。</p>
        </div>
      <?php else: ?>
        <?php foreach ($messages as $m): ?>
          <div class="msg-item">
            <div class="msg-head">
              <span class="msg-name"><?= e($m['name']) ?></span>
              <span style="display:flex;align-items:center;gap:12px;">
                <span class="msg-date"><?= e($m['created_at']) ?></span>
                <form method="post" style="display:inline;">
                  <input type="hidden" name="action" value="delete_message">
                  <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">删除</button>
                </form>
              </span>
            </div>
            <div class="msg-email"><?= e($m['email']) ?></div>
            <div class="msg-content"><?= e($m['content']) ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- ══ 账号安全 ══ -->
    <div class="panel <?= $tab === 'account' ? 'is-active' : '' ?>">
      <div class="panel-title">账号安全</div>
      <div class="panel-sub">Account & Security</div>
      <div class="card">
        <div class="card-title">修改密码</div>
        <p style="color:var(--text-dim);font-size:13px;margin:0 0 20px;">当前登录账号：<strong style="color:var(--text);"><?= e(current_admin() ?? '') ?></strong></p>
        <form method="post">
          <input type="hidden" name="action" value="change_password">
          <div class="form-grid" style="max-width:480px;">
            <div class="form-group form-full">
              <label>原密码</label>
              <input name="old_password" type="password" placeholder="••••••••" required>
            </div>
            <div class="form-group">
              <label>新密码（至少 6 位）</label>
              <input name="new_password" type="password" placeholder="••••••••" required>
            </div>
            <div class="form-group">
              <label>确认新密码</label>
              <input name="new_password2" type="password" placeholder="••••••••" required>
            </div>
          </div>
          <button class="btn btn-primary" type="submit" style="margin-top:10px;">更新密码</button>
        </form>
      </div>
    </div>

    <!-- ══ 页面文案 ══ -->
    <div class="panel <?= $tab === 'pages' ? 'is-active' : '' ?>">
      <div class="panel-title">页面文案</div>
      <div class="panel-sub">Page Content Settings</div>

      <!-- 首页文案 -->
      <div class="card">
        <div class="card-title">首页 · Hero 区域</div>
        <form method="post">
          <input type="hidden" name="action" value="save_home_text">
          <div class="form-grid">
            <div class="form-group">
              <label>顶部小标题</label>
              <input name="hero_kicker" value="<?= e($settings['hero_kicker'] ?? 'Portfolio · Since 2018') ?>" placeholder="Portfolio · Since 2018">
            </div>
            <div class="form-group">
              <label>主标题</label>
              <input name="hero_title" value="<?= e($settings['hero_title'] ?? '光 影') ?>" placeholder="光 影">
            </div>
            <div class="form-group">
              <label>副标题</label>
              <input name="hero_subtitle" value="<?= e($settings['hero_subtitle'] ?? 'Architect / Photographer') ?>" placeholder="Architect / Photographer">
            </div>
            <div class="form-group form-full">
              <label>背景图片 URL（可选，留空则使用渐变）</label>
              <input name="hero_bg_image" type="url" value="<?= e($settings['hero_bg_image'] ?? '') ?>" placeholder="https://...">
            </div>
          </div>
          <div class="divider" style="margin:24px 0;"></div>
          <div class="card-title" style="margin-bottom:16px;">首页 · 作品集预览区</div>
          <div class="form-grid">
            <div class="form-group">
              <label>区块标题</label>
              <input name="home_section_title" value="<?= e($settings['home_section_title'] ?? '精选作品集') ?>" placeholder="精选作品集">
            </div>
            <div class="form-group form-full">
              <label>区块描述</label>
              <input name="home_section_desc" value="<?= e($settings['home_section_desc'] ?? '') ?>" placeholder="四个栏目，二十余幅影像...">
            </div>
            <div class="form-group">
              <label>按钮文案</label>
              <input name="home_btn_text" value="<?= e($settings['home_btn_text'] ?? '浏览完整作品集') ?>" placeholder="浏览完整作品集">
            </div>
          </div>
          <button class="btn btn-primary" type="submit" style="margin-top:16px;">保存首页文案</button>
        </form>
      </div>

      <!-- 作品集页文案 -->
      <div class="card">
        <div class="card-title">作品集页</div>
        <form method="post">
          <input type="hidden" name="action" value="save_portfolio_text">
          <div class="form-grid">
            <div class="form-group">
              <label>页面标题</label>
              <input name="portfolio_title" value="<?= e($settings['portfolio_title'] ?? '作品集') ?>" placeholder="作品集">
            </div>
            <div class="form-group form-full">
              <label>页面描述</label>
              <input name="portfolio_desc" value="<?= e($settings['portfolio_desc'] ?? '') ?>" placeholder="以不对称的网格节奏...">
            </div>
          </div>
          <button class="btn btn-primary" type="submit" style="margin-top:16px;">保存作品集页文案</button>
        </form>
      </div>

      <!-- 关于我页文案 -->
      <div class="card">
        <div class="card-title">关于我页</div>
        <form method="post">
          <input type="hidden" name="action" value="save_about_text">
          <div class="form-grid">
            <div class="form-group form-full">
              <label>页面标题</label>
              <input name="about_title" value="<?= e($settings['about_title'] ?? '关于我') ?>" placeholder="关于我">
            </div>
            <div class="divider" style="margin:16px 0;"></div>
            <div style="font-size:12px;color:var(--text-dim);letter-spacing:.2em;margin-bottom:12px;">统计数据展示</div>
            <div class="form-group">
              <label>从业年数</label>
              <input name="about_stats_years" value="<?= e($settings['about_stats_years'] ?? '07') ?>" placeholder="07">
            </div>
            <div class="form-group">
              <label>项目数</label>
              <input name="about_stats_projects" value="<?= e($settings['about_stats_projects'] ?? '24') ?>" placeholder="24">
            </div>
            <div class="form-group">
              <label>照片数</label>
              <input name="about_stats_frames" value="<?= e($settings['about_stats_frames'] ?? '∞') ?>" placeholder="∞">
            </div>
            <div class="form-group">
              <label>年数标签</label>
              <input name="about_stats_years_label" value="<?= e($settings['about_stats_years_label'] ?? 'Years of Practice') ?>" placeholder="Years of Practice">
            </div>
            <div class="form-group">
              <label>项目标签</label>
              <input name="about_stats_projects_label" value="<?= e($settings['about_stats_projects_label'] ?? 'Projects') ?>" placeholder="Projects">
            </div>
            <div class="form-group">
              <label>照片标签</label>
              <input name="about_stats_frames_label" value="<?= e($settings['about_stats_frames_label'] ?? 'Frames of Light') ?>" placeholder="Frames of Light">
            </div>
          </div>
          <button class="btn btn-primary" type="submit" style="margin-top:16px;">保存关于我页文案</button>
        </form>
      </div>

      <!-- 联系页文案 -->
      <div class="card">
        <div class="card-title">联系页</div>
        <form method="post">
          <input type="hidden" name="action" value="save_contact_text">
          <div class="form-grid">
            <div class="form-group">
              <label>页面标题</label>
              <input name="contact_title" value="<?= e($settings['contact_title'] ?? '联系方式') ?>" placeholder="联系方式">
            </div>
            <div class="form-group">
              <label>侧栏标题</label>
              <input name="contact_side_title" value="<?= e($settings['contact_side_title'] ?? '其他方式') ?>" placeholder="其他方式">
            </div>
            <div class="form-group form-full">
              <label>页面描述</label>
              <input name="contact_desc" value="<?= e($settings['contact_desc'] ?? '') ?>" placeholder="若您希望合作...">
            </div>
            <div class="form-group">
              <label>姓名字段标签</label>
              <input name="contact_form_name_label" value="<?= e($settings['contact_form_name_label'] ?? '姓名 · Name') ?>" placeholder="姓名 · Name">
            </div>
            <div class="form-group">
              <label>邮箱字段标签</label>
              <input name="contact_form_email_label" value="<?= e($settings['contact_form_email_label'] ?? '邮箱 · Email') ?>" placeholder="邮箱 · Email">
            </div>
            <div class="form-group">
              <label>留言字段标签</label>
              <input name="contact_form_msg_label" value="<?= e($settings['contact_form_msg_label'] ?? '留言 · Message') ?>" placeholder="留言 · Message">
            </div>
            <div class="form-group">
              <label>提交按钮文案</label>
              <input name="contact_form_btn_text" value="<?= e($settings['contact_form_btn_text'] ?? '发送消息') ?>" placeholder="发送消息">
            </div>
          </div>
          <button class="btn btn-primary" type="submit" style="margin-top:16px;">保存联系页文案</button>
        </form>
      </div>

      <!-- 页脚文案 -->
      <div class="card">
        <div class="card-title">页脚</div>
        <form method="post">
          <input type="hidden" name="action" value="save_footer_text">
          <div class="form-grid">
            <div class="form-group form-full">
              <label>页脚描述</label>
              <input name="footer_desc" value="<?= e($settings['footer_desc'] ?? '') ?>" placeholder="建筑师 / 摄影师...">
            </div>
            <div class="form-group form-full">
              <label>版权信息（%Y 会替换为年份）</label>
              <input name="footer_copy" value="<?= e($settings['footer_copy'] ?? '') ?>" placeholder="© %Y 光影 LUMIERE. All rights reserved.">
            </div>
          </div>
          <button class="btn btn-primary" type="submit" style="margin-top:16px;">保存页脚文案</button>
        </form>
      </div>
    </div>

  </main>
</div>

<script>
function openEditModal(id, title, url, sort, categoryId) {
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_title').value = title;
  document.getElementById('edit_url').value = url;
  document.getElementById('edit_sort').value = sort;
  document.getElementById('edit_category_id').value = categoryId;
  document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() {
  document.getElementById('editModal').style.display = 'none';
}
document.getElementById('selectAll').addEventListener('change', function() {
  var items = document.querySelectorAll('.photo-item.selectable');
  items.forEach(function(item) {
    if (this.checked) {
      item.classList.add('selected');
    } else {
      item.classList.remove('selected');
    }
  });
});
function toggleSelect(el, id) {
  el.classList.toggle('selected');
}
function submitBatchDelete() {
  var selected = document.querySelectorAll('.photo-item.selected');
  if (selected.length === 0) {
    alert('请先点击照片选中');
    return false;
  }
  if (!confirm('确定删除选中的 ' + selected.length + ' 张照片？')) return false;
  var ids = [];
  selected.forEach(function(item) {
    ids.push(item.getAttribute('data-id'));
  });
  document.getElementById('batchIds').value = ids.join(',');
  document.getElementById('batchDeleteForm').submit();
}
function openContactModal(id, label, value, url, sort) {
  document.getElementById('contact_id').value = id;
  document.getElementById('contact_label').value = label;
  document.getElementById('contact_value').value = value;
  document.getElementById('contact_url').value = url;
  document.getElementById('contact_sort').value = sort;
  document.getElementById('contactModal').style.display = 'flex';
}
function closeContactModal() {
  document.getElementById('contactModal').style.display = 'none';
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeEditModal();
    closeContactModal();
  }
});
</script>

<style>
.modal {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: color-mix(in srgb, var(--bg-0) 85%, transparent);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal-content {
  background: var(--bg-2);
  border: 1px solid var(--line);
  padding: 24px;
  width: 90%;
  max-width: 500px;
  border-radius: 4px;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.modal-title {
  font-size: 14px;
  letter-spacing: 0.2em;
  color: var(--text);
}
.modal-close {
  background: none;
  border: none;
  color: var(--text-dim);
  font-size: 20px;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}
.modal-close:hover { color: var(--text); }
.photo-item {
  cursor: pointer;
  position: relative;
  transition: all 0.2s ease;
}
.photo-item:hover {
  border-color: color-mix(in srgb, var(--accent) 30%, transparent);
}
.photo-item.selected {
  border-color: var(--accent) !important;
  box-shadow: 0 0 0 2px var(--accent);
}
.photo-item.selected .photo-overlay {
  opacity: 1;
}
.photo-overlay {
  position: absolute;
  top: 8px;
  left: 8px;
  width: 28px;
  height: 28px;
  background: var(--accent);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s ease;
}
.check-icon {
  color: var(--bg-0);
  font-size: 16px;
  font-weight: bold;
}
.photo-actions {
  position: absolute;
  top: 8px;
  right: 8px;
  display: none;
  gap: 4px;
}
.photo-item:hover .photo-actions {
  display: flex;
}
.btn-edit {
  background: color-mix(in srgb, var(--bg-0) 80%, transparent);
  border: none;
  color: var(--text);
  font-size: 11px;
  padding: 6px 12px;
  cursor: pointer;
  border-radius: 3px;
}
.btn-edit:hover {
  background: var(--accent);
  color: var(--bg-0);
}
</style>

</body>
</html>
