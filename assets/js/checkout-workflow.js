(function ($) {
    'use strict';

    var previousService = '';
    var consentChecked = null;
    var checkoutSubmitting = false;
    var addressUpdateTimer = null;

    function selectedShippingRate() {
        var $selected = $('input[name^="shipping_method"]:checked').first();

        if (!$selected.length) {
            $selected = $('input[name^="shipping_method"]').first();
        }

        return $selected.length ? String($selected.val() || '') : '';
    }

    function selectedDeliveryService() {
        var rate = selectedShippingRate();

        if (rate.indexOf('fivepost_shipping_method') === 0) {
            return 'fivepost';
        }
        if (rate.indexOf('ll_cdek') === 0) {
            return 'cdek';
        }
        if (rate.indexOf('ll_yandex') === 0) {
            return 'yandex';
        }

        return '';
    }

    function normalizeRegion(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/ё/g, 'е')
            .replace(/[^a-zа-я0-9]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function isMoscowRegion(value) {
        var region = normalizeRegion(value);
        var exactValues = [
            'mow',
            'mos',
            'москва',
            'г москва',
            'город москва',
            'moscow',
            'moskva',
            'московская область',
            'московская обл',
            'московская обл р н',
            'мо',
            'moscow oblast',
            'moskovskaya oblast'
        ];

        if (exactValues.indexOf(region) !== -1) {
            return true;
        }

        return /^московская (?:область|обл)(?:\s|$)/.test(region);
    }

    function moveDeliveryPanel() {
        var $panel = $('.ll-delivery-panel').first();
        var $regionField = $('#billing_state_field');

        if ($panel.length && $regionField.length && !$panel.next().is($regionField)) {
            $panel.insertBefore($regionField);
        }
    }

    function mountShippingMethods() {
        var $host = $('#ll-delivery-methods-host');
        var $source = $('#order_review #shipping_method').first();

        if (!$source.length) {
            $source = $('#order_review .woocommerce-shipping-methods').first();
        }

        if (!$host.length) {
            return;
        }

        if ($source.length) {
            var $sourceRow = $source.closest('tr.woocommerce-shipping-totals, tr.shipping');

            $sourceRow.addClass('ll-shipping-row-source');
            $host.empty().append($source);
            $host.removeClass('ll-delivery-methods-empty');
        } else if (!$host.find('input[name^="shipping_method"]').length) {
            $host
                .addClass('ll-delivery-methods-empty')
                .html('<p class="ll-delivery-loading">Введите регион и город, чтобы выбрать способ доставки.</p>');
        }
    }

    function toggleField(fieldSelector, visible) {
        var $field = $(fieldSelector);

        $field.toggleClass('ll-field-hidden', !visible);
        $field.attr('aria-hidden', visible ? 'false' : 'true');
        $field.toggle(visible);
    }

    function setFieldRequired(fieldSelector, required) {
        var $field = $(fieldSelector);
        var $controls = $field.find('input, select, textarea');

        $field.toggleClass('validate-required', required);
        $field.toggleClass('woocommerce-invalid woocommerce-invalid-required-field', false);
        $controls.attr('aria-required', required ? 'true' : 'false');

        $field.find('label > .required').remove();
        if (required) {
            $field.find('label').first().append(' <span class="required" aria-hidden="true">*</span>');
        }
    }

    function clearFivePostPoint() {
        $('input[name="fivepost_point_id"], input[name="fivepost_point_zone"]').val('');
    }

    function clearManagerDeliveryFields() {
        $('input[name="billing_delivery_mode"]').prop('checked', false);
        $('#billing_pickup_address, #billing_address_2, #billing_postcode').val('');
    }

    function updateTariffMessage(service) {
        var $message = $('.ll-delivery-tariff').first();
        var region = $('#billing_state').val();
        var text = '';

        if (!$message.length) {
            return;
        }

        if (service === 'fivepost') {
            if (!normalizeRegion(region)) {
                text = 'Укажите регион: для Москвы и Московской области доставка 5Post бесплатная, для других регионов России — 250 ₽.';
            } else if (isMoscowRegion(region)) {
                text = '5Post: доставка по Москве и Московской области — бесплатно.';
            } else {
                text = '5Post: доставка в другие регионы России — 250 ₽.';
            }
        } else if (service === 'cdek') {
            text = 'СДЭК: стоимость и срок доставки рассчитает менеджер после проверки заказа.';
        } else if (service === 'yandex') {
            text = 'Яндекс Доставка: стоимость и срок доставки рассчитает менеджер после проверки заказа.';
        }

        $message.text(text).toggle(Boolean(text));
    }

    function refreshConditionalFields() {
        var service = selectedDeliveryService();
        var managerDelivery = service === 'cdek' || service === 'yandex';
        var mode = $('input[name="billing_delivery_mode"]:checked').val() || '';
        var pickup = managerDelivery && mode === 'pvz';
        var courier = managerDelivery && mode === 'courier';

        if (previousService === 'fivepost' && managerDelivery) {
            clearFivePostPoint();
            $('#billing_address_1, #billing_address_2, #billing_postcode').val('');
        } else if (previousService && previousService !== 'fivepost' && service === 'fivepost') {
            clearManagerDeliveryFields();
            mode = '';
            pickup = false;
            courier = false;
        }

        toggleField('.ll-manager-delivery-field', managerDelivery);
        toggleField('.ll-delivery-mode-field', managerDelivery);
        toggleField('.ll-pickup-address-field', pickup);
        toggleField('.ll-courier-address-field', courier);

        setFieldRequired('#billing_delivery_mode_field', managerDelivery);
        setFieldRequired('#billing_pickup_address_field', pickup);
        setFieldRequired('#billing_address_1_field', courier);
        setFieldRequired('#billing_address_2_field', false);
        setFieldRequired('#billing_postcode_field', courier);

        updateTariffMessage(service);
        previousService = service;
    }

    function rememberOrRestoreConsent() {
        var $consent = $('#ll_privacy_consent');

        if (!$consent.length) {
            return;
        }

        if (consentChecked === null) {
            consentChecked = $consent.is(':checked');
        } else {
            $consent.prop('checked', consentChecked);
        }
    }

    function setSubmittingState(submitting) {
        var $button = $('#place_order');

        checkoutSubmitting = submitting;

        if (!$button.length) {
            return;
        }

        if (!$button.data('ll-original-text')) {
            $button.data('ll-original-text', $button.text());
        }

        $button
            .prop('disabled', submitting)
            .attr('aria-disabled', submitting ? 'true' : 'false')
            .toggleClass('ll-order-submitting', submitting)
            .text(submitting ? 'Отправляем заказ…' : $button.data('ll-original-text'));
    }

    function refreshCheckoutInterface() {
        moveDeliveryPanel();
        mountShippingMethods();
        rememberOrRestoreConsent();
        refreshConditionalFields();

        if (checkoutSubmitting) {
            setSubmittingState(true);
        }
    }

    function requestCheckoutUpdate() {
        window.clearTimeout(addressUpdateTimer);
        addressUpdateTimer = window.setTimeout(function () {
            $('body').trigger('update_checkout');
        }, 450);
    }

    $(function () {
        var $checkout = $('form.checkout');

        if (!$checkout.length) {
            return;
        }

        refreshCheckoutInterface();

        $(document.body)
            .on('updated_checkout.llCheckoutWorkflow', refreshCheckoutInterface)
            .on('checkout_error.llCheckoutWorkflow', function () {
                setSubmittingState(false);
                rememberOrRestoreConsent();
                refreshConditionalFields();
            })
            .on('change.llCheckoutWorkflow', 'input[name^="shipping_method"]', function () {
                refreshConditionalFields();
            })
            .on('change.llCheckoutWorkflow', 'input[name="billing_delivery_mode"]', function () {
                refreshConditionalFields();
            })
            .on('change.llCheckoutWorkflow', '#ll_privacy_consent', function () {
                consentChecked = $(this).is(':checked');
            })
            .on('input.llCheckoutWorkflow change.llCheckoutWorkflow', '#billing_state, #billing_city', function () {
                updateTariffMessage(selectedDeliveryService());
                requestCheckoutUpdate();
            });

        $checkout.on('checkout_place_order.llCheckoutWorkflow', function () {
            if (checkoutSubmitting) {
                return false;
            }

            consentChecked = $('#ll_privacy_consent').is(':checked');
            setSubmittingState(true);
            return true;
        });
    });
})(jQuery);
