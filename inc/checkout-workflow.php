<?php
/**
 * LoraLeya checkout workflow.
 *
 * The checkout creates an unpaid order for manager confirmation. Online
 * payment becomes available only after the manager changes the order status
 * to "pending payment".
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
    return;
}

// Disable the temporary rules that were added during the first delivery test.
remove_filter( 'woocommerce_package_rates', 'loraleya_fixed_fivepost_shipping_rate', 100 );
remove_action( 'woocommerce_review_order_after_shipping', 'loraleya_checkout_shipping_rules' );
remove_action( 'wp_footer', 'loraleya_privacy_consent_links_script' );

/**
 * Return a safely normalized POST value.
 */
function loraleya_checkout_post_value( $key, $default = '' ) {
    $value = null;

    if ( isset( $_POST[ $key ] ) ) {
        $value = wp_unslash( $_POST[ $key ] );
    } elseif ( isset( $_POST['post_data'] ) && is_string( $_POST['post_data'] ) ) {
        static $checkout_data = null;

        if ( null === $checkout_data ) {
            $checkout_data = array();
            parse_str( wp_unslash( $_POST['post_data'] ), $checkout_data );
        }

        if ( array_key_exists( $key, $checkout_data ) ) {
            $value = $checkout_data[ $key ];
        }
    }

    if ( null === $value || is_array( $value ) ) {
        return $default;
    }

    return sanitize_text_field( $value );
}

/**
 * Return the selected WooCommerce shipping rate ID.
 */
function loraleya_checkout_selected_shipping_rate() {
    if ( isset( $_POST['shipping_method'] ) && is_array( $_POST['shipping_method'] ) ) {
        $methods = wc_clean( wp_unslash( $_POST['shipping_method'] ) );
        if ( ! empty( $methods[0] ) ) {
            return sanitize_text_field( $methods[0] );
        }
    }

    if ( function_exists( 'WC' ) && WC()->session ) {
        $methods = WC()->session->get( 'chosen_shipping_methods', array() );
        if ( ! empty( $methods[0] ) ) {
            return sanitize_text_field( $methods[0] );
        }
    }

    return '';
}

/**
 * Convert a rate ID to the store delivery service key.
 */
function loraleya_checkout_delivery_service( $rate_id = '' ) {
    $rate_id = $rate_id ? $rate_id : loraleya_checkout_selected_shipping_rate();

    if ( 0 === strpos( $rate_id, 'fivepost_shipping_method' ) ) {
        return 'fivepost';
    }
    if ( 0 === strpos( $rate_id, 'll_cdek' ) ) {
        return 'cdek';
    }
    if ( 0 === strpos( $rate_id, 'll_yandex' ) ) {
        return 'yandex';
    }

    return '';
}

/**
 * Human-readable delivery service name.
 */
function loraleya_checkout_delivery_service_label( $service ) {
    $labels = array(
        'fivepost' => '5Post',
        'cdek'     => 'СДЭК',
        'yandex'   => 'Яндекс Доставка',
    );

    return isset( $labels[ $service ] ) ? $labels[ $service ] : '';
}

/**
 * Only an explicit Moscow or Moscow Oblast region gets the free 5Post rate.
 * City and street are deliberately ignored so an unrelated address cannot
 * accidentally match the word "Moscow" in another field.
 */
function loraleya_checkout_is_moscow_region( $destination ) {
    $state = isset( $destination['state'] ) ? (string) $destination['state'] : '';
    $state = function_exists( 'mb_strtolower' )
        ? mb_strtolower( $state, 'UTF-8' )
        : strtolower( $state );
    $state = str_replace( 'ё', 'е', $state );
    $state = preg_replace( '/[^a-zа-я0-9]+/u', ' ', $state );
    $state = trim( preg_replace( '/\s+/u', ' ', $state ) );

    $moscow_values = array(
        'mow',
        'mos',
        'москва',
        'г москва',
        'город москва',
        'moscow',
        'moskva',
        'московская область',
        'московская обл',
        'московская обл р-н',
        'мо',
        'moscow oblast',
        'moskovskaya oblast',
    );

    if ( in_array( $state, $moscow_values, true ) ) {
        return true;
    }

    // Saved customer profiles sometimes contain a district after the region.
    // Accept that suffix without falling back to a broad substring match.
    return 1 === preg_match( '/^московская (?:область|обл)(?:\s|$)/u', $state );
}

