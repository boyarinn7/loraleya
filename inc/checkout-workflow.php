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
 * Keep 5Post as a pickup-point selector and add manager-calculated CDEK and
 * Yandex rates. All initial rates are zero because checkout creates an unpaid
 * request. The 5Post terms are part of the method label and are not added to
 * the order total before manager confirmation.
 */
function loraleya_checkout_delivery_rates( $rates, $package ) {
    if ( ! class_exists( 'WC_Shipping_Rate' ) ) {
        return $rates;
    }

    $five_rate = null;

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
            '5Post - доставка по Москве и Московской области бесплатно, другие регионы 250 руб',
            0,
            array(),
            'fivepost_shipping_method'
        );
    }

    $five_rate->set_label( '5Post - доставка по Москве и Московской области бесплатно, другие регионы 250 руб' );
    $five_rate->set_cost( 0 );
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
        $billing['billing_state']['required']    = false;
        $billing['billing_state']['label']       = 'Регион';
        $billing['billing_state']['placeholder'] = 'Например, Московская область';
        $billing['billing_state']['class']       = array( 'form-row-wide', 'll-delivery-address-field' );
    }
    if ( isset( $billing['billing_city'] ) ) {
        $billing['billing_city']['priority']    = 70;
        $billing['billing_city']['required']    = false;
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
        $billing['billing_address_2']['label']       = 'Квартира или офис';
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

    if ( isset( $fields['order']['order_comments'] ) ) {
        $fields['order']['order_comments']['label'] = 'Примечание к заказу';
    }

    return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'loraleya_checkout_fields', 30 );

/**
 * WooCommerce appends an "optional" suffix to every non-required field.
 * Keep fields optional where intended, but remove that wording from checkout.
 */
function loraleya_checkout_remove_optional_suffix( $field ) {
    if ( function_exists( 'is_checkout' ) && is_checkout() ) {
        $field = preg_replace( '/\s*<span class="optional">.*?<\/span>/u', '', $field );
    }

    return $field;
}
add_filter( 'woocommerce_form_field', 'loraleya_checkout_remove_optional_suffix', 30, 1 );

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
    echo '<p class="ll-delivery-loading">Загружаем способы доставки…</p>';
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
        if ( ! loraleya_checkout_post_value( 'billing_state' ) ) {
            wc_add_notice( 'Укажите регион доставки.', 'error' );
        }

        if ( ! loraleya_checkout_post_value( 'billing_city' ) ) {
            wc_add_notice( 'Укажите город или населённый пункт.', 'error' );
        }

        if ( ! loraleya_checkout_post_value( 'fivepost_point_id' ) ) {
            wc_add_notice( 'Выберите пункт выдачи 5Post на карте.', 'error' );
        } elseif ( ! loraleya_checkout_post_value( 'billing_address_1' ) ) {
            wc_add_notice( 'Адрес пункта 5Post не сохранился. Выберите пункт на карте ещё раз.', 'error' );
        }
        return;
    }

    if ( ! loraleya_checkout_post_value( 'billing_state' ) ) {
        wc_add_notice( 'Укажите регион доставки.', 'error' );
    }

    if ( ! loraleya_checkout_post_value( 'billing_city' ) ) {
        wc_add_notice( 'Укажите город или населённый пункт.', 'error' );
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
        $order->update_meta_data( '_ll_preliminary_shipping_cost', 'informational' );
        $order->set_billing_address_2( '' );
        $order->set_billing_postcode( '' );
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
    );

    if ( 'fivepost' === $service ) {
        $rows['Пункт 5Post'] = $order->get_billing_address_1();
        $rows['Код пункта']  = $order->get_meta( '_ll_fivepost_point_id' );
        $rows['Условия 5Post'] = 'Доставка по Москве и Московской области бесплатно, другие регионы 250 руб';
    } else {
        $rows['Регион'] = $order->get_billing_state();
        $rows['Город']  = $order->get_billing_city();
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

    $service = $order instanceof WC_Order ? $order->get_meta( '_ll_delivery_service' ) : '';
    if (
        $order instanceof WC_Order
        && 'yes' === $order->get_meta( '_ll_individual_order' )
        && in_array( $service, array( 'cdek', 'yandex' ), true )
    ) {
        $rows['Доставка'] = wc_price( $order->get_shipping_total(), array( 'currency' => $order->get_currency() ) );
    }

    echo '<div class="ll-admin-delivery-summary"><h3>Доставка и подтверждение</h3><p><strong>Требуется связаться с покупателем до оплаты.</strong></p><dl>';
    foreach ( $rows as $label => $value ) {
        echo '<dt><strong>' . esc_html( $label ) . ':</strong></dt><dd>' . wp_kses_post( $value ) . '</dd>';
    }
    echo '</dl></div>';
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'loraleya_checkout_admin_delivery_summary', 20 );

