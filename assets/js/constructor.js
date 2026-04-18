document.addEventListener('DOMContentLoaded', function() {
    const constructor = document.getElementById('constructor');
    if (!constructor) return;

    // --- SET DATA ---
    const setData = {
        4: { title: 'Готовый набор на 4 персоны — выгоднее на 15%', desc: 'Дорожка 40×140 + 4 салфетки + 4 куверта', oldPrice: 3290, newPrice: 2800 },
        6: { title: 'Готовый набор на 6 персон — выгоднее на 15%', desc: 'Дорожка 40×140 + 6 салфеток + 6 кувертов', oldPrice: 4490, newPrice: 3820 }
    };

    // --- SWATCH SELECTION ---
    constructor.querySelectorAll('.sw').forEach(function(sw) {
        sw.addEventListener('click', function() {
            constructor.querySelectorAll('.sw').forEach(function(s) { s.classList.remove('on'); });
            sw.classList.add('on');
        });
    });

    // --- PERSONS SELECTION ---
    constructor.querySelectorAll('.pbtn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var n = parseInt(btn.dataset.persons);
            constructor.querySelectorAll('.pbtn').forEach(function(b) { b.classList.remove('on'); });
            btn.classList.add('on');

            // Update napkin and couverte qty
            var napkin = constructor.querySelector('[data-role="napkin"] .qv');
            var couverte = constructor.querySelector('[data-role="couverte"] .qv');
            if (napkin) { napkin.textContent = n; updateRowSub(napkin.closest('.ir')); }
            if (couverte) { couverte.textContent = n; updateRowSub(couverte.closest('.ir')); }

            // Show/hide set box
            var setBox = document.getElementById('setBox');
            if (n === 2) {
                setBox.style.display = 'none';
            } else {
                setBox.style.display = 'flex';
                var d = setData[n];
                document.getElementById('setTitle').textContent = d.title;
                document.getElementById('setDesc').textContent = d.desc;
                document.getElementById('setOld').textContent = formatPrice(d.oldPrice);
                document.getElementById('setNew').textContent = formatPrice(d.newPrice);
            }

            recalcTotal();
        });
    });

    // --- QTY BUTTONS ---
    constructor.querySelectorAll('.qb-plus').forEach(function(btn) {
        btn.addEventListener('click', function() { changeQty(btn, 1); });
    });
    constructor.querySelectorAll('.qb-minus').forEach(function(btn) {
        btn.addEventListener('click', function() { changeQty(btn, -1); });
    });

    function changeQty(btn, delta) {
        var row = btn.closest('.ir');
        var qv = row.querySelector('.qv');
        var n = parseInt(qv.textContent) + delta;
        if (n < 0) n = 0;
        if (n > 12) n = 12;
        qv.textContent = n;
        updateRowSub(row);
        recalcTotal();
    }

    function updateRowSub(row) {
        var price = parseInt(row.dataset.price);
        var qty = parseInt(row.querySelector('.qv').textContent);
        row.querySelector('.ir-sub').textContent = qty > 0 ? formatPrice(price * qty) : '0 ₽';
    }

    // --- TOTAL ---
    function recalcTotal() {
        var total = 0, items = 0;
        constructor.querySelectorAll('.ir').forEach(function(row) {
            var q = parseInt(row.querySelector('.qv').textContent);
            if (q > 0) {
                var p = parseInt(row.dataset.price);
                total += p * q;
                items += q;
            }
        });
        document.getElementById('totalSum').textContent = total > 0 ? formatPrice(total) : '0 ₽';
        document.getElementById('totalItems').textContent = items > 0 ? items + ' поз.' : '—';
    }

    // --- FORMAT PRICE ---
    function formatPrice(n) {
        return n.toLocaleString('ru-RU') + ' ₽';
    }

    // --- ADD TO CART (visual only) ---
    var cartBtn = document.getElementById('addCartBtn');
    if (cartBtn) {
        cartBtn.addEventListener('click', function() {
            var total = 0;
            constructor.querySelectorAll('.ir').forEach(function(row) {
                total += parseInt(row.querySelector('.qv').textContent);
            });
            if (total === 0) {
                cartBtn.style.background = '#7a4a4a';
                setTimeout(function() { cartBtn.style.background = ''; }, 500);
                return;
            }
            cartBtn.textContent = 'Добавлено ✓';
            cartBtn.style.background = '#4a7a4a';
            animateCartCount(total);
            setTimeout(function() {
                cartBtn.textContent = 'В корзину →';
                cartBtn.style.background = '';
            }, 2000);
        });
    }

    // --- ADD SET (visual only) ---
    var setBtn = document.getElementById('addSetBtn');
    if (setBtn) {
        setBtn.addEventListener('click', function() {
            setBtn.textContent = 'Добавлено ✓';
            setBtn.style.background = 'var(--gold)';
            setBtn.style.color = 'var(--bg)';
            animateCartCount(1);
            setTimeout(function() {
                setBtn.textContent = 'Добавить набор';
                setBtn.style.background = '';
                setBtn.style.color = '';
            }, 2000);
        });
    }

    // --- CART COUNT ANIMATION ---
    function animateCartCount(addItems) {
        var cc = document.querySelector('.cart-count');
        if (cc) {
            cc.textContent = parseInt(cc.textContent) + addItems;
            cc.style.animation = 'none';
            cc.offsetHeight; // reflow
            cc.style.animation = 'cartPop 0.3s';
        }
    }

    // --- RECOMMENDED COLOR PILLS ---
    document.querySelectorAll('.sc-cr').forEach(function(pill) {
        pill.addEventListener('click', function(e) {
            e.preventDefault();
            var colorName = pill.dataset.colorName;
            if (!colorName) return;

            var targetSwatch = constructor.querySelector('.sw[data-name="' + colorName + '"]');
            if (targetSwatch) {
                targetSwatch.click();
            }

            var gallery = document.getElementById('gallery');
            if (gallery) {
                gallery.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // --- INIT ---
    var defaultPersons = parseInt(constructor.dataset.defaultPersons) || 2;
    var defaultBtn = constructor.querySelector('.pbtn[data-persons="' + defaultPersons + '"]');
    if (defaultBtn) defaultBtn.click();
});
