/**
 * LoraLeya Theme - Main JS
 */

// === Cart counter (localStorage) ===
var CART_KEY = 'loraleya_cart';

function cartRead() {
    try {
        var raw = localStorage.getItem(CART_KEY);
        return raw ? JSON.parse(raw) : {};
    } catch (e) {
        return {};
    }
}

function cartWrite(data) {
    try {
        localStorage.setItem(CART_KEY, JSON.stringify(data));
    } catch (e) {}
}

function cartGet(item) {
    return cartRead()[item] || 0;
}

function cartSet(item, count) {
    var data = cartRead();
    if (count <= 0) {
        delete data[item];
    } else {
        data[item] = count;
    }
    cartWrite(data);
}

// === Cart button state rendering ===
function renderCartBtn(btn) {
    var item = btn.dataset.item;
    if (!item) return;
    var count = cartGet(item);
    var baseClass = btn.classList.contains('btn-set') ? 'btn-set' : 'btn-prod';

    if (count === 0) {
        btn.innerHTML = 'В корзину';
        btn.dataset.state = 'empty';
        btn.classList.remove('qty-ctrl');
        btn.classList.add(baseClass);
    } else {
        btn.innerHTML =
            '<button type="button" class="qty-ctrl__btn" data-act="dec">−</button>' +
            '<span class="qty-ctrl__val">' + count + '</span>' +
            '<button type="button" class="qty-ctrl__btn" data-act="inc">+</button>';
        btn.dataset.state = 'filled';
        btn.classList.remove(baseClass);
        btn.classList.add('qty-ctrl');
    }
}

// === Init all cart buttons ===
function initCartButtons() {
    document.querySelectorAll('.btn-prod, .btn-set').forEach(function(btn) {
        renderCartBtn(btn);
    });

    document.addEventListener('click', function(e) {
        var target = e.target;

        // Click on + or − inside counter
        if (target.matches('.qty-ctrl__btn')) {
            e.preventDefault();
            e.stopPropagation();
            var wrapper = target.closest('[data-item]');
            if (!wrapper) return;
            var item = wrapper.dataset.item;
            var act = target.dataset.act;
            var count = cartGet(item);
            count = act === 'inc' ? count + 1 : Math.max(0, count - 1);
            cartSet(item, count);
            renderCartBtn(wrapper);
            return;
        }

        // Click on "В корзину" button (first add)
        if (target.matches('.btn-prod, .btn-set') && target.dataset.state === 'empty') {
            e.preventDefault();
            var item = target.dataset.item;
            if (!item) return;
            cartSet(item, 1);
            renderCartBtn(target);
        }
    });
}

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

        var sbBtn = document.getElementById('sbBtn');
        if (sbBtn) {
            sbBtn.addEventListener('click', function() {
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
                if (addBtn) {
                    addBtn.dataset.item = btn.dataset.item;
                    renderCartBtn(addBtn);
                }
            });
        });
    });

    // Init cart buttons
    initCartButtons();

});
