/**
 * LoraLeya Theme - Main JS
 */

// === WC Cart cache (replaces localStorage) ===
// Хранит соответствие data-item → { count, cart_key } в JS-памяти.
// При загрузке страницы синхронизируется с WC через loraleya_get_cart.
var CART_CACHE = {};

function cartGet(item) {
    return (CART_CACHE[item] && CART_CACHE[item].count) || 0;
}

function cartSet(item, count, onDone) {
    if (typeof LORALEYA_ITEM_MAP === 'undefined' || !LORALEYA_ITEM_MAP[item]) {
        console.warn('LORALEYA_ITEM_MAP missing for item:', item);
        if (onDone) onDone();
        return;
    }

    var mapped = LORALEYA_ITEM_MAP[item];
    var cached = CART_CACHE[item];

    if (count <= 0 && cached && cached.cart_key) {
        wcUpdateCartItem(cached.cart_key, 0, function(success) {
            if (success) { delete CART_CACHE[item]; }
            if (onDone) onDone();
        });
    } else if (cached && cached.cart_key) {
        wcUpdateCartItem(cached.cart_key, count, function(success) {
            if (success) { CART_CACHE[item].count = count; }
            if (onDone) onDone();
        });
    } else if (count > 0) {
        wcAddToCart(mapped, count, function(cart_key) {
            if (cart_key) { CART_CACHE[item] = { count: count, cart_key: cart_key }; }
            if (onDone) onDone();
        });
    } else {
        if (onDone) onDone();
    }
}

function wcAddToCart(mapped, quantity, callback) {
    var body = new URLSearchParams();
    body.append('action', 'loraleya_add_to_cart');
    body.append('nonce', loraleya.nonce);
    body.append('product_id', mapped.product_id);
    body.append('variation_id', mapped.variation_id || 0);
    body.append('quantity', quantity);

    if (mapped.attrs && typeof mapped.attrs === 'object') {
        Object.keys(mapped.attrs).forEach(function(k) {
            body.append('variation[' + k + ']', mapped.attrs[k]);
        });
    }

    fetch(loraleya.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            document.dispatchEvent(new CustomEvent('loraleya:cart-updated', { detail: res.data }));
            callback(res.data.cart_key);
        } else {
            console.error('add_to_cart failed:', res.data);
            callback(null);
        }
    })
    .catch(function(err) {
        console.error('add_to_cart error:', err);
        callback(null);
    });
}

function wcUpdateCartItem(cart_key, quantity, callback) {
    var body = new URLSearchParams();
    body.append('action', 'loraleya_update_cart_item');
    body.append('nonce', loraleya.nonce);
    body.append('cart_key', cart_key);
    body.append('quantity', quantity);

    fetch(loraleya.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            document.dispatchEvent(new CustomEvent('loraleya:cart-updated', { detail: res.data }));
            callback(true);
        } else {
            console.error('update_cart_item failed:', res.data);
            callback(false);
        }
    })
    .catch(function(err) {
        console.error('update_cart_item error:', err);
        callback(false);
    });
}

function cartSyncFromServer(onDone) {
    if (typeof LORALEYA_ITEM_MAP === 'undefined') {
        if (onDone) onDone();
        return;
    }

    var body = new URLSearchParams();
    body.append('action', 'loraleya_get_cart');
    body.append('nonce', loraleya.nonce);

    fetch(loraleya.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (!res.success) { if (onDone) onDone(); return; }
        var items = res.data.items || [];

        Object.keys(LORALEYA_ITEM_MAP).forEach(function(dataItem) {
            var mapped = LORALEYA_ITEM_MAP[dataItem];
            var found = items.find(function(it) {
                if (mapped.variation_id > 0) {
                    return parseInt(it.variation_id, 10) === mapped.variation_id;
                } else {
                    return parseInt(it.product_id, 10) === mapped.product_id && parseInt(it.variation_id, 10) === 0;
                }
            });
            if (found) {
                CART_CACHE[dataItem] = {
                    count: parseInt(found.quantity, 10),
                    cart_key: found.cart_key
                };
            }
        });

        if (onDone) onDone();
    })
    .catch(function(err) {
        console.error('get_cart error:', err);
        if (onDone) onDone();
    });
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
            cartSet(item, count, function() { renderCartBtn(wrapper); });
            return;
        }

        // Click on "В корзину" button (first add)
        if (target.matches('.btn-prod, .btn-set') && target.dataset.state === 'empty') {
            e.preventDefault();
            var item = target.dataset.item;
            if (!item) return;
            cartSet(item, 1, function() { renderCartBtn(target); });
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
                    addBtn.dataset.productId = btn.dataset.productId;
                    addBtn.dataset.variationId = btn.dataset.variationId;
                    renderCartBtn(addBtn);
                }
            });
        });
    });

    // Sync cart state from WC, then init buttons
    cartSyncFromServer(function() {
        initCartButtons();
    });

});