function loraleya_checkout_admin_tracking_number( $order ) {
    if ( ! $order instanceof WC_Order || ! current_user_can( 'manage_woocommerce' ) ) {
        return;
    }

    wp_nonce_field( 'loraleya_checkout_save_tracking_number', 'loraleya_checkout_tracking_nonce' );
    woocommerce_wp_text_input(
        array(
            'id'            => '_ll_tracking_number',
            'label'         => 'Трек-номер отправления',
            'value'         => $order->get_meta( '_ll_tracking_number' ),
            'wrapper_class' => 'form-field-wide',
        )
    );
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'loraleya_checkout_admin_tracking_number', 25 );

function loraleya_checkout_save_tracking_number( $order_id, $order = null ) {
    if (
        ! current_user_can( 'manage_woocommerce' )
        || empty( $_POST['loraleya_checkout_tracking_nonce'] )
        || ! wp_verify_nonce(
            sanitize_text_field( wp_unslash( $_POST['loraleya_checkout_tracking_nonce'] ) ),
            'loraleya_checkout_save_tracking_number'
        )
    ) {
        return;
    }

    $order = $order instanceof WC_Order ? $order : wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }

    $tracking_number = isset( $_POST['_ll_tracking_number'] )
        ? sanitize_text_field( wp_unslash( $_POST['_ll_tracking_number'] ) )
        : '';

    if ( '' === $tracking_number ) {
        $order->delete_meta_data( '_ll_tracking_number' );
    } else {
        $order->update_meta_data( '_ll_tracking_number', $tracking_number );
    }

    $order->save();
}
add_action( 'woocommerce_process_shop_order_meta', 'loraleya_checkout_save_tracking_number', 20, 2 );

function loraleya_checkout_is_manager_invoice_email( $order, $email ) {
    return $order instanceof WC_Order
        && $email instanceof WC_Email_Customer_Invoice
        && 'yes' === $order->get_meta( '_ll_manager_confirmation_required' );
}

function loraleya_checkout_manager_invoice_intro_context( $active = null ) {
    static $is_active = false;

    if ( null !== $active ) {
        $is_active = (bool) $active;
    }

    return $is_active;
}

function loraleya_checkout_manager_invoice_intro_text( $translation, $text, $domain ) {
    if ( ! loraleya_checkout_manager_invoice_intro_context() || 'woocommerce' !== $domain ) {
        return $translation;
    }

    $invoice_texts = array(
        'An order has been created for you on %1$s. Your order details are below, with a link to make payment when you’re ready: %2$s',
        "An order has been created for you on %1\$s. Your order details are below, with a link to make payment when you're ready: %2\$s",
        "An order has been created for you on %s. The order details are as follows, with a link to make payment when you're ready: %s",
    );

    return in_array( $text, $invoice_texts, true )
        ? 'Ваш заказ согласован и готов к оплате. Для оплаты перейдите по ссылке: %2$s'
        : $translation;
}
add_filter( 'gettext', 'loraleya_checkout_manager_invoice_intro_text', 20, 3 );

function loraleya_checkout_manager_invoice_intro_end( $order, $sent_to_admin, $plain_text, $email ) {
    if ( loraleya_checkout_is_manager_invoice_email( $order, $email ) ) {
        loraleya_checkout_manager_invoice_intro_context( false );
    }
}
add_action( 'woocommerce_email_before_order_table', 'loraleya_checkout_manager_invoice_intro_end', 1, 4 );

function loraleya_checkout_is_manager_processing_email( $order, $email ) {
    return $order instanceof WC_Order
        && $email instanceof WC_Email
        && 'customer_processing_order' === $email->id
        && 'yes' === $order->get_meta( '_ll_manager_confirmation_required' )
        && 'yes' !== $order->get_meta( '_ll_individual_order' );
}

function loraleya_checkout_manager_processing_intro_context( $active = null ) {
    static $is_active = false;

    if ( null !== $active ) {
        $is_active = (bool) $active;
    }

    return $is_active;
}

function loraleya_checkout_manager_processing_intro_text( $translation, $text, $domain ) {
    if ( ! loraleya_checkout_manager_processing_intro_context() || 'woocommerce' !== $domain ) {
        return $translation;
    }

    $hidden_texts = array(
        'Just to let you know &mdash; we’ve received your order, and it is now being processed.',
        'Here’s a reminder of what you’ve ordered:',
        "Just to let you know &mdash; we've received your order #%s, and it is now being processed:",
    );

    return in_array( $text, $hidden_texts, true ) ? '' : $translation;
}
add_filter( 'gettext', 'loraleya_checkout_manager_processing_intro_text', 20, 3 );

