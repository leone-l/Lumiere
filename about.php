<?php require_once __DIR__ . '/config.php';
$avatar = AUTHOR_AVATAR();
if (!$avatar) {
    $avatar = 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=' . urlencode('portrait of an architect photographer, elegant, moody, cinematic lighting, black and white, minimalist, studio portrait, artistic') . '&image_size=portrait_4_3';
}
include __DIR__ . '/partials/header.php';
?>

<section class="section">
  <div class="container">
    <div class="about">
      <div class="avatar">
        <div class="img" data-bg="<?= e($avatar) ?>"></div>
      </div>
      <div class="about-bio">
        <h1><?= e(AUTHOR_NAME()) ?></h1>
        <div class="role"><?= e(AUTHOR_ROLE()) ?></div>

        <?php
        $bio = AUTHOR_BIO();
        if ($bio):
            foreach (explode("\n", trim($bio)) as $para):
                if (trim($para)): ?>
        <p><?= e(trim($para)) ?></p>
        <?php endif; endforeach;
        else: ?>
        <p>暂无个人简介。</p>
        <?php endif; ?>

        <div class="signature">— <?= e(AUTHOR_NAME()) ?></div>

        <div class="about-meta">
          <div class="cell">
            <div class="num"><?= e(ABOUT_STATS_YEARS()) ?></div>
            <div class="label"><?= e(ABOUT_STATS_YEARS_LABEL()) ?></div>
          </div>
          <div class="cell">
            <div class="num"><?= e(ABOUT_STATS_PROJECTS()) ?></div>
            <div class="label"><?= e(ABOUT_STATS_PROJECTS_LABEL()) ?></div>
          </div>
          <div class="cell">
            <div class="num"><?= e(ABOUT_STATS_FRAMES()) ?></div>
            <div class="label"><?= e(ABOUT_STATS_FRAMES_LABEL()) ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