/**
 * Keep 5Post as a pickup-point selector and add manager-calculated CDEK and
 * Yandex rates. The 5Post price is controlled by the store, not by the plugin.
 */
function loraleya_checkout_delivery_rates( $rates, $package ) {
    if ( ! class_exists( 'WC_Shipping_Rate' ) ) {
        return $rates;
    }

    $destination = isset( $package['destination'] ) && is_array( $package['destination'] )
        ? $package['destination']
        : array();

    // During update_order_review WooCommerce sends current checkout fields in
    // the serialized post_data value. It can still expose an older destination
    // in the package, so the region entered on the current screen wins.
    $posted_state = loraleya_checkout_post_value( 'billing_state' );
    if ( '' !== $posted_state ) {
        $destination['state'] = $posted_state;
    }

    $is_moscow  = loraleya_checkout_is_moscow_region( $destination );
    $five_cost  = $is_moscow ? 0 : 250;
    $five_rate  = null;

    foreach ( (array) $rates as $rate ) {
        if ( $rate instanceof WC_Shipping_Rate && 'fivepost_shipping_method' === $rate->get_method_id() ) {
            $five_rate = $rate;
            break;
        }
    }

    // The fallback keeps the 5Post option visible while its plugin refreshes
    // pickup points for a newly entered city.
    if ( ! $five_rate ) {
        $five_rate = new WC_Shipping_Rate(
            'fivepost_shipping_method:pickup',
            '5Post',
            $five_cost,
            array(),
            'fivepost_shipping_method'
        );
    }

    $five_rate->set_label( $is_moscow ? '5Post — бесплатно' : '5Post' );
    $five_rate->set_cost( $five_cost );
    $five_rate->set_taxes( array() );

    return array(
        $five_rate->get_id() => $five_rate,
        'll_cdek:manager'    => new WC_Shipping_Rate(
            'll_cdek:manager',
            'СДЭК — стоимость рассчитает менеджер',
            0,
            array(),
            'll_cdek'
        ),
        'll_yandex:manager'  => new WC_Shipping_Rate(
            'll_yandex:manager',
            'Яндекс Доставка — стоимость рассчитает менеджер',
            0,
            array(),
            'll_yandex'
        ),
    );
}
add_filter( 'woocommerce_package_rates', 'loraleya_checkout_delivery_rates', 120, 2 );

/**
 * Reorder and relabel the classic checkout fields.
 */
