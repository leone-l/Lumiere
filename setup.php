<?php
/**
 * 数据库初始化与占位数据
 * 访问 setup.php 自动建表并写入示例作品。
 */

require_once __DIR__ . '/config.php';

$pdo = db();

// ── 建表 ──
$tables = <<<'SQL'
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT UNIQUE NOT NULL,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    cover TEXT,
    sort INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS photos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    url TEXT NOT NULL,
    description TEXT,
    sort INTEGER DEFAULT 0,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    content TEXT NOT NULL,
    created_at TEXT DEFAULT (datetime('now','localtime'))
);

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    created_at TEXT DEFAULT (datetime('now','localtime'))
);

CREATE TABLE IF NOT EXISTS settings (
    key TEXT PRIMARY KEY,
    value TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS contacts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    label TEXT NOT NULL,
    value TEXT NOT NULL,
    icon TEXT,
    url TEXT,
    sort INTEGER DEFAULT 0
);
SQL;

foreach (array_filter(array_map('trim', explode(';', $tables))) as $stmt) {
    if ($stmt) $pdo->exec($stmt);
}

// ── 默认管理员账号 ──
$stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
$stmt->execute(['admin']);
if ((int)$stmt->fetchColumn() === 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)')->execute(['admin', $hash]);
    echo "✓ 默认管理员账号已创建<br>";
} else {
    echo "✓ 管理员账号已存在<br>";
}

// ── 默认站点设置 ──
$defaults = [
    // 基本信息
    'site_name' => '光影',
    'site_title' => '光影 · Photographer',
    'site_desc' => '极简主义黑白摄影作品集——城市建筑、旷野遐想、建筑光影、生活碎片。',
    // 作者信息
    'author_name' => '光影',
    'author_role' => 'Photographer',
    'author_bio' => '我是一名摄影师。在这个图像泛滥的时代，我选择用黑白来简化世界的复杂性，让光线、结构与瞬间自己说话。

城市建筑系列关注几何与尺度，旷野系列记录地平线与孤独的形体，建筑光影专注于「时间在墙面上的表演」，而生活碎片则是我的日常素描。

如果你也喜欢这种安静的影像，欢迎通过下方联系我——我们可以一起谈论光、谈论空间中那些不经意却值得被定格的瞬间。',
    'author_avatar' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=portrait%20of%20a%20photographer%2C%20elegant%2C%20moody%2C%20cinematic%20lighting%2C%20black%20and%20white%2C%20minimalist%2C%20studio%20portrait%2C%20artistic&image_size=portrait_4_3',
    // 社交联系方式
    'social_qq' => '123 456 789',
    'social_wechat' => 'xxx',
    'social_behance' => 'behance.net/lumiere',
    'social_email' => 'hello@lumiere.studio',
    'social_studio' => '上海 · 徐汇',
    // 首页文案
    'hero_kicker' => 'Portfolio · Since 2018',
    'hero_title' => '光 影',
    'hero_subtitle' => 'Photographer',
    'home_section_title' => '精选作品集',
    'home_section_desc' => '四个栏目，二十余幅影像——记录光影的流转，与时间留下的微弱痕迹。',
    'home_btn_text' => '浏览完整作品集',
    'hero_bg_image' => '',
    // 作品集页文案
    'portfolio_title' => '作品集',
    'portfolio_desc' => '以不对称的网格节奏，呈现每一个系列的视觉语言。',
    // 关于我页文案
    'about_title' => '关于我',
    'about_stats_years' => '07',
    'about_stats_projects' => '24',
    'about_stats_frames' => '∞',
    'about_stats_years_label' => 'Years of Practice',
    'about_stats_projects_label' => 'Projects',
    'about_stats_frames_label' => 'Frames of Light',
    // 联系页文案
    'contact_title' => '联系方式',
    'contact_desc' => '若您希望合作、委托拍摄，或仅仅想与我聊聊光影，请留下信息——或通过右侧任一社交方式与我联系。',
    'contact_form_name_label' => '姓名 · Name',
    'contact_form_email_label' => '邮箱 · Email',
    'contact_form_msg_label' => '留言 · Message',
    'contact_form_btn_text' => '发送消息',
    'contact_side_title' => '其他方式',
    // 页脚文案
    'footer_desc' => '摄影师 —— 在光与影之间，寻迹生活的质地。',
    'footer_copy' => '© %Y 光影 LUMIERE. All rights reserved.',
];