// === CART WIDGET (icon + modal) ===
(function() {
    var fab, fabBadge, modal, modalBody, modalFooter, modalTotal;

    function $(sel) { return document.querySelector(sel); }

    function init() {
        fab         = $('#llCartFab');
        fabBadge    = $('#llCartFabBadge');
        modal       = $('#llCartModal');
        modalBody   = $('#llCartModalBody');
        modalFooter = $('#llCartModalFooter');
        modalTotal  = $('#llCartModalTotal');

        if (!fab || !modal) return;

        fab.addEventListener('click', openModal);

        modal.addEventListener('click', function(e) {
            if (e.target.matches('[data-close]')) closeModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
        });

        document.addEventListener('loraleya:cart-updated', function(e) {
            updateBadge(e.detail && e.detail.cart_count);
        });

        fetchCount();
    }

    function fetchCount() {
        var body = new URLSearchParams();
        body.append('action', 'loraleya_get_cart');
        body.append('nonce', loraleya.nonce);

        fetch(loraleya.ajax_url, { method: 'POST', body: body })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) updateBadge(res.data.cart_count);
            });
    }

    function updateBadge(count) {
        count = parseInt(count, 10) || 0;
        fab.dataset.count = count;
        if (count > 0) {
            fabBadge.textContent = count;
            fabBadge.removeAttribute('hidden');
        } else {
            fabBadge.setAttribute('hidden', '');
        }
    }

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        loadCart();
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function loadCart() {
        modalBody.innerHTML = '<div class="ll-cart-modal__empty">Загрузка…</div>';
        modalFooter.setAttribute('hidden', '');

        var body = new URLSearchParams();
        body.append('action', 'loraleya_get_cart');
        body.append('nonce', loraleya.nonce);

        fetch(loraleya.ajax_url, { method: 'POST', body: body })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) renderCart(res.data);
            })
            .catch(function() {
                modalBody.innerHTML = '<div class="ll-cart-modal__empty">Ошибка загрузки</div>';
            });
    }

    function renderCart(data) {
        var items = (data && data.items) || [];

        if (items.length === 0) {
            modalBody.innerHTML = '<div class="ll-cart-modal__empty">Корзина пуста</div>';
            modalFooter.setAttribute('hidden', '');
            updateBadge(0);
            return;
        }

        var html = '';
        items.forEach(function(item) {
            var imgSrc = item.image || '';
            var variationText = '';
            if (item.variation && Object.keys(item.variation).length > 0) {
                variationText = Object.values(item.variation).filter(Boolean).join(' · ');
            }

            html += '<div class="ll-cart-item" data-cart-key="' + escapeHtml(item.cart_key) + '">';
            html += '  <img class="ll-cart-item__img" src="' + escapeHtml(imgSrc) + '" alt="">';
            html += '  <div class="ll-cart-item__info">';
            html += '    <div class="ll-cart-item__name">' + escapeHtml(item.name) + '</div>';
            if (variationText) {
                html += '    <div class="ll-cart-item__variation">' + escapeHtml(variationText) + '</div>';
            }
            html += '  </div>';
            html += '  <div class="ll-cart-item__controls">';
            html += '    <div class="ll-cart-item__price">' + item.subtotal + '</div>';
            html += '    <div class="ll-cart-item__qty">';
            html += '      <button type="button" data-act="dec" aria-label="Минус">−</button>';
            html += '      <span>' + item.quantity + '</span>';
            html += '      <button type="button" data-act="inc" aria-label="Плюс">+</button>';
            html += '    </div>';
            html += '  </div>';
            html += '</div>';
        });

        modalBody.innerHTML = html;
        modalTotal.innerHTML = data.cart_total;
        modalFooter.removeAttribute('hidden');
        updateBadge(data.cart_count);

        modalBody.querySelectorAll('.ll-cart-item__qty button').forEach(function(btn) {
            btn.addEventListener('click', handleQtyClick);
        });
    }

    function handleQtyClick(e) {
        var btn = e.currentTarget;
        var item = btn.closest('.ll-cart-item');
        var cartKey = item.dataset.cartKey;
        var qtySpan = item.querySelector('.ll-cart-item__qty span');
        var current = parseInt(qtySpan.textContent, 10);
        var newQty = btn.dataset.act === 'inc' ? current + 1 : Math.max(0, current - 1);

        item.querySelectorAll('button').forEach(function(b) { b.disabled = true; });

        var body = new URLSearchParams();
        body.append('action', 'loraleya_update_cart_item');
        body.append('nonce', loraleya.nonce);
        body.append('cart_key', cartKey);
        body.append('quantity', newQty);

        fetch(loraleya.ajax_url, { method: 'POST', body: body })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    loadCart();
                    document.dispatchEvent(new CustomEvent('loraleya:cart-updated', { detail: res.data }));
                } else {
                    item.querySelectorAll('button').forEach(function(b) { b.disabled = false; });
                }
            })
            .catch(function() {
                item.querySelectorAll('button').forEach(function(b) { b.disabled = false; });
            });
    }

    function escapeHtml(str) {
        if (str == null) return '';
        return String(str).replace(/[&<>"']/g, function(c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
