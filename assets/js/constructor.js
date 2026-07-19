document.addEventListener('DOMContentLoaded', function() {
    const constructor = document.getElementById('constructor');
    if (!constructor) return;

    // --- SET DATA (динамические цены из WC через LORALEYA_ITEM_PRICES) ---
    function buildSetData() {
        var ip = window.LORALEYA_ITEM_PRICES || {};
        function p(key)   { return parseFloat((ip[key] || {}).price)     || 0; }
        function op(key)  { return parseFloat((ip[key] || {}).old_price) || 0; }
        return {
            2: { title: 'Готовый набор на 2 персоны — выгоднее на 15%', desc: 'Дорожка 40×140 + 2 салфетки + 2 куверта', oldPrice: op('Набор 2п/140'), newPrice: p('Набор 2п/140') },
            4: { title: 'Готовый набор на 4 персоны — выгоднее на 15%', desc: 'Дорожка 40×140 + 4 салфетки + 4 куверта', oldPrice: op('Набор 4п/140'), newPrice: p('Набор 4п/140') },
            6: { title: 'Готовый набор на 6 персон — выгоднее на 15%', desc: 'Дорожка 40×240 + 6 салфеток + 6 кувертов', oldPrice: op('Набор 6п/240'), newPrice: p('Набор 6п/240') },
        };
    }
    const setData = buildSetData();

    // --- SWATCH SELECTION ---
    constructor.querySelectorAll('.sw').forEach(function(sw) {
        sw.addEventListener('click', function() {
            constructor.querySelectorAll('.sw').forEach(function(s) { s.classList.remove('on'); });
            sw.classList.add('on');
            var colorSlug = sw.dataset.color;
            updateGallery();
            updateItemPricesForColor(colorSlug);
        });
    });

    // --- PERSONS SELECTION ---
    constructor.querySelectorAll('.pbtn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var n = parseInt(btn.dataset.persons);
            constructor.querySelectorAll('.pbtn').forEach(function(b) { b.classList.remove('on'); });
            btn.classList.add('on');

            // Update set box for selected persons count
            var setBox = document.getElementById('setBox');
            setBox.style.display = 'flex';
            var d = setData[n];
            if (d) {
                document.getElementById('setTitle').textContent = d.title;
                document.getElementById('setDesc').textContent = d.desc;
                document.getElementById('setOld').textContent = formatPrice(d.oldPrice);
                document.getElementById('setNew').textContent = formatPrice(d.newPrice);
            }

            recalcTotal();
            updateGallery();
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

    // --- HELPER: обновить цены поштучных товаров при смене цвета ---
    function updateItemPricesForColor(colorSlug) {
        if (typeof window.LORALEYA_ITEM_PRICES_BY_COLOR === 'undefined') return;
        var prices = window.LORALEYA_ITEM_PRICES_BY_COLOR[colorSlug];
        if (!prices) return;

        // Обновляем поштучные строки (.ir) — data-price и видимый текст
        document.querySelectorAll('.ir[data-item]').forEach(function(el) {
            var key = el.getAttribute('data-item');
            if (!prices[key]) return;
            var price = parseInt(prices[key].price, 10);
            if (!price) return;
            el.setAttribute('data-price', price);
            var priceEl = el.querySelector('.ir-price');
            if (priceEl) {
                var oldText = priceEl.textContent;
                var suffixMatch = oldText.match(/\s*\/\s*\S+\s*$/);
                var suffix = suffixMatch ? suffixMatch[0] : ' / шт';
                priceEl.textContent = price.toLocaleString('ru-RU') + ' ₽' + suffix;
            }
        });

        // Пересчитываем итог (data-price изменились)
        recalcTotal();

        // Обновляем карточку готового набора
        var activePer = constructor.querySelector('.pbtn.on');
        var persons = parseInt(activePer ? activePer.dataset.persons : '4', 10);
        var keyByPersons = { 2: 'Набор 2п/140', 4: 'Набор 4п/140', 6: 'Набор 6п/240' };
        var setKey = keyByPersons[persons];
        if (setKey && prices[setKey]) {
            var setBox = document.getElementById('setBox');
            if (setBox) {
                var newP = parseInt(prices[setKey].price, 10) || 0;
                var oldP = parseInt((prices[setKey].old_price || 0), 10);
                var np = document.getElementById('setNew');
                var op = document.getElementById('setOld');
                if (np) np.textContent = formatPrice(newP);
                if (op) op.textContent = oldP > newP ? formatPrice(oldP) : '';
            }
        }
    }

    // --- HELPER: получить item_map для текущего активного цвета ---
    function getCurrentItemMap() {
        if (typeof window.LORALEYA_ITEM_MAP_BY_COLOR === 'undefined') return null;
        var activeSwatch = constructor.querySelector('.sw.on');
        if (!activeSwatch) return null;
        var colorSlug = activeSwatch.dataset.color;
        return window.LORALEYA_ITEM_MAP_BY_COLOR[colorSlug] || null;
    }

    // --- HELPER: проверить что wcAddToCart доступен (он живёт в main.js) ---
    function canAddToCart() {
        return typeof window.wcAddToCart === 'function';
    }

    // --- ADD TO CART: одиночные позиции (поштучно) ---
    var cartBtn = document.getElementById('addCartBtn');
    if (cartBtn) {
        cartBtn.addEventListener('click', function() {
            if (cartBtn.disabled) return;

            var rowsToAdd = [];
            constructor.querySelectorAll('.ir').forEach(function(row) {
                var qv  = row.querySelector('.qv');
                var qty = parseInt(qv ? qv.textContent : '0', 10);
                var item = row.dataset.item;
                if (qty > 0 && item) {
                    rowsToAdd.push({ row: row, item: item, qty: qty });
                }
            });

            if (rowsToAdd.length === 0) {
                cartBtn.style.background = '#7a4a4a';
                cartBtn.style.borderColor = '#7a4a4a';
                cartBtn.style.color = '#fff';
                setTimeout(function() {
                    cartBtn.style.background = '';
                    cartBtn.style.borderColor = '';
                    cartBtn.style.color = '';
                }, 500);
                return;
            }

            if (!canAddToCart()) {
                console.warn('wcAddToCart not available — cart not connected');
                return;
            }

            var map = getCurrentItemMap();
            if (!map) {
                console.warn('No item_map for current color');
                return;
            }

            cartBtn.disabled = true;
            var originalText = cartBtn.textContent;
            cartBtn.textContent = 'Отправляется…';

            var anyError = false;
            var index = 0;

            function updateProgress() {
                if (rowsToAdd.length > 1) {
                    cartBtn.textContent = 'Отправляется… ' + index + '/' + rowsToAdd.length;
                }
            }

            function sendNext() {
                if (index >= rowsToAdd.length) {
                    finishAdd();
                    return;
                }

                var entry  = rowsToAdd[index];
                var mapped = map[entry.item];
                updateProgress();
                index++;

                if (!mapped) {
                    anyError = true;
                    console.warn('Нет mapping для позиции:', entry.item);
                    sendNext();
                    return;
                }

                window.wcAddToCart(mapped, entry.qty, function(cart_key) {
                    if (!cart_key) {
                        anyError = true;
                        console.warn('Ошибка добавления в корзину:', entry.item);
                    }
                    sendNext();
                });
            }
            sendNext();

            function finishAdd() {
                if (anyError) {
                    cartBtn.textContent = 'Ошибка ✕';
                    cartBtn.style.background = '#7a4a4a';
                    cartBtn.style.borderColor = '#7a4a4a';
                    cartBtn.style.color = '#fff';
                    setTimeout(function() {
                        cartBtn.disabled = false;
                        cartBtn.textContent = originalText;
                        cartBtn.style.background = '';
                        cartBtn.style.borderColor = '';
                        cartBtn.style.color = '';
                    }, 2000);
                    return;
                }

                rowsToAdd.forEach(function(entry) {
                    var qv = entry.row.querySelector('.qv');
                    if (qv) qv.textContent = '0';
                    updateRowSub(entry.row);
                });
                recalcTotal();

                cartBtn.textContent = 'Добавлено ✓';
                setTimeout(function() {
                    cartBtn.disabled = false;
                    cartBtn.textContent = originalText;
                }, 2000);
            }
        });
    }

    // --- ADD SET: добавление готового набора ---
    var setBtn = document.getElementById('addSetBtn');
    if (setBtn) {
        setBtn.addEventListener('click', function() {
            if (setBtn.disabled) return;

            if (!canAddToCart()) {
                console.warn('wcAddToCart not available — cart not connected');
                return;
            }

            var map = getCurrentItemMap();
            if (!map) {
                console.warn('No item_map for current color');
                return;
            }

            var activePer = constructor.querySelector('.pbtn.on');
            var persons = parseInt(activePer ? activePer.dataset.persons : '4', 10);

            var keyByPersons = {
                2: 'Набор 2п/140',
                4: 'Набор 4п/140',
                6: 'Набор 6п/240',
            };
            var setKey = keyByPersons[persons];
            if (!setKey) {
                console.warn('No set key for persons:', persons);
                return;
            }

            var mapped = map[setKey];
            if (!mapped) {
                console.warn('No mapping for set:', setKey);
                return;
            }

            setBtn.disabled = true;
            var originalText = setBtn.textContent;
            setBtn.textContent = 'Отправляется…';

            window.wcAddToCart(mapped, 1, function(cart_key) {
                if (cart_key) {
                    setBtn.textContent = 'Добавлено ✓';
                } else {
                    setBtn.textContent = 'Ошибка ✕';
                }
                setTimeout(function() {
                    setBtn.disabled = false;
                    setBtn.textContent = originalText;
                }, 2000);
            });
        });
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

    // --- GALLERY UPDATE ---
    function updateGallery() {
        var photos = window.LORALEYA_GALLERY_PHOTOS;
        if (!photos) return;

        var gallery = document.querySelector('.sc-gallery');
        if (!gallery) return;

        var activeSwatch = constructor.querySelector('.sw.on');
        var activePer    = constructor.querySelector('.pbtn.on');
        if (!activeSwatch || !activePer) return;

        var colorSlug = activeSwatch.dataset.color;
        var persons   = parseInt(activePer.dataset.persons);

        var colorPhotos = photos[colorSlug];
        if (!colorPhotos) return;

        var photosFull      = window.LORALEYA_GALLERY_PHOTOS_FULL || {};
        var colorPhotosFull = photosFull[colorSlug] || {};

        var naborKey = persons === 2 ? 'nabor-4-140'
                     : persons === 4 ? 'nabor-4-175'
                     : 'nabor-6-300';
        applySlot('overall', colorPhotos[naborKey], colorPhotosFull[naborKey]);
        applySlot('napkin',  colorPhotos['napkin'],  colorPhotosFull['napkin']);
        applySlot('kuvert',  colorPhotos['kuvert'],  colorPhotosFull['kuvert']);
        applySlot('faktura', colorPhotos['faktura'], colorPhotosFull['faktura']);

        gallery.dataset.color   = colorSlug;
        gallery.dataset.persons = persons;
    }

    function applySlot(slotName, url, fullUrl) {
        var slot = document.querySelector('.sc-gallery-item[data-slot="' + slotName + '"]');
        if (!slot) return;
        if (url) {
            slot.style.backgroundImage = "url('" + url + "')";
            slot.setAttribute('data-full', fullUrl || url);
            var ph = slot.querySelector('.sc-gallery-ph');
            if (ph) ph.style.display = 'none';
        } else {
            slot.style.backgroundImage = '';
            slot.removeAttribute('data-full');
            var ph2 = slot.querySelector('.sc-gallery-ph');
            if (ph2) ph2.style.display = '';
        }
    }

    // --- INIT ---
    // Активировать дефолтный свотч по data-default-color на контейнере палитры
    var swatchesContainer = constructor.querySelector('.swatches');
    var defaultColor = swatchesContainer ? swatchesContainer.dataset.defaultColor : null;
    if (defaultColor) {
        var defaultSwatch = constructor.querySelector('.sw[data-color="' + defaultColor + '"]');
        if (defaultSwatch) {
            constructor.querySelectorAll('.sw').forEach(function (s) { s.classList.remove('on'); });
            defaultSwatch.classList.add('on');
            updateItemPricesForColor(defaultColor);
        }
    }

    // Дефолтные персоны: устанавливаем визуальное состояние БЕЗ триггера обработчика клика,
    // чтобы не сработало автозаполнение салфеток/кувёртов количеством персон.
    var defaultPersons = parseInt(constructor.dataset.defaultPersons) || 2;
    var defaultBtn = constructor.querySelector('.pbtn[data-persons="' + defaultPersons + '"]');
    if (defaultBtn) {
        constructor.querySelectorAll('.pbtn').forEach(function (b) { b.classList.remove('on'); });
        defaultBtn.classList.add('on');

        var setBox = document.getElementById('setBox');
        if (setBox) {
            setBox.style.display = 'flex';
            var d = setData[defaultPersons];
            if (d) {
                var t  = document.getElementById('setTitle');
                var s  = document.getElementById('setDesc');
                var op = document.getElementById('setOld');
                var np = document.getElementById('setNew');
                if (t)  t.textContent  = d.title;
                if (s)  s.textContent  = d.desc;
                if (op) op.textContent = formatPrice(d.oldPrice);
                if (np) np.textContent = formatPrice(d.newPrice);
            }
        }
    }

    // --- LISTEN FOR EXTERNAL RESET (от кнопки "Добавить в другом цвете") ---
    document.addEventListener('loraleya:constructor-reset', function() {
        constructor.querySelectorAll('.ir').forEach(function(row) {
            var qv = row.querySelector('.qv');
            if (qv) qv.textContent = '0';
            updateRowSub(row);
        });

        recalcTotal();

        var firstStep = constructor.querySelector('.grp');
        if (firstStep) {
            firstStep.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