$insertSet = $pdo->prepare('INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)');
foreach ($defaults as $k => $v) {
    $insertSet->execute([$k, $v]);
}
echo "✓ 站点默认配置已写入<br>";

// ── 示例分类 ──
$categories = [
    ['city', '城市建筑', '混凝土与玻璃编织的现代之城。线条、结构、尺度、秩序——在这些几何语言中，我寻找人与空间对话的瞬间。', 1],
    ['wild', '旷野遐想', '远离城市的旷野，是我安放思绪的地方。地平线、雾气、孤独的树——一切都在缓慢地呼吸。', 2],
    ['light', '建筑光影', '光是建筑的第四维度。清晨与黄昏的斜射光，让墙面的纹理苏醒，让空间的层次浮现。', 3],
    ['life', '生活碎片', '街角、咖啡杯、窗台的剪影——日常的褶皱里藏着最真实的温度。', 4],
];

$photoSets = [
    'city' => [['天际线', 'skylines'], ['几何立面', 'geometric facade'], ['玻璃反射', 'glass reflection'], ['街角高楼', 'corner tower'], ['混凝土结构', 'concrete structure'], ['楼梯光影', 'staircase light']],
    'light' => [['清晨的光', 'morning light'], ['黄昏斜射', 'golden hour'], ['墙面纹理', 'wall texture'], ['柱廊阴影', 'column shadows'], ['窗口光线', 'window light'], ['屋顶天光', 'roof skylight']],
    'wild' => [['旷野地平线', 'wilderness horizon'], ['孤独的树', 'lonely tree'], ['远山薄雾', 'distant mountains fog'], ['黄昏草原', 'dusk grassland'], ['公路延伸', 'endless road'], ['湖畔倒影', 'lake reflection']],
    'life' => [['街角咖啡', 'street coffee'], ['窗台植物', 'windowsill plants'], ['旧书与光', 'old book light'], ['自行车', 'bicycle'], ['雨后街道', 'after rain street'], ['日常剪影', 'daily silhouette']],
];

foreach ($categories as $c) {
    $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ?');
    $stmt->execute([$c[0]]);
    $row = $stmt->fetch();
    if ($row) {
        $cid = $row['id'];
    } else {
        $pdo->prepare('INSERT INTO categories (slug, title, description, sort) VALUES (?, ?, ?, ?)')->execute([$c[0], $c[1], $c[2], $c[3]]);
        $cid = $pdo->lastInsertId();
    }

    $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM photos WHERE category_id = ? AND title = ?');
    $insStmt = $pdo->prepare('INSERT INTO photos (category_id, title, url, description, sort) VALUES (?, ?, ?, ?, ?)');

    foreach ($photoSets[$c[0]] as $idx => $p) {
        $checkStmt->execute([$cid, $p[0]]);
        if ((int)$checkStmt->fetchColumn() > 0) continue;
        $prompt = urlencode($p[1] . ', cinematic, moody dark, minimalist photography, high quality, professional, black and white, high contrast');
        $url = 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=' . $prompt . '&image_size=landscape_16_9';
        $insStmt->execute([$cid, $p[0], $url, $p[1], $idx]);
    }
}

echo "✓ 数据库初始化完成<br>";

// ── 默认联系方式 ──
$defaultContacts = [
    ['QQ', '123 456 789', 'qq', '', 1],
    ['微信', 'xxx', 'wechat', '', 2],
    ['Behance', 'behance.net/lumiere', 'behance', 'https://behance.net/lumiere', 3],
    ['Email', 'hello@lumiere.studio', 'mail', 'mailto:hello@lumiere.studio', 4],
    ['所在地', '上海 · 徐汇', 'location', '', 5],
];

$contactStmt = $pdo->prepare('INSERT OR IGNORE INTO contacts (label, value, icon, url, sort) VALUES (?, ?, ?, ?, ?)');
foreach ($defaultContacts as $c) {
    $contactStmt->execute($c);
}
echo "✓ 默认联系方式已写入<br>";

echo "<br>";
echo "【管理员登录信息】<br>";
echo "用户名：<strong>admin</strong><br>";
echo "密码：<strong>admin123</strong><br>";
echo "<br>";
echo "<a href='login.php'>前往登录</a> | <a href='admin.php'>管理后台</a> | <a href='index.php'>网站首页</a>";
