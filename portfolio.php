<?php
require_once __DIR__ . '/config.php';

$pdo = db();
$categories = $pdo->query('SELECT * FROM categories ORDER BY sort ASC, id ASC')->fetchAll();

$spanPatterns = [
    ['span-8', 'span-4', 'span-3', 'span-5', 'span-4', 'span-4'],
    ['span-5', 'span-7', 'span-4', 'span-4', 'span-4', 'span-6', 'span-6'],
    ['span-9', 'span-3', 'span-4', 'span-4', 'span-4', 'span-8', 'span-4'],
    ['span-6', 'span-6', 'span-5', 'span-3', 'span-4', 'span-4', 'span-4'],
];

$photosByCat = [];
foreach ($categories as $c) {
    $stmt = $pdo->prepare('SELECT * FROM photos WHERE category_id = ? ORDER BY sort ASC, id ASC');
    $stmt->execute([$c['id']]);
    $photosByCat[$c['id']] = $stmt->fetchAll();
}

include __DIR__ . '/partials/header.php';
?>

<section class="section" style="padding-bottom: 40px;">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow">Portfolio</div>
      <h2><?= e(PORTFOLIO_TITLE()) ?></h2>
      <p><?= e(PORTFOLIO_DESC()) ?></p>
    </div>

    <div class="portfolio-tabs" role="tablist">
      <button class="is-active" data-filter="all">全部</button>
      <?php foreach ($categories as $c): ?>
        <button data-filter="<?= e($c['slug']) ?>"><?= e($c['title']) ?></button>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section style="padding-bottom: 80px;">
  <div class="container">
    <?php foreach ($categories as $i => $cat):
      $photos = $photosByCat[$cat['id']];
      $pattern = $spanPatterns[$i % count($spanPatterns)];
    ?>
      <article class="project-block" data-category="<?= e($cat['slug']) ?>" id="<?= e($cat['slug']) ?>">
        <div class="project-meta">
          <div class="left">
            <div class="num">0<?= $i + 1 ?> / Series</div>
            <h3><?= e($cat['title']) ?></h3>
          </div>
          <div class="right">
            <p><?= e($cat['description']) ?></p>
          </div>
        </div>

        <div class="grid">
          <?php foreach ($photos as $k => $p):
            $span = $pattern[$k % count($pattern)];
          ?>
            <div class="photo <?= $span ?>">
              <div class="img" data-bg="<?= e($p['url']) ?>"></div>
              <div class="caption"><?= e($p['title']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