function loraleya_checkout_manager_processing_content( $order, $sent_to_admin, $plain_text, $email ) {
    if ( ! loraleya_checkout_is_manager_processing_email( $order, $email ) ) {
        return;
    }

    if ( $plain_text ) {
        echo "Спасибо за заказ! Мы приступили к его подготовке.\n\n";
        echo "Если у вас появятся вопросы, свяжитесь с нами:\n\n";
        echo "Email: loraleya-tex@yandex.ru\n";
        echo "Телефон: +7 926 495 02 10\n";
        return;
    }

    echo '<p>Спасибо за заказ! Мы приступили к его подготовке.</p>';
    echo '<p>Если у вас появятся вопросы, свяжитесь с нами:</p>';
    echo '<p>Email: <a href="mailto:loraleya-tex@yandex.ru">loraleya-tex@yandex.ru</a><br>';
    echo 'Телефон: <a href="tel:+79264950210">+7 926 495 02 10</a></p>';
}

function loraleya_checkout_manager_processing_hooks( $activate ) {
    static $removed_hooks = array();
    static $is_active     = false;

    if ( $activate ) {
        if ( $is_active ) {
            return;
        }

        $mailer = WC()->mailer();
        $hooks  = array(
            array( 'woocommerce_email_order_details', array( $mailer, 'order_downloads' ), 4 ),
            array( 'woocommerce_email_order_details', array( $mailer, 'order_details' ), 4 ),
            array( 'woocommerce_email_order_details', array( 'WC_Structured_Data', 'generate_order_data' ), 3 ),
            array( 'woocommerce_email_order_details', array( 'WC_Structured_Data', 'output_structured_data' ), 3 ),
            array( 'woocommerce_email_order_meta', array( $mailer, 'order_meta' ), 3 ),
            array( 'woocommerce_email_customer_details', array( $mailer, 'customer_details' ), 3 ),
            array( 'woocommerce_email_customer_details', array( $mailer, 'email_addresses' ), 3 ),
            array( 'woocommerce_email_customer_details', array( $mailer, 'email_address' ), 3 ),
            array( 'woocommerce_email_customer_details', array( $mailer, 'additional_checkout_fields' ), 3 ),
        );

        foreach ( $hooks as $hook ) {
            $priority = has_action( $hook[0], $hook[1] );
            if ( false !== $priority ) {
                remove_action( $hook[0], $hook[1], $priority );
                $removed_hooks[] = array( $hook[0], $hook[1], $priority, $hook[2] );
            }
        }

        add_action( 'woocommerce_email_order_details', 'loraleya_checkout_manager_processing_content', 10, 4 );
        add_action( 'woocommerce_email_customer_details', 'loraleya_checkout_manager_processing_restore', PHP_INT_MAX, 4 );
        $is_active = true;
        return;
    }

    if ( ! $is_active ) {
        return;
    }

    remove_action( 'woocommerce_email_order_details', 'loraleya_checkout_manager_processing_content', 10 );
    remove_action( 'woocommerce_email_customer_details', 'loraleya_checkout_manager_processing_restore', PHP_INT_MAX );

    foreach ( $removed_hooks as $hook ) {
        add_action( $hook[0], $hook[1], $hook[2], $hook[3] );
    }

    $removed_hooks = array();
    $is_active     = false;
}

function loraleya_checkout_manager_processing_restore( $order, $sent_to_admin, $plain_text, $email ) {
    if ( loraleya_checkout_is_manager_processing_email( $order, $email ) ) {
        loraleya_checkout_manager_processing_hooks( false );
        loraleya_checkout_manager_processing_intro_context( false );
    }
}

function loraleya_checkout_is_manager_completed_email( $order, $email ) {
    return $order instanceof WC_Order
        && $email instanceof WC_Email
        && 'customer_completed_order' === $email->id
        && 'yes' === $order->get_meta( '_ll_manager_confirmation_required' );
}

function loraleya_checkout_manager_completed_intro_context( $active = null ) {
    static $is_active = false;

    if ( null !== $active ) {
        $is_active = (bool) $active;
    }

    return $is_active;
}

function loraleya_checkout_manager_completed_intro_text( $translation, $text, $domain ) {
    if ( ! loraleya_checkout_manager_completed_intro_context() || 'woocommerce' !== $domain ) {
        return $translation;
    }

    $hidden_texts = array(
        'We have finished processing your order.',
        'Here’s a reminder of what you’ve ordered:',
        "Here's a reminder of what you've ordered:",
    );

    return in_array( $text, $hidden_texts, true ) ? '' : $translation;
}
add_filter( 'gettext', 'loraleya_checkout_manager_completed_intro_text', 20, 3 );