function loraleya_checkout_fields( $fields ) {
    if ( empty( $fields['billing'] ) || ! is_array( $fields['billing'] ) ) {
        return $fields;
    }

    $billing = &$fields['billing'];

    if ( isset( $billing['billing_last_name'] ) ) {
        $billing['billing_last_name']['priority'] = 10;
        $billing['billing_last_name']['required'] = true;
    }
    if ( isset( $billing['billing_first_name'] ) ) {
        $billing['billing_first_name']['priority'] = 20;
        $billing['billing_first_name']['required'] = true;
    }
    if ( isset( $billing['billing_email'] ) ) {
        $billing['billing_email']['priority'] = 30;
        $billing['billing_email']['required'] = true;
    }
    if ( isset( $billing['billing_phone'] ) ) {
        $billing['billing_phone']['priority'] = 40;
        $billing['billing_phone']['required'] = true;
        $billing['billing_phone']['label']    = 'Телефон';
    }
    if ( isset( $billing['billing_country'] ) ) {
        $billing['billing_country']['type']     = 'hidden';
        $billing['billing_country']['default']  = 'RU';
        $billing['billing_country']['priority'] = 50;
    }
    if ( isset( $billing['billing_state'] ) ) {
        $billing['billing_state']['priority']    = 60;
        $billing['billing_state']['required']    = true;
        $billing['billing_state']['label']       = 'Регион';
        $billing['billing_state']['placeholder'] = 'Например, Московская область';
        $billing['billing_state']['class']       = array( 'form-row-wide', 'll-delivery-address-field' );
    }
    if ( isset( $billing['billing_city'] ) ) {
        $billing['billing_city']['priority']    = 70;
        $billing['billing_city']['required']    = true;
        $billing['billing_city']['label']       = 'Город или населённый пункт';
        $billing['billing_city']['placeholder'] = 'Например, Раменское';
        $billing['billing_city']['class']       = array( 'form-row-wide', 'll-delivery-address-field' );
    }

    $billing['billing_delivery_mode'] = array(
        'type'     => 'radio',
        'label'    => 'Вариант получения',
        'required' => false,
        'priority' => 80,
        'class'    => array( 'form-row-wide', 'll-manager-delivery-field', 'll-delivery-mode-field' ),
        'options'  => array(
            'pvz'     => 'До пункта выдачи (ПВЗ)',
            'courier' => 'Курьером до адреса',
        ),
    );

    $billing['billing_pickup_address'] = array(
        'type'        => 'text',
        'label'       => 'Адрес или номер выбранного ПВЗ',
        'placeholder' => 'Укажите удобный пункт выдачи',
        'required'    => false,
        'priority'    => 90,
        'class'       => array( 'form-row-wide', 'll-manager-delivery-field', 'll-pickup-address-field' ),
    );

    if ( isset( $billing['billing_address_1'] ) ) {
        $billing['billing_address_1']['priority']    = 100;
        $billing['billing_address_1']['required']    = false;
        $billing['billing_address_1']['label']       = 'Адрес доставки';
        $billing['billing_address_1']['placeholder'] = 'Улица, дом, корпус';
        $billing['billing_address_1']['class']       = array( 'form-row-wide', 'll-courier-address-field' );
    }
    if ( isset( $billing['billing_address_2'] ) ) {
        $billing['billing_address_2']['priority']    = 110;
        $billing['billing_address_2']['required']    = false;
        $billing['billing_address_2']['label']       = 'Квартира или офис (необязательно)';
        $billing['billing_address_2']['placeholder'] = 'Квартира, офис, подъезд, этаж';
        $billing['billing_address_2']['class']       = array( 'form-row-wide', 'll-courier-address-field' );
    }
    if ( isset( $billing['billing_postcode'] ) ) {
        $billing['billing_postcode']['priority']    = 120;
        $billing['billing_postcode']['required']    = false;
        $billing['billing_postcode']['label']       = 'Почтовый индекс';
        $billing['billing_postcode']['placeholder'] = 'Индекс места доставки';
        $billing['billing_postcode']['class']       = array( 'form-row-wide', 'll-courier-address-field' );
    }

    // One destination is used throughout this workflow, so the duplicate
    // "ship to a different address" section is intentionally removed.
    $fields['shipping'] = array();

    return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'loraleya_checkout_fields', 30 );

/**
 * The workflow uses one billing/delivery destination. Suppress WooCommerce's
 * duplicate "ship to a different address" section and keep billing fields as
 * the source for shipping calculations.
 */
function loraleya_checkout_needs_separate_shipping_address( $needs_address ) {
    if ( function_exists( 'is_checkout' ) && is_checkout() ) {
        return false;
    }

    return $needs_address;
}
add_filter( 'woocommerce_cart_needs_shipping_address', 'loraleya_checkout_needs_separate_shipping_address', 30 );

function loraleya_checkout_separate_shipping_checked( $checked ) {
    if ( function_exists( 'is_checkout' ) && is_checkout() ) {
        return false;
    }

    return $checked;
}
add_filter( 'woocommerce_ship_to_different_address_checked', 'loraleya_checkout_separate_shipping_checked', 30 );

/**
 * Mount point for moving the real WooCommerce shipping controls next to the
 * delivery address fields. JavaScript performs the move after each AJAX refresh.
 */
