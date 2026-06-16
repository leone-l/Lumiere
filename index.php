<?php
require_once __DIR__ . '/config.php';

$pdo = db();
$categories = $pdo->query('SELECT * FROM categories ORDER BY sort ASC, id ASC')->fetchAll();

include __DIR__ . '/partials/header.php';
?>

<section class="hero" aria-label="首页主视觉">
  <canvas class="hero-canvas" id="heroCanvas"></canvas>
  <div class="hero-inner">
    <div class="kicker"><?= e(HERO_KICKER()) ?></div>
    <h1><?= e(HERO_TITLE()) ?></h1>
    <div class="divider"></div>
    <div class="subtitle"><?= e(HERO_SUBTITLE()) ?></div>
  </div>
  <div class="hero-scroll" aria-hidden="true">Scroll</div>
</section>

<script>
(function() {
  const canvas = document.getElementById('heroCanvas');
  const ctx = canvas.getContext('2d');
  let particles = [];
  let w, h;
  const isDark = () => document.documentElement.getAttribute('data-theme') !== 'light';

  function resize() {
    w = canvas.width = canvas.offsetWidth;
    h = canvas.height = canvas.offsetHeight;
  }

  function createParticle() {
    return {
      x: Math.random() * w,
      y: Math.random() * h,
      vx: (Math.random() - 0.5) * 0.4,
      vy: (Math.random() - 0.5) * 0.4,
      r: Math.random() * 1.5 + 0.5,
      alpha: Math.random() * 0.5 + 0.3
    };
  }

  function init() {
    resize();
    particles = [];
    const count = Math.floor((w * h) / 15000);
    for (let i = 0; i < count; i++) {
      particles.push(createParticle());
    }
  }

  function draw() {
    ctx.clearRect(0, 0, w, h);
    const dark = isDark();
    const pColor = dark ? '255,255,255' : '0,0,0';
    const lColor = dark ? '255,255,255' : '100,100,100';

    // Update and draw particles
    particles.forEach((p, i) => {
      p.x += p.vx;
      p.y += p.vy;

      if (p.x < 0 || p.x > w) p.vx *= -1;
      if (p.y < 0 || p.y > h) p.vy *= -1;

      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(${pColor},${p.alpha})`;
      ctx.fill();

      // Connect nearby particles
      particles.slice(i + 1).forEach(p2 => {
        const dx = p.x - p2.x;
        const dy = p.y - p2.y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < 120) {
          ctx.beginPath();
          ctx.moveTo(p.x, p.y);
          ctx.lineTo(p2.x, p2.y);
          ctx.strokeStyle = `rgba(${lColor},${(1 - dist / 120) * 0.15})`;
          ctx.lineWidth = 0.5;
          ctx.stroke();
        }
      });
    });

    requestAnimationFrame(draw);
  }

  window.addEventListener('resize', init);
  
  // Reinit on theme change
  const observer = new MutationObserver(init);
  observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

  init();
  draw();
})();
</script>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow">Featured Projects</div>
      <h2><?= e(HOME_SECTION_TITLE()) ?></h2>
      <p><?= e(HOME_SECTION_DESC()) ?></p>
    </div>

    <div class="category-list">
      <?php foreach ($categories as $idx => $cat):
        $cover = $pdo->prepare('SELECT url FROM photos WHERE category_id = ? ORDER BY sort ASC, id ASC LIMIT 1');
        $cover->execute([$cat['id']]);
        $row = $cover->fetch();
        $url = $row['url'] ?? '';
      ?>
        <a class="category-card" href="<?= site_url('/portfolio.php') ?>#<?= e($cat['slug']) ?>">
          <div class="img" style="background-image:url('<?= e($url) ?>');"></div>
          <div class="meta">
            <div class="num">0<?= $idx + 1 ?></div>
            <h3><?= e($cat['title']) ?></h3>
            <p><?= e(mb_substr($cat['description'], 0, 36)) ?>…</p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top: 40px;">
  <div class="container" style="text-align:center;">
    <a class="btn" href="<?= site_url('/portfolio.php') ?>"><?= e(HOME_BTN_TEXT()) ?></a>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>