function loraleya_checkout_manager_completed_content( $order, $sent_to_admin, $plain_text, $email ) {
    if ( ! loraleya_checkout_is_manager_completed_email( $order, $email ) ) {
        return;
    }

    $service_key     = (string) $order->get_meta( '_ll_delivery_service' );
    $service         = loraleya_checkout_delivery_service_label( $service_key );
    $tracking_number = trim( (string) $order->get_meta( '_ll_tracking_number' ) );
    $delivery_mode   = (string) $order->get_meta( '_ll_delivery_mode' );

    if ( $plain_text ) {
        echo "Ваш заказ передан в службу доставки и уже в пути.\n\n";
        if ( '' !== $service ) {
            echo 'Способ доставки: ' . wp_strip_all_tags( $service ) . "\n";
        }
        if ( '' !== $tracking_number ) {
            echo 'Трек-номер: ' . wp_strip_all_tags( $tracking_number ) . "\n";
        }
        if ( 'fivepost' === $service_key || 'pvz' === $delivery_mode ) {
            echo "\nКогда заказ поступит в пункт выдачи, служба доставки отправит вам уведомление о готовности к получению.\n";
        } elseif ( 'courier' === $delivery_mode ) {
            echo "\nКурьер свяжется с вами перед доставкой.\n";
        }
        echo "\nЕсли у вас появятся вопросы, свяжитесь с нами:\n\n";
        echo "Email: loraleya-tex@yandex.ru\n";
        echo "Телефон: +7 926 495 02 10\n";
        return;
    }

    echo '<p>Ваш заказ передан в службу доставки и уже в пути.</p>';
    if ( '' !== $service || '' !== $tracking_number ) {
        echo '<p>';
        if ( '' !== $service ) {
            echo 'Способ доставки: ' . esc_html( $service );
        }
        if ( '' !== $tracking_number ) {
            echo ( '' !== $service ? '<br>' : '' ) . 'Трек-номер: ' . esc_html( $tracking_number );
        }
        echo '</p>';
    }
    if ( 'fivepost' === $service_key || 'pvz' === $delivery_mode ) {
        echo '<p>Когда заказ поступит в пункт выдачи, служба доставки отправит вам уведомление о готовности к получению.</p>';
    } elseif ( 'courier' === $delivery_mode ) {
        echo '<p>Курьер свяжется с вами перед доставкой.</p>';
    }
    echo '<p>Если у вас появятся вопросы, свяжитесь с нами:</p>';
    echo '<p>Email: <a href="mailto:loraleya-tex@yandex.ru">loraleya-tex@yandex.ru</a><br>';
    echo 'Телефон: <a href="tel:+79264950210">+7 926 495 02 10</a></p>';
}

function loraleya_checkout_manager_completed_hooks( $activate ) {
    static $removed_hooks = array();
    static $is_active     = false;

    if ( $activate ) {
        if ( $is_active ) {
            return;
        }

        $mailer = WC()->mailer();
        $hooks  = array(
            array( 'woocommerce_email_order_details', array( $mailer, 'order_downloads' ), 4 ),
            array( 'woocommerce_email_order_details', array( $mailer, 'order_details' ), 4 ),
            array( 'woocommerce_email_order_details', array( 'WC_Structured_Data', 'generate_order_data' ), 3 ),
            array( 'woocommerce_email_order_details', array( 'WC_Structured_Data', 'output_structured_data' ), 3 ),
            array( 'woocommerce_email_order_meta', array( $mailer, 'order_meta' ), 3 ),
            array( 'woocommerce_email_customer_details', array( $mailer, 'customer_details' ), 3 ),
            array( 'woocommerce_email_customer_details', array( $mailer, 'email_addresses' ), 3 ),
            array( 'woocommerce_email_customer_details', array( $mailer, 'email_address' ), 3 ),
            array( 'woocommerce_email_customer_details', array( $mailer, 'additional_checkout_fields' ), 3 ),
        );

        foreach ( $hooks as $hook ) {
            $priority = has_action( $hook[0], $hook[1] );
            if ( false !== $priority ) {
                remove_action( $hook[0], $hook[1], $priority );
                $removed_hooks[] = array( $hook[0], $hook[1], $priority, $hook[2] );
            }
        }

        add_action( 'woocommerce_email_order_details', 'loraleya_checkout_manager_completed_content', 10, 4 );
        add_action( 'woocommerce_email_customer_details', 'loraleya_checkout_manager_completed_restore', PHP_INT_MAX, 4 );
        $is_active = true;
        return;
    }

    if ( ! $is_active ) {
        return;
    }

    remove_action( 'woocommerce_email_order_details', 'loraleya_checkout_manager_completed_content', 10 );
    remove_action( 'woocommerce_email_customer_details', 'loraleya_checkout_manager_completed_restore', PHP_INT_MAX );

    foreach ( $removed_hooks as $hook ) {
        add_action( $hook[0], $hook[1], $hook[2], $hook[3] );
    }

    $removed_hooks = array();
    $is_active     = false;
}