function loraleya_checkout_delivery_panel() {
    echo '<section class="ll-delivery-panel" aria-labelledby="ll-delivery-title">';
    echo '<h3 id="ll-delivery-title">Способ доставки</h3>';
    echo '<div id="ll-delivery-methods-host">';
    echo '<p class="ll-delivery-loading">Выберите регион и город — доступные способы появятся здесь.</p>';
    echo '</div>';
    echo '<p class="ll-delivery-tariff" aria-live="polite"></p>';
    echo '</section>';
}
add_action( 'woocommerce_after_checkout_billing_form', 'loraleya_checkout_delivery_panel', 5 );

/**
 * Required consent for the actual classic checkout used by this site.
 */
function loraleya_checkout_privacy_consent() {
    $privacy_url = home_url( '/privacy-policy/' );
    $offer_url   = home_url( '/oferta/' );
    ?>
    <p class="form-row validate-required ll-checkout-consent" id="ll_privacy_consent_field">
        <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
            <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="ll_privacy_consent" id="ll_privacy_consent" value="1" />
            <span>Я согласен(на) с <a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener">Политикой обработки персональных данных</a> и <a href="<?php echo esc_url( $offer_url ); ?>" target="_blank" rel="noopener">Условиями оферты</a></span>
            <span class="required" aria-hidden="true">*</span>
        </label>
    </p>
    <?php
}
add_action( 'woocommerce_review_order_before_submit', 'loraleya_checkout_privacy_consent', 8 );

/**
 * Validate fields according to the selected delivery service.
 */
function loraleya_checkout_validate_delivery() {
    $rate_id = loraleya_checkout_selected_shipping_rate();
    $service = loraleya_checkout_delivery_service( $rate_id );
    $mode    = loraleya_checkout_post_value( 'billing_delivery_mode' );

    if ( ! $service ) {
        wc_add_notice( 'Выберите способ доставки.', 'error' );
        return;
    }

    if ( ! loraleya_checkout_post_value( 'll_privacy_consent' ) ) {
        wc_add_notice( 'Для оформления заказа необходимо согласиться с Политикой обработки персональных данных и Условиями оферты.', 'error' );
    }

    if ( 'fivepost' === $service ) {
        if ( ! loraleya_checkout_post_value( 'fivepost_point_id' ) ) {
            wc_add_notice( 'Выберите пункт выдачи 5Post на карте.', 'error' );
        }
        return;
    }

    if ( ! in_array( $mode, array( 'pvz', 'courier' ), true ) ) {
        wc_add_notice( 'Выберите доставку до пункта выдачи или курьером до адреса.', 'error' );
        return;
    }

    if ( 'pvz' === $mode && ! loraleya_checkout_post_value( 'billing_pickup_address' ) ) {
        wc_add_notice( 'Укажите адрес или номер желаемого пункта выдачи.', 'error' );
    }

    if ( 'courier' === $mode ) {
        if ( ! loraleya_checkout_post_value( 'billing_address_1' ) ) {
            wc_add_notice( 'Укажите улицу и дом для курьерской доставки.', 'error' );
        }
        if ( ! loraleya_checkout_post_value( 'billing_postcode' ) ) {
            wc_add_notice( 'Укажите почтовый индекс места доставки.', 'error' );
        }
    }
}
add_action( 'woocommerce_checkout_process', 'loraleya_checkout_validate_delivery' );

/**
 * Store manager-facing delivery information on the order.
 */
function loraleya_checkout_create_order( $order, $data ) {
    $rate_id = loraleya_checkout_selected_shipping_rate();
    $service = loraleya_checkout_delivery_service( $rate_id );
    $mode    = loraleya_checkout_post_value( 'billing_delivery_mode' );

    $order->update_meta_data( '_ll_manager_confirmation_required', 'yes' );
    $order->update_meta_data( '_ll_delivery_service', $service );
    $order->update_meta_data( '_ll_delivery_rate_id', $rate_id );
    $order->update_meta_data( '_ll_delivery_mode', $mode );
    $order->update_meta_data( '_ll_pickup_address', loraleya_checkout_post_value( 'billing_pickup_address' ) );
    $order->update_meta_data( '_ll_privacy_consent', 'yes' );
    $order->update_meta_data( '_ll_privacy_consent_time', current_time( 'mysql' ) );

    if ( 'fivepost' === $service ) {
        $order->update_meta_data( '_ll_fivepost_point_id', loraleya_checkout_post_value( 'fivepost_point_id' ) );
        $order->update_meta_data( '_ll_fivepost_point_zone', loraleya_checkout_post_value( 'fivepost_point_zone' ) );
        $destination = array( 'state' => isset( $data['billing_state'] ) ? $data['billing_state'] : '' );
        $order->update_meta_data(
            '_ll_preliminary_shipping_cost',
            loraleya_checkout_is_moscow_region( $destination ) ? '0' : '250'
        );
    } else {
        $order->update_meta_data( '_ll_preliminary_shipping_cost', 'manager' );
    }
}
add_action( 'woocommerce_checkout_create_order', 'loraleya_checkout_create_order', 20, 2 );

