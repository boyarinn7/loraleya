/* Custom Order Page — front-end logic */
(function () {
    'use strict';

    /* ── Shape selector ── */
    var shapes = document.querySelectorAll('.co-shape');
    var sumShape = document.getElementById('coSumShape');
    shapes.forEach(function (el) {
        el.addEventListener('click', function () {
            shapes.forEach(function (s) { s.classList.remove('co-shape--on'); });
            el.classList.add('co-shape--on');
            if (sumShape) sumShape.textContent = el.dataset.name || '';
        });
    });

    /* ── Persons selector ── */
    var persons = document.querySelectorAll('.co-per');
    var sumPers = document.getElementById('coSumPers');
    persons.forEach(function (el) {
        el.addEventListener('click', function () {
            persons.forEach(function (p) { p.classList.remove('co-per--on'); });
            el.classList.add('co-per--on');
            if (sumPers) sumPers.textContent = el.dataset.value || '';
        });
    });

    /* ── Color swatch selector ── */
    var swatches = document.querySelectorAll('.co-sw');
    var colorLabel = document.getElementById('coColorLabel');
    var sumColor = document.getElementById('coSumColor');
    swatches.forEach(function (el) {
        el.addEventListener('click', function () {
            swatches.forEach(function (s) { s.classList.remove('co-sw--on'); });
            el.classList.add('co-sw--on');
            if (sumColor) sumColor.textContent = el.dataset.name || '';
        });
    });

    /* ── Dimensions → summary ── */
    var dimL   = document.getElementById('coDimL');
    var dimW   = document.getElementById('coDimW');
    var sumSize = document.getElementById('coSumSize');
    function updateSize() {
        if (!sumSize) return;
        var l = (dimL && dimL.value) ? dimL.value : '';
        var w = (dimW && dimW.value) ? dimW.value : '';
        if (l && w) {
            sumSize.textContent = l + ' × ' + w + ' см';
        } else if (l) {
            sumSize.textContent = l + ' × …';
        } else {
            sumSize.textContent = 'Укажите выше';
        }
    }
    if (dimL) dimL.addEventListener('input', updateSize);
    if (dimW) dimW.addEventListener('input', updateSize);

    /* ── Toggle + qty ── */
    function updateItemsSummary() {
        var sumItems = document.getElementById('coSumItems');
        if (!sumItems) return;
        var parts = [];
        document.querySelectorAll('.co-item-row').forEach(function (row) {
            var toggle = row.querySelector('.co-toggle');
            if (!toggle || toggle.dataset.on !== '1') return;
            var name = row.dataset.name || '';
            var qty  = parseInt(row.querySelector('.co-qty-val').textContent, 10);
            parts.push(qty > 1 ? name + ' ×' + qty : name);
        });
        sumItems.textContent = parts.length ? parts.join(', ') : 'Ничего не выбрано';
    }

    document.querySelectorAll('.co-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var row = toggle.closest('.co-item-row');
            var on  = toggle.dataset.on === '1';
            toggle.dataset.on = on ? '0' : '1';
            toggle.classList.toggle('co-toggle--on', !on);
            var qtyBlock = row ? row.querySelector('.co-qty') : null;
            if (qtyBlock) qtyBlock.style.opacity = on ? '0.3' : '';
            updateItemsSummary();
        });
    });

    document.querySelectorAll('.co-qty-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var qtyEl = btn.closest('.co-qty').querySelector('.co-qty-val');
            if (!qtyEl) return;
            var val = parseInt(qtyEl.textContent, 10);
            var delta = parseInt(btn.dataset.delta, 10);
            val = Math.max(1, Math.min(12, val + delta));
            qtyEl.textContent = val;
            updateItemsSummary();
        });
    });

    updateItemsSummary();

    /* ── FAQ accordion ── */
    document.querySelectorAll('.co-faq-q').forEach(function (q) {
        q.addEventListener('click', function () {
            var faq    = q.closest('.co-faq');
            var isOpen = faq.classList.contains('co-faq--open');
            document.querySelectorAll('.co-faq').forEach(function (f) {
                f.classList.remove('co-faq--open');
            });
            if (!isOpen) faq.classList.add('co-faq--open');
        });
    });

    /* ── Submit (реальная отправка через AJAX) ── */
    var form       = document.getElementById('customOrderForm');
    var btnSubmit  = form ? form.querySelector('.co-btn-submit') : null;
    var resSuccess = document.getElementById('coResultSuccess');
    var resError   = document.getElementById('coResultError');

    function showError(msg) {
        if (!resError) return;
        resError.hidden = false;
        if (msg) {
            resError.innerHTML = '<strong>Ошибка отправки.</strong> ' + msg;
        }
        resError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function showSuccess(msg) {
        if (!resSuccess) return;
        resSuccess.hidden = false;
        if (msg) {
            resSuccess.innerHTML = '<strong>Заявка отправлена!</strong> ' + msg;
        }
        resSuccess.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function collectFormData() {
        var fd = new FormData();

        fd.append('action', 'loraleya_custom_order');

        var nonceEl = form.querySelector('input[name="co_nonce"]');
        if (nonceEl) fd.append('co_nonce', nonceEl.value);

        var honey = form.querySelector('input[name="website"]');
        if (honey) fd.append('website', honey.value);

        fd.append('customer_name',    document.getElementById('coName').value.trim());
        fd.append('customer_contact', document.getElementById('coContact').value.trim());
        fd.append('customer_notes',   document.getElementById('coNotes').value.trim());

        if (document.getElementById('coConsent').checked) fd.append('consent', '1');

        var shapeOn = document.querySelector('.co-shape.co-shape--on');
        if (shapeOn) {
            fd.append('shape',      shapeOn.dataset.value || '');
            fd.append('shape_name', shapeOn.dataset.name  || '');
        }

        fd.append('dim_length', document.getElementById('coDimL').value || '');
        fd.append('dim_width',  document.getElementById('coDimW').value || '');

        var persOn = document.querySelector('.co-per.co-per--on');
        if (persOn) fd.append('persons', persOn.dataset.value || '');

        var colorOn = document.querySelector('.co-sw.co-sw--on');
        if (colorOn) {
            fd.append('color',      colorOn.dataset.value || '');
            fd.append('color_name', colorOn.dataset.name  || '');
        }

        var itemsEl = document.getElementById('coSumItems');
        if (itemsEl) fd.append('items_summary', itemsEl.textContent.trim());

        if (document.getElementById('coOptMono').checked)  fd.append('opt_monogram', '1');
        if (document.getElementById('coOptEdge').checked)  fd.append('opt_edge',     '1');
        if (document.getElementById('coOptRings').checked) fd.append('opt_rings',    '1');

        return fd;
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var nameEl    = document.getElementById('coName');
            var contactEl = document.getElementById('coContact');
            var consentEl = document.getElementById('coConsent');

            var valid = true;
            [nameEl, contactEl].forEach(function (el) {
                if (!el || !el.value.trim()) {
                    if (el) el.classList.add('co-ct-input--error');
                    valid = false;
                } else {
                    if (el) el.classList.remove('co-ct-input--error');
                }
            });
            if (!consentEl || !consentEl.checked) valid = false;

            if (!valid) return;

            if (resError)   resError.hidden = true;
            if (resSuccess) resSuccess.hidden = true;
            if (btnSubmit)  btnSubmit.disabled = true;
            if (btnSubmit)  btnSubmit.textContent = 'Отправляется…';

            var ajaxUrl = (window.loraleya && window.loraleya.ajax_url)
                ? window.loraleya.ajax_url
                : '/wp-admin/admin-ajax.php';

            fetch(ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: collectFormData()
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success) {
                    showSuccess(data.data && data.data.message ? data.data.message : '');
                    if (btnSubmit) btnSubmit.textContent = 'Отправлено ✓';
                } else {
                    var msg = (data && data.data && data.data.message) ? data.data.message : 'Попробуйте ещё раз.';
                    showError(msg);
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.textContent = 'Отправить заявку →';
                    }
                }
            })
            .catch(function () {
                showError('Проблема с сетью. Проверьте подключение и попробуйте снова.');
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'Отправить заявку →';
                }
            });
        });
    }

})();