function loraleya_checkout_manager_completed_restore( $order, $sent_to_admin, $plain_text, $email ) {
    if ( loraleya_checkout_is_manager_completed_email( $order, $email ) ) {
        loraleya_checkout_manager_completed_hooks( false );
        loraleya_checkout_manager_completed_intro_context( false );
    }
}

function loraleya_checkout_is_manager_customer_note_email( $order, $email ) {
    return $order instanceof WC_Order
        && $email instanceof WC_Email
        && 'customer_note' === $email->id
        && 'yes' === $order->get_meta( '_ll_manager_confirmation_required' )
        && 'yes' !== $order->get_meta( '_ll_individual_order' );
}

function loraleya_checkout_manager_customer_note_context( $order_number = null ) {
    static $current_order_number = '';

    if ( null !== $order_number ) {
        $current_order_number = (string) $order_number;
    }

    return $current_order_number;
}

function loraleya_checkout_manager_customer_note_text( $translation, $text, $domain ) {
    $order_number = loraleya_checkout_manager_customer_note_context();
    if ( '' === $order_number || 'woocommerce' !== $domain ) {
        return $translation;
    }

    if ( 'The following note has been added to your order:' === $text ) {
        return 'Заказ №' . $order_number;
    }

    if ( 'As a reminder, here are your order details:' === $text ) {
        return '';
    }

    return $translation;
}
add_filter( 'gettext', 'loraleya_checkout_manager_customer_note_text', 20, 3 );

function loraleya_checkout_manager_customer_note_hooks( $activate ) {
    static $removed_hooks = array();
    static $is_active     = false;

    if ( $activate ) {
        if ( $is_active ) {
            return;
        }

        $mailer = WC()->mailer();
        $hooks  = array(
            array( 'woocommerce_email_order_details', array( $mailer, 'order_downloads' ), 4 ),
            array( 'woocommerce_email_order_details', array( $mailer, 'order_details' ), 4 ),
            array( 'woocommerce_email_order_details', array( 'WC_Structured_Data', 'generate_order_data' ), 3 ),
            array( 'woocommerce_email_order_details', array( 'WC_Structured_Data', 'output_structured_data' ), 3 ),
            array( 'woocommerce_email_order_meta', array( $mailer, 'order_meta' ), 3 ),
            array( 'woocommerce_email_customer_details', array( $mailer, 'customer_details' ), 3 ),
            array( 'woocommerce_email_customer_details', array( $mailer, 'email_addresses' ), 3 ),
            array( 'woocommerce_email_customer_details', array( $mailer, 'email_address' ), 3 ),
            array( 'woocommerce_email_customer_details', array( $mailer, 'additional_checkout_fields' ), 3 ),
        );

        foreach ( $hooks as $hook ) {
            $priority = has_action( $hook[0], $hook[1] );
            if ( false !== $priority ) {
                remove_action( $hook[0], $hook[1], $priority );
                $removed_hooks[] = array( $hook[0], $hook[1], $priority, $hook[2] );
            }
        }

        add_action( 'woocommerce_email_customer_details', 'loraleya_checkout_manager_customer_note_restore', PHP_INT_MAX, 4 );
        $is_active = true;
        return;
    }

    if ( ! $is_active ) {
        return;
    }

    remove_action( 'woocommerce_email_customer_details', 'loraleya_checkout_manager_customer_note_restore', PHP_INT_MAX );

    foreach ( $removed_hooks as $hook ) {
        add_action( $hook[0], $hook[1], $hook[2], $hook[3] );
    }

    $removed_hooks = array();
    $is_active     = false;
}

function loraleya_checkout_manager_customer_note_restore( $order, $sent_to_admin, $plain_text, $email ) {
    if ( loraleya_checkout_is_manager_customer_note_email( $order, $email ) ) {
        loraleya_checkout_manager_customer_note_hooks( false );
        loraleya_checkout_manager_customer_note_context( '' );
    }
}

function loraleya_checkout_is_initial_customer_order( $order ) {
    return $order instanceof WC_Order
        && 'yes' === $order->get_meta( '_ll_manager_confirmation_required' )
        && 'yes' !== $order->get_meta( '_ll_individual_order' );
}