/**
 * Offline gateway used only for the initial order request.
 */
if ( class_exists( 'WC_Payment_Gateway' ) && ! class_exists( 'Loraleya_Manager_Confirmation_Gateway' ) ) {
    class Loraleya_Manager_Confirmation_Gateway extends WC_Payment_Gateway {
        public function __construct() {
            $this->id                 = 'll_manager_confirmation';
            $this->method_title       = 'Подтверждение заказа менеджером';
            $this->method_description = 'Создаёт заказ без оплаты. Менеджер проверяет наличие, сроки и доставку, после чего покупателю становится доступна оплата.';
            $this->title              = 'Подтверждение заказа менеджером';
            $this->description        = 'Оплата сейчас не требуется. Менеджер проверит заказ и после согласования отправит ссылку на оплату.';
            $this->has_fields         = false;
            $this->enabled            = 'yes';
            $this->supports           = array( 'products' );
        }

        public function is_available() {
            if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-pay' ) ) {
                return false;
            }

            return parent::is_available();
        }

        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );
            if ( ! $order ) {
                wc_add_notice( 'Не удалось создать заказ. Обновите страницу и попробуйте ещё раз.', 'error' );
                return array( 'result' => 'failure' );
            }

            $order->update_status(
                'on-hold',
                'Заказ принят без оплаты и ожидает проверки менеджером.'
            );

            // Do not reserve or write off inventory until the order has been
            // checked and subsequently paid.
            if ( function_exists( 'wc_release_stock_for_order' ) ) {
                wc_release_stock_for_order( $order );
            }

            if ( function_exists( 'WC' ) && WC()->cart ) {
                WC()->cart->empty_cart();
            }

            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order ),
            );
        }
    }
}

function loraleya_checkout_register_manager_gateway( $gateways ) {
    $gateways[] = 'Loraleya_Manager_Confirmation_Gateway';
    return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'loraleya_checkout_register_manager_gateway' );

/**
 * Initial checkout: only manager confirmation. Order-pay endpoint: only real
 * online gateways, so the customer can pay after manager approval.
 */
function loraleya_checkout_available_gateways( $gateways ) {
    if ( is_admin() && ! wp_doing_ajax() ) {
        return $gateways;
    }

    if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-pay' ) ) {
        unset( $gateways['ll_manager_confirmation'] );
        return $gateways;
    }

    if ( ( function_exists( 'is_checkout' ) && is_checkout() ) || wp_doing_ajax() ) {
        return isset( $gateways['ll_manager_confirmation'] )
            ? array( 'll_manager_confirmation' => $gateways['ll_manager_confirmation'] )
            : $gateways;
    }

    return $gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'loraleya_checkout_available_gateways', 100 );

/**
 * Prevent the on-hold transition from reducing inventory for these orders.
 * A later successful online payment is allowed to reduce stock normally.
 */
function loraleya_checkout_prevent_early_stock_reduction( $can_reduce, $order ) {
    if (
        $order instanceof WC_Order
        && $order->has_status( 'on-hold' )
        && 'll_manager_confirmation' === $order->get_payment_method()
    ) {
        return false;
    }

    return $can_reduce;
}
add_filter( 'woocommerce_can_reduce_order_stock', 'loraleya_checkout_prevent_early_stock_reduction', 20, 2 );

/**
 * Customer-facing status names.
 */
