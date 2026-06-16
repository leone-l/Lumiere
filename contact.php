<?php
require_once __DIR__ . '/config.php';

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($name === '' || $email === '' || $content === '') {
        $flash = ['type' => 'err', 'msg' => '请填写完整的姓名、邮箱与留言。'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $flash = ['type' => 'err', 'msg' => '邮箱格式不正确，请重新填写。'];
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO messages (name, email, content) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $content]);
            $flash = ['type' => 'ok', 'msg' => '留言已收到，我将在近期回复您，感谢您的来信。'];
            $_POST = []; // 清空表单
        } catch (Throwable $e) {
            $flash = ['type' => 'err', 'msg' => '保存失败：' . $e->getMessage()];
        }
    }
    flash_set($flash['msg'], $flash['type']);
    header('Location: ' . site_url('/contact.php'));
    exit;
}

$flash = flash_get();
include __DIR__ . '/partials/header.php';
?>

<section class="section">
  <div class="container">
    <div class="contact">
      <div>
        <h1><?= e(CONTACT_TITLE()) ?></h1>
        <p class="lead"><?= e(CONTACT_DESC()) ?></p>

        <?php if ($flash): ?>
          <div class="flash <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
        <?php endif; ?>

        <form class="form" method="post" action="<?= site_url('/contact.php') ?>" novalidate>
          <div class="field">
            <label for="name"><?= e(CONTACT_FORM_NAME_LABEL()) ?></label>
            <input id="name" name="name" type="text" required value="<?= e($_POST['name'] ?? '') ?>">
          </div>
          <div class="field">
            <label for="email"><?= e(CONTACT_FORM_EMAIL_LABEL()) ?></label>
            <input id="email" name="email" type="email" required value="<?= e($_POST['email'] ?? '') ?>">
          </div>
          <div class="field">
            <label for="content"><?= e(CONTACT_FORM_MSG_LABEL()) ?></label>
            <textarea id="content" name="content" required><?= e($_POST['content'] ?? '') ?></textarea>
          </div>
          <button class="btn" type="submit"><?= e(CONTACT_FORM_BTN_TEXT()) ?></button>
        </form>
      </div>

      <aside class="contact-side">
        <h3><?= e(CONTACT_SIDE_TITLE()) ?></h3>
        <ul class="social-list">
          <?php foreach (get_contacts() as $c): ?>
            <li>
              <span class="label"><?= e($c['label']) ?></span>
              <?php if ($c['url']): ?>
                <a href="<?= e($c['url']) ?>" target="_blank"><?= e($c['value']) ?></a>
              <?php else: ?>
                <span><?= e($c['value']) ?></span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </aside>
    </div>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