function loraleya_checkout_order_items_total_html( $order, $tax_display ) {
    $total = 0.0;

    foreach ( $order->get_items( 'line_item' ) as $item ) {
        $total += (float) $item->get_total();

        if ( 'incl' === $tax_display ) {
            $total += (float) $item->get_total_tax();
        }
    }

    return wc_price( $total, array( 'currency' => $order->get_currency() ) );
}

function loraleya_checkout_customer_on_hold_order_totals( $totals, $order, $tax_display ) {
    if ( ! loraleya_checkout_is_initial_customer_order( $order ) ) {
        return $totals;
    }

    return array(
        'll_product_total' => array(
            'label' => 'Итого стоимость товара:',
            'value' => loraleya_checkout_order_items_total_html( $order, $tax_display ),
        ),
    );
}

function loraleya_checkout_customer_on_hold_email_begin( $order, $sent_to_admin, $plain_text, $email ) {
    if (
        ! $sent_to_admin
        && $email instanceof WC_Email
        && 'customer_on_hold_order' === $email->id
        && loraleya_checkout_is_initial_customer_order( $order )
    ) {
        add_filter( 'woocommerce_get_order_item_totals', 'loraleya_checkout_customer_on_hold_order_totals', 100, 3 );
    }
}
add_action( 'woocommerce_email_before_order_table', 'loraleya_checkout_customer_on_hold_email_begin', 20, 4 );

function loraleya_checkout_customer_on_hold_email_end( $order, $sent_to_admin, $plain_text, $email ) {
    if (
        ! $sent_to_admin
        && $email instanceof WC_Email
        && 'customer_on_hold_order' === $email->id
        && loraleya_checkout_is_initial_customer_order( $order )
    ) {
        remove_filter( 'woocommerce_get_order_item_totals', 'loraleya_checkout_customer_on_hold_order_totals', 100 );
    }
}
add_action( 'woocommerce_email_after_order_table', 'loraleya_checkout_customer_on_hold_email_end', 5, 4 );

function loraleya_checkout_customer_on_hold_intro_context( $active = null ) {
    static $is_active = false;

    if ( null !== $active ) {
        $is_active = (bool) $active;
    }

    return $is_active;
}

function loraleya_checkout_customer_on_hold_intro_text( $translation, $text, $domain ) {
    if ( ! loraleya_checkout_customer_on_hold_intro_context() || 'woocommerce' !== $domain ) {
        return $translation;
    }

    $hidden_texts = array(
        'We’ve received your order and it’s currently on hold until we can confirm your payment has been processed.',
        'Here’s a reminder of what you’ve ordered:',
        'Thanks for your order. It’s on-hold until we confirm that payment has been received.',
        "We've received your order and it's currently on hold until we can confirm your payment has been processed.",
        "Here's a reminder of what you've ordered:",
        "Thanks for your order. It's on-hold until we confirm that payment has been received.",
    );

    return in_array( $text, $hidden_texts, true ) ? '' : $translation;
}
add_filter( 'gettext', 'loraleya_checkout_customer_on_hold_intro_text', 20, 3 );

function loraleya_checkout_customer_on_hold_intro_end( $order, $sent_to_admin, $plain_text, $email ) {
    if (
        ! $sent_to_admin
        && $email instanceof WC_Email
        && 'customer_on_hold_order' === $email->id
        && loraleya_checkout_is_initial_customer_order( $order )
    ) {
        loraleya_checkout_customer_on_hold_intro_context( false );
    }
}
add_action( 'woocommerce_email_before_order_table', 'loraleya_checkout_customer_on_hold_intro_end', 1, 4 );

function loraleya_checkout_invoice_order_totals( $totals, $order, $tax_display ) {
    if ( $order instanceof WC_Order && 'yes' === $order->get_meta( '_ll_manager_confirmation_required' ) ) {
        unset( $totals['payment_method'] );

        $service = $order->get_meta( '_ll_delivery_service' );
        if ( isset( $totals['shipping'] ) && in_array( $service, array( 'fivepost', 'cdek', 'yandex' ), true ) ) {
            $shipping_total = (float) $order->get_shipping_total();
            if ( 'incl' === $tax_display ) {
                $shipping_total += (float) $order->get_shipping_tax();
            }

            $shipping_labels = array(
                'fivepost' => 'Доставка 5Post',
                'cdek'     => 'Доставка СДЭК',
                'yandex'   => 'Доставка Яндекс',
            );
            $totals['shipping']['label'] = $shipping_labels[ $service ];
            $totals['shipping']['value'] = wc_price( $shipping_total, array( 'currency' => $order->get_currency() ) );
            unset( $totals['shipping']['meta'] );
        }
    }

    return $totals;
}