function loraleya_checkout_order_status_names( $statuses ) {
    if ( isset( $statuses['wc-on-hold'] ) ) {
        $statuses['wc-on-hold'] = 'Ожидает подтверждения менеджером';
    }
    if ( isset( $statuses['wc-pending'] ) ) {
        $statuses['wc-pending'] = 'Ожидает оплаты';
    }

    return $statuses;
}
add_filter( 'wc_order_statuses', 'loraleya_checkout_order_status_names', 30 );

/**
 * Send the standard WooCommerce invoice/payment email as soon as the manager
 * changes a confirmed order from on-hold to pending payment.
 */
function loraleya_checkout_send_payment_email( $order_id, $order = null ) {
    $order = $order instanceof WC_Order ? $order : wc_get_order( $order_id );
    if ( ! $order || 'yes' !== $order->get_meta( '_ll_manager_confirmation_required' ) ) {
        return;
    }

    $emails = WC()->mailer()->get_emails();
    if ( isset( $emails['WC_Email_Customer_Invoice'] ) ) {
        $emails['WC_Email_Customer_Invoice']->trigger( $order_id, $order );
    }
}
add_action( 'woocommerce_order_status_on-hold_to_pending', 'loraleya_checkout_send_payment_email', 20, 2 );

/**
 * Delivery summary for manager emails, customer emails and the admin order.
 */
function loraleya_checkout_delivery_summary_rows( $order ) {
    if ( ! $order instanceof WC_Order ) {
        return array();
    }

    $service = $order->get_meta( '_ll_delivery_service' );
    if ( ! $service ) {
        return array();
    }

    $rows = array(
        'Способ доставки' => loraleya_checkout_delivery_service_label( $service ),
        'Регион'          => $order->get_billing_state(),
        'Город'           => $order->get_billing_city(),
    );

    if ( 'fivepost' === $service ) {
        $rows['Пункт 5Post'] = $order->get_billing_address_1();
        $rows['Код пункта']  = $order->get_meta( '_ll_fivepost_point_id' );
        $cost                = $order->get_meta( '_ll_preliminary_shipping_cost' );
        $rows['Доставка']    = '0' === (string) $cost ? 'Бесплатно' : '250 ₽';
    } else {
        $mode = $order->get_meta( '_ll_delivery_mode' );
        if ( 'pvz' === $mode ) {
            $rows['Получение'] = 'До пункта выдачи (ПВЗ)';
            $rows['ПВЗ']       = $order->get_meta( '_ll_pickup_address' );
        } else {
            $rows['Получение'] = 'Курьером до адреса';
            $rows['Адрес']     = $order->get_formatted_billing_address();
        }
        $rows['Доставка'] = 'Стоимость рассчитывает менеджер';
    }

    return array_filter( $rows, static function ( $value ) {
        return '' !== trim( wp_strip_all_tags( (string) $value ) );
    } );
}

function loraleya_checkout_admin_delivery_summary( $order ) {
    $rows = loraleya_checkout_delivery_summary_rows( $order );
    if ( ! $rows ) {
        return;
    }

    echo '<div class="ll-admin-delivery-summary"><h3>Доставка и подтверждение</h3><p><strong>Требуется связаться с покупателем до оплаты.</strong></p><dl>';
    foreach ( $rows as $label => $value ) {
        echo '<dt><strong>' . esc_html( $label ) . ':</strong></dt><dd>' . wp_kses_post( $value ) . '</dd>';
    }
    echo '</dl></div>';
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'loraleya_checkout_admin_delivery_summary', 20 );

