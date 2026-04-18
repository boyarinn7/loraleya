/**
 * LoraLeya Theme - Main JS
 */

document.addEventListener('DOMContentLoaded', function() {

    // ===== HEADER SCROLL EFFECT =====
    const header = document.getElementById('siteHeader');
    if (header) {
        let lastScroll = 0;
        window.addEventListener('scroll', function() {
            const scroll = window.pageYOffset;
            if (scroll > 100) {
                header.style.padding = '0.6rem 3rem';
            } else {
                header.style.padding = '1rem 3rem';
            }
            lastScroll = scroll;
        });
    }

    // ===== AJAX ADD TO CART =====
    document.querySelectorAll('[data-add-to-cart]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.addToCart;
            const qty = this.dataset.qty || 1;

            btn.textContent = '...';
            btn.disabled = true;

            fetch(loraleya.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'loraleya_add_to_cart',
                    nonce: loraleya.nonce,
                    product_id: productId,
                    quantity: qty,
                }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    document.querySelectorAll('.cart-count').forEach(function(el) {
                        el.textContent = data.data.cart_count;
                    });
                    btn.textContent = '✓';
                    setTimeout(() => {
                        btn.textContent = '+';
                        btn.disabled = false;
                    }, 1500);
                } else {
                    btn.textContent = '✕';
                    setTimeout(() => {
                        btn.textContent = '+';
                        btn.disabled = false;
                    }, 1500);
                }
            })
            .catch(() => {
                btn.textContent = '+';
                btn.disabled = false;
            });
        });
    });

    // ===== COLOR SWATCH SELECTION =====
    document.querySelectorAll('.color-swatch[data-selectable]').forEach(function(sw) {
        sw.addEventListener('click', function() {
            this.closest('.swatches-group').querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
            this.classList.add('active');
            const colorName = this.title;
            const label = this.closest('.swatches-group').querySelector('.color-label');
            if (label) label.textContent = colorName;
        });
    });

    // ===== QUANTITY BUTTONS =====
    document.querySelectorAll('.qty-minus, .qty-plus').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const wrap = this.closest('.qty-wrap');
            const input = wrap.querySelector('.qty-value');
            let val = parseInt(input.textContent || input.value);
            if (this.classList.contains('qty-minus')) val--;
            else val++;
            if (val < 0) val = 0;
            if (val > 99) val = 99;
            if (input.tagName === 'INPUT') input.value = val;
            else input.textContent = val;
            wrap.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });

    // ===== SMOOTH SCROLL FOR ANCHOR LINKS =====
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ===== FADE IN ON SCROLL =====
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.section, .scenario-card, .prod, .set').forEach(function(el) {
        observer.observe(el);
    });

    // ===== COLOR PAGE =====
    if (document.querySelector('.color-hero')) {

        var colorCart = [];
        var colorTotal = 0;

        var colorPrices = {
            'Дорожка 140': 890, 'Дорожка 175': 990,
            'Дорожка 240': 1290, 'Дорожка 300': 1590,
            'Скатерть 175': 2490, 'Скатерть 220': 2990, 'Скатерть 240': 3490,
            'Салфетка': 350, 'Куверт': 250,
            'Набор 4п/140': 2790, 'Набор 4п/175': 2970,
            'Набор 6п/140': 3820, 'Набор 6п/175': 3990
        };

        function colorAddItem(name) {
            colorCart.push(name);
            colorTotal += colorPrices[name] || 0;

            var cc = document.querySelector('.cart-count');
            if (cc) {
                cc.textContent = colorCart.length;
                cc.style.animation = 'none';
                cc.offsetHeight;
                cc.style.animation = 'cartPop .3s';
            }

            var bar = document.getElementById('stickyBar');
            if (bar) bar.classList.add('show');

            var sbTotal = document.getElementById('sbTotal');
            if (sbTotal) sbTotal.textContent = colorTotal.toLocaleString('ru-RU') + ' ₽';
        }

        document.querySelectorAll('.prod-add').forEach(function(btn) {
            btn.addEventListener('click', function() {
                colorAddItem(btn.dataset.item);
                btn.textContent = '✓';
                btn.style.background = 'rgba(74,122,74,.5)';
                btn.style.borderColor = 'rgba(74,122,74,.5)';
                setTimeout(function() {
                    btn.textContent = '+';
                    btn.style.background = '';
                    btn.style.borderColor = '';
                }, 1000);
            });
        });

        document.querySelectorAll('.btn-set').forEach(function(btn) {
            btn.addEventListener('click', function() {
                colorAddItem(btn.dataset.item);
                var orig = btn.textContent;
                btn.textContent = 'Добавлено ✓';
                btn.style.background = 'rgba(74,122,74,.5)';
                btn.style.borderColor = 'rgba(74,122,74,.5)';
                btn.style.color = '#fff';
                setTimeout(function() {
                    btn.textContent = orig;
                    btn.style.background = '';
                    btn.style.borderColor = '';
                    btn.style.color = '';
                }, 1500);
            });
        });

        var sbBtn = document.getElementById('sbBtn');
        if (sbBtn) {
            sbBtn.addEventListener('click', function() {
                if (colorCart.length === 0) return;
                sbBtn.textContent = 'Переход в корзину...';
            });
        }
    }

    // Product size tabs
    document.querySelectorAll('.prod--variants').forEach(function(card) {
        var sizeBtns  = card.querySelectorAll('.prod-size-btn');
        var sizeLabel = card.querySelector('.prod-size');
        var priceOld  = card.querySelector('.prod-price-old');
        var priceNow  = card.querySelector('.prod-price-now');
        var addBtn    = card.querySelector('.btn-prod');

        sizeBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                sizeBtns.forEach(function(b) { b.classList.remove('is-active'); });
                btn.classList.add('is-active');

                if (sizeLabel) sizeLabel.textContent = btn.dataset.size;
                if (priceNow)  priceNow.innerHTML    = btn.dataset.price;
                if (priceOld)  priceOld.innerHTML    = btn.dataset.priceOld;
                if (addBtn)    addBtn.dataset.item    = btn.dataset.item;
            });
        });
    });

});