function loraleya_checkout_invoice_totals_begin( $order, $sent_to_admin, $plain_text, $email ) {
    if ( loraleya_checkout_is_manager_invoice_email( $order, $email ) ) {
        add_filter( 'woocommerce_get_order_item_totals', 'loraleya_checkout_invoice_order_totals', 20, 3 );
    }
}
add_action( 'woocommerce_email_before_order_table', 'loraleya_checkout_invoice_totals_begin', 20, 4 );

function loraleya_checkout_invoice_totals_end( $order, $sent_to_admin, $plain_text, $email ) {
    if ( loraleya_checkout_is_manager_invoice_email( $order, $email ) ) {
        remove_filter( 'woocommerce_get_order_item_totals', 'loraleya_checkout_invoice_order_totals', 20 );
    }
}
add_action( 'woocommerce_email_after_order_table', 'loraleya_checkout_invoice_totals_end', 5, 4 );

function loraleya_checkout_email_price_styles( $css ) {
    return $css . "\n.woocommerce-Price-amount { white-space: nowrap; word-break: normal; overflow-wrap: normal; }\n";
}
add_filter( 'woocommerce_email_styles', 'loraleya_checkout_email_price_styles' );

function loraleya_checkout_email_delivery_summary( $order, $sent_to_admin, $plain_text, $email ) {
    if ( loraleya_checkout_is_manager_processing_email( $order, $email ) ) {
        return;
    }

    if ( loraleya_checkout_is_manager_completed_email( $order, $email ) ) {
        return;
    }

    if ( loraleya_checkout_is_manager_customer_note_email( $order, $email ) ) {
        return;
    }

    $rows = loraleya_checkout_delivery_summary_rows( $order );
    if ( ! $rows ) {
        return;
    }

    $is_initial_customer_email = ! $sent_to_admin
        && $email instanceof WC_Email
        && 'customer_on_hold_order' === $email->id
        && loraleya_checkout_is_initial_customer_order( $order );

    if ( $is_initial_customer_email ) {
        $service = $order->get_meta( '_ll_delivery_service' );

        if ( in_array( $service, array( 'cdek', 'yandex' ), true ) ) {
            unset( $rows['Доставка'] );
            $rows[ 'cdek' === $service ? 'Доставка СДЭК' : 'Доставка Яндекс' ] = 'Стоимость рассчитает менеджер индивидуально';
        }

        if ( $plain_text ) {
            echo "\nДОСТАВКА И СОГЛАСОВАНИЕ\n";
            echo "Заказ принят. Мы свяжемся с вами для согласования деталей заказа, сроков и доставки. После согласования вы получите ссылку на оплату.\n";
            foreach ( $rows as $label => $value ) {
                echo wp_strip_all_tags( $label . ': ' . $value ) . "\n";
            }
            return;
        }

        echo '<h2>Доставка и согласование</h2>';
        echo '<p>Заказ принят. Мы свяжемся с вами для согласования деталей заказа, сроков и доставки. После согласования вы получите ссылку на оплату.</p>';
        echo '<table cellspacing="0" cellpadding="6" style="width:100%;border:1px solid #e5e5e5" border="1">';
        foreach ( $rows as $label => $value ) {
            echo '<tr><th style="text-align:left">' . esc_html( $label ) . '</th><td>' . wp_kses_post( $value ) . '</td></tr>';
        }
        echo '</table>';
        return;
    }

    $is_manager_invoice = loraleya_checkout_is_manager_invoice_email( $order, $email );
    $service            = $order->get_meta( '_ll_delivery_service' );
    if ( $is_manager_invoice ) {
        if ( 'fivepost' === $service ) {
            unset( $rows['Условия 5Post'] );
        } elseif ( in_array( $service, array( 'cdek', 'yandex' ), true ) ) {
            unset( $rows['Доставка'] );
        }
    }

    if ( $plain_text ) {
        echo $is_manager_invoice ? "\nДОСТАВКА\n" : "\nДОСТАВКА И ПОДТВЕРЖДЕНИЕ\n";
        if ( ! $is_manager_invoice ) {
            echo $sent_to_admin
                ? "Необходимо связаться с покупателем до оплаты.\n"
                : "Менеджер свяжется с вами для подтверждения заказа.\n";
        }
        foreach ( $rows as $label => $value ) {
            echo wp_strip_all_tags( $label . ': ' . $value ) . "\n";
        }
        return;
    }

    echo $is_manager_invoice ? '<h2>Доставка</h2>' : '<h2>Доставка и подтверждение</h2>';
    if ( ! $is_manager_invoice ) {
        echo $sent_to_admin
            ? '<p><strong>Необходимо связаться с покупателем до оплаты.</strong></p>'
            : '<p>Менеджер свяжется с вами для подтверждения наличия, количества, сроков и доставки.</p>';
    }
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
    if ( loraleya_checkout_is_initial_customer_order( $order ) ) {
        return 'Заказ №' . $order->get_order_number() . ' принят';
    }
    return $subject;
}, 20, 2 );

