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
                .html('<p class="ll-delivery-loading">Загружаем способы доставки…</p>');
        }
    }

    function mountFivePostMapAction() {
        var $cityField = $('#billing_city_field');
        var $action = $('#ll-fivepost-map-action');
        var $mapButton = $('[data-post5_popup="post5-map-popup"]').first();

        if (!$cityField.length) {
            return;
        }

        if (!$action.length) {
            $action = $('<div id="ll-fivepost-map-action" class="ll-fivepost-map-action"></div>');
            $action.insertAfter($cityField);
        }

        if ($mapButton.length && !$mapButton.parent().is($action)) {
            $action.empty().append($mapButton);
        }

        $action.find('[data-post5_popup="post5-map-popup"]')
            .addClass('ll-fivepost-map-button')
            .text('Выбрать ПВЗ на карте');
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

    function setFieldLabel(fieldSelector, label) {
        var $label = $(fieldSelector).find('label').first();

        if (!$label.length) {
            return;
        }

        $label.text(label);
    }

    function configureAddressField(service, pointSelected) {
        var fivepost = service === 'fivepost';
        var $field = $('#billing_address_1_field');
        var $input = $('#billing_address_1');

        setFieldLabel(
            '#billing_address_1_field',
            fivepost ? 'Адрес выбранного ПВЗ' : 'Адрес доставки'
        );

        $input
            .prop('readonly', fivepost)
            .attr(
                'placeholder',
                fivepost ? 'После выбора ПВЗ адрес появится здесь' : 'Улица, дом, корпус'
            );

        $field.toggleClass('ll-fivepost-point-selected', fivepost && pointSelected);
    }

    function clearFivePostPoint() {
        $('input[name="fivepost_point_id"], input[name="fivepost_point_zone"]').val('');
    }

    function clearManagerDeliveryFields() {
        $('input[name="billing_delivery_mode"]').prop('checked', false);
        $('#billing_pickup_address, #billing_address_1, #billing_address_2, #billing_postcode').val('');
    }

    function updateTariffMessage(service) {
        var $message = $('.ll-delivery-tariff').first();
        var text = '';

        if (!$message.length) {
            return;
        }

        if (service === 'cdek') {
            text = 'СДЭК: стоимость и срок доставки рассчитает менеджер после проверки заказа.';
        } else if (service === 'yandex') {
            text = 'Яндекс Доставка: стоимость и срок доставки рассчитает менеджер после проверки заказа.';
        }

        $message.text(text).toggle(Boolean(text));
    }

    function refreshConditionalFields() {
        var service = selectedDeliveryService();
        var managerDelivery = service === 'cdek' || service === 'yandex';
        var deliveryLocation = service === 'fivepost' || managerDelivery;
        var mode = $('input[name="billing_delivery_mode"]:checked').val() || '';
        var pickup = managerDelivery && mode === 'pvz';
        var courier = managerDelivery && mode === 'courier';
        var fivepostPointSelected;

        if (previousService === 'fivepost' && managerDelivery) {
            clearFivePostPoint();
            $('#billing_address_1, #billing_address_2, #billing_postcode').val('');
        } else if (previousService && previousService !== 'fivepost' && service === 'fivepost') {
            clearManagerDeliveryFields();
            mode = '';
            pickup = false;
            courier = false;
        }

        fivepostPointSelected = service === 'fivepost'
            && Boolean($.trim($('#fivepost_point_id').val() || ''))
            && Boolean($.trim($('#billing_address_1').val() || ''));

        mountFivePostMapAction();

        toggleField('.ll-manager-delivery-field', managerDelivery);
        toggleField('.ll-delivery-mode-field', managerDelivery);
        toggleField('.ll-delivery-address-field', deliveryLocation);
        toggleField('#ll-fivepost-map-action', service === 'fivepost');
        toggleField('.ll-pickup-address-field', pickup);
        toggleField('#billing_address_1_field', courier || fivepostPointSelected);
        toggleField('#billing_address_2_field', courier);
        toggleField('#billing_postcode_field', courier);

        configureAddressField(service, fivepostPointSelected);

        setFieldRequired('#billing_delivery_mode_field', managerDelivery);
        setFieldRequired('#billing_state_field', deliveryLocation);
        setFieldRequired('#billing_city_field', deliveryLocation);
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

    function updateCheckoutSizeLabels() {
        var sizePrefixes = {
            'Размер скатерти': '140',
            'Размер дорожки': '40'
        };

        $('#order_review dl.variation dt').each(function () {
            var $name = $(this);
            var $valueContainer = $name.next('dd');
            var $value = $valueContainer.find('p').first();
            var label = $.trim($name.text()).replace(/:\s*$/, '');
            var prefix = sizePrefixes[label];
            var size;

            if (!$value.length) {
                $value = $valueContainer;
            }

            size = $.trim($value.text()).match(/^(\d+)\s*(?:см)?$/i);

            if (prefix && size) {
                $value.text(prefix + ' × ' + size[1] + ' см');
            }
        });
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
        updateCheckoutSizeLabels();
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
                if (selectedDeliveryService() === 'fivepost') {
                    clearFivePostPoint();
                    $('#billing_address_1').val('');
                    refreshConditionalFields();
                }

                updateTariffMessage(selectedDeliveryService());
                requestCheckoutUpdate();
            })
            .on('input.llCheckoutWorkflow change.llCheckoutWorkflow', '#fivepost_point_id, #billing_address_1', function () {
                refreshConditionalFields();
            })
            .on('click.llCheckoutWorkflow', '#post5-map-popup button', function () {
                window.setTimeout(refreshConditionalFields, 100);
                window.setTimeout(refreshConditionalFields, 500);
                window.setTimeout(refreshConditionalFields, 1000);
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