function loraleya_checkout_email_delivery_summary( $order, $sent_to_admin, $plain_text, $email ) {
    $rows = loraleya_checkout_delivery_summary_rows( $order );
    if ( ! $rows ) {
        return;
    }

    if ( $plain_text ) {
        echo "\nДОСТАВКА И ПОДТВЕРЖДЕНИЕ\n";
        echo $sent_to_admin
            ? "Необходимо связаться с покупателем до оплаты.\n"
            : "Менеджер свяжется с вами для подтверждения заказа.\n";
        foreach ( $rows as $label => $value ) {
            echo wp_strip_all_tags( $label . ': ' . $value ) . "\n";
        }
        return;
    }

    echo '<h2>Доставка и подтверждение</h2>';
    echo $sent_to_admin
        ? '<p><strong>Необходимо связаться с покупателем до оплаты.</strong></p>'
        : '<p>Менеджер свяжется с вами для подтверждения наличия, количества, сроков и доставки.</p>';
    echo '<table cellspacing="0" cellpadding="6" style="width:100%;border:1px solid #e5e5e5" border="1">';
    foreach ( $rows as $label => $value ) {
        echo '<tr><th style="text-align:left">' . esc_html( $label ) . '</th><td>' . wp_kses_post( $value ) . '</td></tr>';
    }
    echo '</table>';
}
add_action( 'woocommerce_email_after_order_table', 'loraleya_checkout_email_delivery_summary', 20, 4 );

/**
 * Checkout and email wording.
 */
add_filter( 'woocommerce_order_button_text', static function () {
    return 'Оформить заказ';
} );

add_filter( 'woocommerce_thankyou_order_received_text', static function ( $text, $order ) {
    if ( $order instanceof WC_Order && 'yes' === $order->get_meta( '_ll_manager_confirmation_required' ) ) {
        return 'Заказ принят. Оплата пока не требуется. Менеджер свяжется с вами для подтверждения наличия, количества, сроков и доставки. После согласования вы получите ссылку на оплату, а зарегистрированным покупателям станет доступна кнопка оплаты в личном кабинете.';
    }
    return $text;
}, 20, 2 );

add_filter( 'woocommerce_email_subject_new_order', static function ( $subject, $order ) {
    if ( $order instanceof WC_Order && 'yes' === $order->get_meta( '_ll_manager_confirmation_required' ) ) {
        return '[Требуется подтверждение] Заказ №' . $order->get_order_number();
    }
    return $subject;
}, 20, 2 );

add_filter( 'woocommerce_email_subject_customer_on_hold_order', static function ( $subject, $order ) {
    if ( $order instanceof WC_Order && 'yes' === $order->get_meta( '_ll_manager_confirmation_required' ) ) {
        return 'Заказ №' . $order->get_order_number() . ' принят — менеджер свяжется с вами';
    }
    return $subject;
}, 20, 2 );

add_filter( 'woocommerce_email_heading_customer_on_hold_order', static function ( $heading, $order ) {
    if ( $order instanceof WC_Order && 'yes' === $order->get_meta( '_ll_manager_confirmation_required' ) ) {
        return 'Ваш заказ принят';
    }
    return $heading;
}, 20, 2 );

add_filter( 'woocommerce_email_subject_customer_invoice', static function ( $subject, $order ) {
    if ( $order instanceof WC_Order && 'yes' === $order->get_meta( '_ll_manager_confirmation_required' ) ) {
        return 'Заказ №' . $order->get_order_number() . ' подтверждён и готов к оплате';
    }
    return $subject;
}, 20, 2 );

add_filter( 'woocommerce_email_heading_customer_invoice', static function ( $heading, $order ) {
    if ( $order instanceof WC_Order && 'yes' === $order->get_meta( '_ll_manager_confirmation_required' ) ) {
        return 'Заказ подтверждён — можно оплатить';
    }
    return $heading;
}, 20, 2 );

/**
 * Load the conditional checkout interface only when its file exists.
 */
function loraleya_checkout_workflow_script() {
    if (
        ! function_exists( 'is_checkout' )
        || ! is_checkout()
        || ( function_exists( 'is_wc_endpoint_url' ) && ( is_wc_endpoint_url( 'order-pay' ) || is_wc_endpoint_url( 'order-received' ) ) )
    ) {
        return;
    }

    $path = get_stylesheet_directory() . '/assets/js/checkout-workflow.js';
    if ( ! file_exists( $path ) ) {
        return;
    }

    wp_enqueue_script(
        'loraleya-checkout-workflow',
        get_stylesheet_directory_uri() . '/assets/js/checkout-workflow.js',
        array( 'jquery', 'wc-checkout' ),
        filemtime( $path ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'loraleya_checkout_workflow_script', 30 );