add_filter( 'woocommerce_email_heading_customer_on_hold_order', static function ( $heading, $order ) {
    if ( loraleya_checkout_is_initial_customer_order( $order ) ) {
        loraleya_checkout_customer_on_hold_intro_context( true );
        return 'Ваш заказ принят';
    }
    return $heading;
}, 20, 2 );

add_filter( 'woocommerce_email_additional_content_customer_on_hold_order', static function ( $content, $order ) {
    return loraleya_checkout_is_initial_customer_order( $order ) ? '' : $content;
}, 20, 2 );

add_filter( 'woocommerce_email_order_details_heading', static function ( $heading, $order, $email ) {
    if (
        $email instanceof WC_Email
        && 'customer_on_hold_order' === $email->id
        && loraleya_checkout_is_initial_customer_order( $order )
    ) {
        return 'Детали заказа';
    }

    return $heading;
}, 20, 3 );

add_filter( 'woocommerce_email_subject_customer_invoice', static function ( $subject, $order ) {
    if ( $order instanceof WC_Order && 'yes' === $order->get_meta( '_ll_manager_confirmation_required' ) ) {
        return 'Заказ №' . $order->get_order_number() . ' подтверждён и готов к оплате';
    }
    return $subject;
}, 20, 2 );

add_filter( 'woocommerce_email_heading_customer_invoice', static function ( $heading, $order ) {
    if ( $order instanceof WC_Order && 'yes' === $order->get_meta( '_ll_manager_confirmation_required' ) ) {
        loraleya_checkout_manager_invoice_intro_context( true );
        return 'Заказ подтверждён — можно оплатить';
    }
    return $heading;
}, 20, 2 );

add_filter( 'woocommerce_email_subject_customer_processing_order', static function ( $subject, $order, $email ) {
    if ( loraleya_checkout_is_manager_processing_email( $order, $email ) ) {
        return 'Заказ №' . $order->get_order_number() . ' принят в работу';
    }

    return $subject;
}, 20, 3 );

add_filter( 'woocommerce_email_heading_customer_processing_order', static function ( $heading, $order, $email ) {
    if ( loraleya_checkout_is_manager_processing_email( $order, $email ) ) {
        loraleya_checkout_manager_processing_intro_context( true );
        loraleya_checkout_manager_processing_hooks( true );
        return 'Ваш заказ принят в работу';
    }

    return $heading;
}, 20, 3 );

add_filter( 'woocommerce_email_additional_content_customer_processing_order', static function ( $content, $order, $email ) {
    return loraleya_checkout_is_manager_processing_email( $order, $email ) ? '' : $content;
}, 20, 3 );

add_filter( 'woocommerce_email_subject_customer_completed_order', static function ( $subject, $order, $email ) {
    if ( loraleya_checkout_is_manager_completed_email( $order, $email ) ) {
        return 'Заказ №' . $order->get_order_number() . ' отправлен';
    }

    return $subject;
}, 20, 3 );

add_filter( 'woocommerce_email_heading_customer_completed_order', static function ( $heading, $order, $email ) {
    if ( loraleya_checkout_is_manager_completed_email( $order, $email ) ) {
        loraleya_checkout_manager_completed_intro_context( true );
        loraleya_checkout_manager_completed_hooks( true );
        return 'Ваш заказ отправлен';
    }

    return $heading;
}, 20, 3 );

add_filter( 'woocommerce_email_additional_content_customer_completed_order', static function ( $content, $order, $email ) {
    return loraleya_checkout_is_manager_completed_email( $order, $email ) ? '' : $content;
}, 20, 3 );

add_filter( 'woocommerce_email_heading_customer_note', static function ( $heading, $order, $email ) {
    if ( loraleya_checkout_is_manager_customer_note_email( $order, $email ) ) {
        loraleya_checkout_manager_customer_note_context( $order->get_order_number() );
        loraleya_checkout_manager_customer_note_hooks( true );
        return 'Сообщение по вашему заказу';
    }

    return $heading;
}, 20, 3 );

add_filter( 'woocommerce_email_additional_content_customer_note', static function ( $content, $order, $email ) {
    return loraleya_checkout_is_manager_customer_note_email( $order, $email ) ? '' : $content;
}, 20, 3 );

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
