</main>
<footer class="site-footer">
  <div class="container footer-inner">
    <div class="foot-col">
      <div class="brand-cn"><?= e(SITE_NAME()) ?></div>
      <p class="foot-desc"><?= e(AUTHOR_ROLE()) ?> —— 在光与影之间，寻迹生活的质地。</p>
    </div>
    <div class="foot-col">
      <div class="foot-title">导航</div>
      <ul>
        <li><a href="<?= site_url('/') ?>">首页</a></li>
        <li><a href="<?= site_url('/portfolio.php') ?>">作品集</a></li>
        <li><a href="<?= site_url('/about.php') ?>">关于我</a></li>
        <li><a href="<?= site_url('/contact.php') ?>">联系方式</a></li>
      </ul>
    </div>
    <div class="foot-col">
      <div class="foot-title">联系</div>
      <ul class="social">
        <?php foreach (get_contacts() as $c): ?>
          <li><?= e($c['label']) ?> · <?= e($c['value']) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="foot-col foot-copy">
      © <?= date('Y') ?> <?= e(SITE_NAME()) ?> LUMIERE. All rights reserved.
    </div>
  </div>
</footer>
<script>
<?= inline_js() ?>

</script>
</body>
</html>
