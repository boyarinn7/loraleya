(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', function () {
    var items = document.querySelectorAll('.sc-gallery-item');
    if (!items.length) return;

    var overlay = document.createElement('div');
    overlay.className = 'll-lightbox';
    overlay.setAttribute('aria-hidden', 'true');
    overlay.innerHTML =
      '<button class="ll-lightbox__close" aria-label="Закрыть">&times;</button>' +
      '<img class="ll-lightbox__img" alt="">';
    document.body.appendChild(overlay);

    var lbImg = overlay.querySelector('.ll-lightbox__img');
    var closeBtn = overlay.querySelector('.ll-lightbox__close');

    function openLb(src) {
      if (!src) return;
      lbImg.src = src;
      overlay.classList.add('is-open');
      overlay.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }
    function closeLb() {
      overlay.classList.remove('is-open');
      overlay.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      lbImg.src = '';
    }

    items.forEach(function (el) {
      el.addEventListener('click', function () {
        var full = el.getAttribute('data-full');
        if (!full) {
          var bg = getComputedStyle(el).backgroundImage;
          var m = bg && bg.match(/url\(["']?(.*?)["']?\)/);
          full = m ? m[1] : '';
        }
        openLb(full);
      });
    });

    overlay.addEventListener('click', function (e) {
      if (e.target === overlay || e.target === closeBtn) closeLb();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeLb();
    });
  });
})();
