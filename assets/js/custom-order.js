/* Custom Order Page — positional request form */
(function () {
    'use strict';

    var form = document.getElementById('customOrderForm');
    if (!form) return;

    var productList = document.getElementById('coProductList');
    var productTemplate = document.getElementById('coProductTemplate');
    var addProductButton = document.getElementById('coAddProduct');
    var summaryItems = document.getElementById('coSumItems');
    var btnSubmit = form.querySelector('.co-btn-submit');
    var resSuccess = document.getElementById('coResultSuccess');
    var resError = document.getElementById('coResultError');
    var formErrorEl = null;
    var submissionLocked = false;

    function ensureFormErrorEl() {
        if (formErrorEl) return formErrorEl;
        formErrorEl = document.createElement('div');
        formErrorEl.className = 'co-form-error';
        formErrorEl.hidden = true;
        if (btnSubmit && btnSubmit.parentNode) btnSubmit.parentNode.appendChild(formErrorEl);
        return formErrorEl;
    }

    function showFormError(message, target) {
        var error = ensureFormErrorEl();
        error.textContent = message;
        error.hidden = false;
        if (target && target.scrollIntoView) {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function hideFormError() {
        if (formErrorEl) formErrorEl.hidden = true;
    }

    function clearFieldError(element) {
        if (!element) return;
        element.classList.remove('co-ct-input--error', 'co-item-colors--error');
        if (!form.querySelector('.co-ct-input--error, .co-item-colors--error, .co-consent--error')) {
            hideFormError();
        }
    }

    function cards() {
        return productList ? Array.prototype.slice.call(productList.querySelectorAll('[data-item-card]')) : [];
    }

    function renumberCards() {
        cards().forEach(function (card, index) {
            var number = card.querySelector('[data-item-number]');
            var remove = card.querySelector('[data-remove-item]');
            if (number) number.textContent = String(index + 1);
            if (remove) remove.hidden = index === 0;
        });
    }

    function selectedColor(card) {
        return card ? card.querySelector('.co-item-swatch--on') : null;
    }

    function itemFromCard(card) {
        var typeSelect = card.querySelector('[data-item-field="item_type"]');
        var customName = card.querySelector('[data-item-field="item_name"]');
        var size = card.querySelector('[data-item-field="size"]');
        var quantity = card.querySelector('[data-item-field="quantity"]');
        var comment = card.querySelector('[data-item-field="comment"]');
        var color = selectedColor(card);
        var type = typeSelect ? typeSelect.value : '';
        var option = typeSelect && typeSelect.selectedIndex >= 0 ? typeSelect.options[typeSelect.selectedIndex] : null;
        var name = type === 'other'
            ? (customName ? customName.value.trim() : '')
            : (option && type ? option.textContent.trim() : '');

        return {
            item_type: type,
            item_name: name,
            size: size ? size.value.trim() : '',
            color_slug: color ? (color.dataset.colorSlug || '') : '',
            color_name: color ? (color.dataset.colorName || '') : '',
            quantity: quantity ? parseInt(quantity.value, 10) : 0,
            comment: comment ? comment.value.trim() : ''
        };
    }

    function updateOtherName(card) {
        var typeSelect = card.querySelector('[data-item-field="item_type"]');
        var otherBlock = card.querySelector('[data-other-name]');
        var otherInput = card.querySelector('[data-item-field="item_name"]');
        var isOther = typeSelect && typeSelect.value === 'other';
        if (otherBlock) otherBlock.hidden = !isOther;
        if (otherInput) {
            otherInput.required = !!isOther;
            if (!isOther) {
                otherInput.value = '';
                clearFieldError(otherInput);
            }
        }
    }

    function updateSummary() {
        if (!summaryItems) return;
        summaryItems.textContent = '';
        var hasContent = false;

        cards().forEach(function (card) {
            var item = itemFromCard(card);
            if (!item.item_name && !item.size && !item.color_name) return;

            var parts = [item.item_name || 'Изделие не выбрано'];
            if (item.size) parts.push(item.size);
            if (item.color_name) parts.push(item.color_name);
            parts.push((item.quantity >= 1 ? item.quantity : 1) + ' шт.');

            var line = document.createElement('div');
            line.className = 'co-sum-item';
            line.textContent = parts.join(' — ');
            summaryItems.appendChild(line);
            hasContent = true;
        });

        if (!hasContent) {
            var empty = document.createElement('div');
            empty.className = 'co-sum-empty';
            empty.textContent = 'Заполните первую позицию выше.';
            summaryItems.appendChild(empty);
        }
    }

    function addProductCard() {
        if (!productList || !productTemplate || !productTemplate.content.firstElementChild) return;
        var card = productTemplate.content.firstElementChild.cloneNode(true);
        productList.appendChild(card);
        renumberCards();
        updateOtherName(card);
        updateSummary();
        var firstField = card.querySelector('[data-item-field="item_type"]');
        if (firstField) firstField.focus();
    }

    if (addProductButton) addProductButton.addEventListener('click', addProductCard);

    if (productList) {
        productList.addEventListener('click', function (event) {
            var remove = event.target.closest('[data-remove-item]');
            if (remove) {
                var currentCards = cards();
                var removeCard = remove.closest('[data-item-card]');
                if (removeCard && currentCards.length > 1) removeCard.remove();
                renumberCards();
                updateSummary();
                return;
            }

            var color = event.target.closest('.co-item-swatch');
            if (color) {
                var colorCard = color.closest('[data-item-card]');
                colorCard.querySelectorAll('.co-item-swatch').forEach(function (swatch) {
                    var isSelected = swatch === color;
                    swatch.classList.toggle('co-item-swatch--on', isSelected);
                    swatch.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
                });
                clearFieldError(colorCard.querySelector('[data-item-colors]'));
                updateSummary();
                return;
            }

            var qtyButton = event.target.closest('[data-qty-delta]');
            if (qtyButton) {
                var qtyInput = qtyButton.closest('.co-qty').querySelector('[data-item-field="quantity"]');
                var current = parseInt(qtyInput.value, 10);
                var delta = parseInt(qtyButton.dataset.qtyDelta, 10);
                qtyInput.value = String(Math.max(1, (Number.isInteger(current) ? current : 1) + delta));
                clearFieldError(qtyInput);
                updateSummary();
            }
        });

        productList.addEventListener('input', function (event) {
            if (event.target.matches('[data-item-field]')) {
                clearFieldError(event.target);
                updateSummary();
            }
        });

        productList.addEventListener('change', function (event) {
            if (event.target.matches('[data-item-field="item_type"]')) {
                updateOtherName(event.target.closest('[data-item-card]'));
            }
            if (event.target.matches('[data-item-field]')) {
                clearFieldError(event.target);
                updateSummary();
            }
        });
    }

    document.querySelectorAll('.co-faq-q').forEach(function (question) {
        question.addEventListener('click', function () {
            var faq = question.closest('.co-faq');
            var isOpen = faq.classList.contains('co-faq--open');
            document.querySelectorAll('.co-faq').forEach(function (item) {
                item.classList.remove('co-faq--open');
            });
            if (!isOpen) faq.classList.add('co-faq--open');
        });
    });

    function createRequestToken() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }
        if (window.crypto && typeof window.crypto.getRandomValues === 'function') {
            var bytes = new Uint8Array(16);
            window.crypto.getRandomValues(bytes);
            return Array.prototype.map.call(bytes, function (byte) {
                return byte.toString(16).padStart(2, '0');
            }).join('');
        }
        return Date.now().toString(36) + '-' + Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2);
    }

    var requestToken = createRequestToken();

    function normalizeRussianPhone(value) {
        var phone = String(value || '').trim();
        if (!phone || !/^\+?[\d\s().\-–—]+$/.test(phone)) return '';
        var digits = phone.replace(/\D/g, '');
        if (phone.charAt(0) === '+') return /^7\d{10}$/.test(digits) ? '+' + digits : '';
        if (/^8\d{10}$/.test(digits)) return '+7' + digits.slice(1);
        return /^7\d{10}$/.test(digits) ? '+' + digits : '';
    }

    function isValidEmail(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || '').trim());
    }

    function validateItems() {
        var result = [];
        var cardList = cards();
        for (var index = 0; index < cardList.length; index += 1) {
            var card = cardList[index];
            var item = itemFromCard(card);
            var itemNumber = index + 1;
            var type = card.querySelector('[data-item-field="item_type"]');
            var customName = card.querySelector('[data-item-field="item_name"]');
            var size = card.querySelector('[data-item-field="size"]');
            var quantity = card.querySelector('[data-item-field="quantity"]');
            var colors = card.querySelector('[data-item-colors]');

            if (!item.item_type) {
                type.classList.add('co-ct-input--error');
                return { message: 'Выберите изделие в позиции ' + itemNumber + '.', target: type };
            }
            if (!item.item_name) {
                customName.classList.add('co-ct-input--error');
                return { message: 'Укажите название изделия в позиции ' + itemNumber + '.', target: customName };
            }
            if (!item.size) {
                size.classList.add('co-ct-input--error');
                return { message: 'Укажите размер или параметры позиции ' + itemNumber + '.', target: size };
            }
            if (!item.color_slug) {
                colors.classList.add('co-item-colors--error');
                return { message: 'Выберите цвет позиции ' + itemNumber + '.', target: colors };
            }
            if (!Number.isInteger(item.quantity) || item.quantity < 1) {
                quantity.classList.add('co-ct-input--error');
                return { message: 'Количество в позиции ' + itemNumber + ' должно быть целым числом от 1.', target: quantity };
            }
            result.push(item);
        }
        return { items: result };
    }

    function appendItems(formData, items) {
        items.forEach(function (item, index) {
            Object.keys(item).forEach(function (field) {
                formData.append('items[' + index + '][' + field + ']', String(item[field]));
            });
        });
    }

    function collectFormData(items) {
        var formData = new FormData();
        formData.append('action', 'loraleya_custom_order');
        formData.append('request_token', requestToken);

        var nonce = form.querySelector('input[name="co_nonce"]');
        var honey = form.querySelector('input[name="website"]');
        if (nonce) formData.append('co_nonce', nonce.value);
        if (honey) formData.append('website', honey.value);

        formData.append('customer_name', document.getElementById('coName').value.trim());
        formData.append('customer_contact', document.getElementById('coContact').value.trim());
        formData.append('customer_email', document.getElementById('coEmail').value.trim());
        formData.append('delivery_address', document.getElementById('coDeliveryAddress').value.trim());
        formData.append('customer_notes', document.getElementById('coNotes').value.trim());
        if (document.getElementById('coConsent').checked) formData.append('consent', '1');
        appendItems(formData, items);
        return formData;
    }

    function showError(message) {
        if (!resError) return;
        resError.textContent = '';
        var strong = document.createElement('strong');
        strong.textContent = 'Ошибка отправки. ';
        resError.appendChild(strong);
        resError.appendChild(document.createTextNode(message || 'Попробуйте ещё раз.'));
        resError.hidden = false;
        resError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function showSuccess(requestNumber) {
        if (!resSuccess) return;
        resSuccess.textContent = '';
        var message = document.createElement('div');
        message.textContent = requestNumber
            ? 'Заявка ' + requestNumber + ' принята. Мы свяжемся с вами для согласования деталей.'
            : 'Заявка принята. Мы свяжемся с вами для согласования деталей.';
        resSuccess.appendChild(message);

        var newRequestButton = document.createElement('button');
        newRequestButton.type = 'button';
        newRequestButton.className = 'co-btn-submit co-btn-new-request';
        newRequestButton.textContent = 'Отправить ещё одну заявку';
        newRequestButton.style.marginTop = '1rem';
        newRequestButton.addEventListener('click', resetFormToInitial);
        resSuccess.appendChild(newRequestButton);
        resSuccess.hidden = false;
        resSuccess.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    var consent = document.getElementById('coConsent');
    var consentBlock = document.querySelector('.co-consent');
    if (consent && consentBlock) {
        consent.addEventListener('change', function () {
            if (consent.checked) {
                consentBlock.classList.remove('co-consent--error');
                clearFieldError(consentBlock);
            }
        });
    }

    ['coName', 'coContact', 'coEmail', 'coDeliveryAddress'].forEach(function (id) {
        var input = document.getElementById(id);
        if (input) input.addEventListener('input', function () { clearFieldError(input); });
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        if (submissionLocked) return;

        hideFormError();
        if (consentBlock) consentBlock.classList.remove('co-consent--error');

        var itemValidation = validateItems();
        if (!itemValidation.items) {
            showFormError(itemValidation.message, itemValidation.target);
            return;
        }

        var name = document.getElementById('coName');
        var contact = document.getElementById('coContact');
        var email = document.getElementById('coEmail');
        var address = document.getElementById('coDeliveryAddress');

        if (!name.value.trim()) {
            name.classList.add('co-ct-input--error');
            showFormError('Заполните ФИО.', name);
            return;
        }
        var normalizedPhone = normalizeRussianPhone(contact.value);
        if (!normalizedPhone) {
            contact.classList.add('co-ct-input--error');
            showFormError('Введите номер телефона, например +79991234567', contact);
            return;
        }
        contact.value = normalizedPhone;
        if (!isValidEmail(email.value)) {
            email.classList.add('co-ct-input--error');
            showFormError(email.value.trim() ? 'Введите корректный адрес электронной почты.' : 'Введите электронную почту.', email);
            return;
        }
        email.value = email.value.trim();
        if (!address.value.trim()) {
            address.classList.add('co-ct-input--error');
            showFormError('Укажите адрес доставки.', address);
            return;
        }
        if (!consent || !consent.checked) {
            if (consentBlock) consentBlock.classList.add('co-consent--error');
            showFormError('Чтобы отправить заявку, отметьте согласие с политикой обработки персональных данных.', consentBlock);
            return;
        }

        submissionLocked = true;
        if (resError) resError.hidden = true;
        if (resSuccess) resSuccess.hidden = true;
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Отправляется…';
        }

        var ajaxUrl = window.loraleya && window.loraleya.ajax_url ? window.loraleya.ajax_url : form.action;
        fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: collectFormData(itemValidation.items)
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data && data.success) {
                var requestNumber = data.data && data.data.request_number ? data.data.request_number : '';
                showSuccess(requestNumber);
                if (btnSubmit) btnSubmit.textContent = 'Отправлено ✓';
                return;
            }
            submissionLocked = false;
            var message = data && data.data && data.data.message ? data.data.message : 'Попробуйте ещё раз.';
            showError(message);
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Отправить заявку';
            }
        })
        .catch(function () {
            submissionLocked = false;
            showError('Проблема с сетью. Проверьте подключение и попробуйте снова.');
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Отправить заявку';
            }
        });
    });

    function resetCard(card) {
        card.querySelectorAll('.co-item-swatch').forEach(function (swatch) {
            swatch.classList.remove('co-item-swatch--on');
            swatch.setAttribute('aria-pressed', 'false');
        });
        card.querySelectorAll('.co-ct-input--error, .co-item-colors--error').forEach(function (element) {
            element.classList.remove('co-ct-input--error', 'co-item-colors--error');
        });
        updateOtherName(card);
    }

    function resetFormToInitial() {
        requestToken = createRequestToken();
        submissionLocked = false;
        form.reset();
        var cardList = cards();
        cardList.slice(1).forEach(function (card) { card.remove(); });
        if (cardList[0]) resetCard(cardList[0]);
        renumberCards();
        updateSummary();
        hideFormError();
        if (consentBlock) consentBlock.classList.remove('co-consent--error');
        if (resSuccess) resSuccess.hidden = true;
        if (resError) resError.hidden = true;
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Отправить заявку';
        }
        var configSection = document.querySelector('.co-config');
        if (configSection) configSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    renumberCards();
    cards().forEach(updateOtherName);
    updateSummary();
})();
