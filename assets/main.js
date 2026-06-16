/* 光影 LUMIERE · 前端脚本 —— 极简交互 */
(function () {
  // 进入可视区时淡入上移
  const els = document.querySelectorAll('.reveal, .section, .photo, .category-card, .project-block');
  els.forEach((el, i) => {
    el.classList.add('reveal');
    el.style.transitionDelay = (i % 10) * 30 + 'ms';
  });

  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add('is-in');
        }
      });
    }, { threshold: 0.12 });
    els.forEach((el) => io.observe(el));
  } else {
    els.forEach((el) => el.classList.add('is-in'));
  }

  // 作品集分类切换（只展示选中的项目块）
  const tabs = document.querySelectorAll('[data-filter]');
  const blocks = document.querySelectorAll('.project-block');
  if (tabs.length && blocks.length) {
    tabs.forEach((btn) => {
      btn.addEventListener('click', () => {
        tabs.forEach((t) => t.classList.remove('is-active'));
        btn.classList.add('is-active');
        const slug = btn.getAttribute('data-filter');
        blocks.forEach((b) => {
          if (slug === 'all' || b.dataset.category === slug) {
            b.style.display = '';
          } else {
            b.style.display = 'none';
          }
        });
      });
    });
  }

  // 图片懒加载的渐进加载（占位 → 真实图）
  document.querySelectorAll('[data-bg]').forEach((node) => {
    const url = node.getAttribute('data-bg');
    const img = new Image();
    img.onload = () => {
      node.style.backgroundImage = 'url("' + url + '")';
      node.classList.add('is-loaded');
    };
    img.src = url;
  });
})();
