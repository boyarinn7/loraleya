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

});
