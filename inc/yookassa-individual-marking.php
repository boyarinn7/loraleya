<?php
/**
 * YooKassa marking bridge for productless individual-order line items.
 *
 * YooKassa 2.16.3 expects every marked order item to have a WC_Product. The
 * individual-order workflow intentionally keeps product_id = 0, so this file
 * adapts only those items without creating or persisting placeholder products.
 */

defined( 'ABSPATH' ) || exit;

const LORALEYA_YOOKASSA_MARKING_FIELD_META = '_marking_fields';

/**
 * Return the native YooKassa marking values for a supported individual item.
 *
 * @param WC_Order_Item_Product $item Order item.
 * @return array|null
 */
function loraleya_yookassa_individual_marking_params( $item ) {
    if ( ! $item instanceof WC_Order_Item_Product || 'yes' !== $item->get_meta( '_ll_individual_line_item', true ) ) {
        return null;
    }

    $marked_types = array( 'tablecloth', 'runner', 'napkins', 'kuverts', 'curtains' );
    $item_type    = sanitize_key( (string) $item->get_meta( '_ll_individual_item_type', true ) );

    if ( ! in_array( $item_type, $marked_types, true ) ) {
        return null;
    }

    return array(
        'category'       => 'light_industry',
        'measure'        => 'piece',
        'denominator'    => 1,
        'mark_code_info' => 'gs_1m',
    );
}

/**
 * Check whether an item belongs to the productless individual-order workflow.
 *
 * @param mixed $item Order item.
 * @return bool
 */
function loraleya_yookassa_is_individual_item( $item ) {
    return $item instanceof WC_Order_Item_Product
        && 'yes' === $item->get_meta( '_ll_individual_line_item', true );
}

/**
 * Use the same remaining quantity calculation as YooKassa.
 *
 * @param WC_Order_Item_Product $item Order item.
 * @return int
 */
function loraleya_yookassa_remaining_quantity( $item ) {
    if ( class_exists( 'YooKassaMarkingOrder' ) ) {
        return (int) YooKassaMarkingOrder::getRemainingQuantity( $item );
    }

    $order             = $item->get_order();
    $refunded_quantity = $order ? (int) $order->get_qty_refunded_for_item( $item->get_id() ) : 0;

    return (int) $item->get_quantity() + $refunded_quantity;
}

/**
 * Replace one YooKassa action callback while retaining its instance.
 *
 * @param string $hook        Hook name.
 * @param string $class_name  Callback object class.
 * @param string $method_name Callback method.
 * @param string $store_key   Global key for the retained callback.
 * @param string $replacement Replacement callback.
 * @param int    $accepted_args Accepted argument count.
 * @return bool
 */
function loraleya_yookassa_replace_action_callback( $hook, $class_name, $method_name, $store_key, $replacement, $accepted_args ) {
    global $wp_filter;

    if ( empty( $wp_filter[ $hook ] ) || ! $wp_filter[ $hook ] instanceof WP_Hook ) {
        return false;
    }

    foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
        foreach ( $callbacks as $callback_data ) {
            $callback = isset( $callback_data['function'] ) ? $callback_data['function'] : null;
            if (
                ! is_array( $callback )
                || ! isset( $callback[0], $callback[1] )
                || ! is_object( $callback[0] )
                || ! is_a( $callback[0], $class_name )
                || $method_name !== $callback[1]
            ) {
                continue;
            }

            remove_action( $hook, $callback, $priority );
            $GLOBALS[ $store_key ] = $callback;
            add_action( $hook, $replacement, $priority, $accepted_args );
            return true;
        }
    }

    return false;
}

/**
 * Install wrappers after YooKassa has registered its hooks.
 */
function loraleya_yookassa_install_individual_marking_bridge() {
    static $installed = false;

    if ( $installed || ! class_exists( 'YooKassaMarkingOrder' ) ) {
        return;
    }

    $installed = true;

    loraleya_yookassa_replace_action_callback(
        'woocommerce_admin_order_item_values',
        'YooKassaMarkingOrder',
        'addMarkingProductValuesTab',
        'loraleya_yookassa_order_values_callback',
        'loraleya_yookassa_render_order_item_marking_cell',
        3
    );

    if ( class_exists( 'YooKassaSecondReceipt' ) ) {
        loraleya_yookassa_replace_action_callback(
            'woocommerce_order_status_completed',
            'YooKassaSecondReceipt',
            'changeOrderStatusToCompleted',
            'loraleya_yookassa_completed_callback',
            'loraleya_yookassa_completed_with_individual_items',
            1
        );
    }
}
add_action( 'after_setup_theme', 'loraleya_yookassa_install_individual_marking_bridge', 100 );

/**
 * Render YooKassa's native marking button for supported individual items.
 * Ordinary WooCommerce items continue through the original plugin callback.
 *
 * @param WC_Product|null      $product Product passed by WooCommerce.
 * @param WC_Order_Item        $item    Order item.
 * @param int                  $item_id Order item ID.
 */
function loraleya_yookassa_render_order_item_marking_cell( $product, $item, $item_id ) {
    $params = loraleya_yookassa_individual_marking_params( $item );
    if ( null === $params ) {
        $callback = isset( $GLOBALS['loraleya_yookassa_order_values_callback'] )
            ? $GLOBALS['loraleya_yookassa_order_values_callback']
            : null;
        if ( is_callable( $callback ) ) {
            call_user_func( $callback, $product, $item, $item_id );
        }
        return;
    }

    $quantity = loraleya_yookassa_remaining_quantity( $item );
    if ( $quantity <= 0 ) {
        echo '<td class="mark_code_column" style="text-align:center;color:gray;">'
            . esc_html__( 'Не требуется', 'yookassa' )
            . '</td>';
        return;
    }

    $marking_fields = wc_get_order_item_meta( $item_id, LORALEYA_YOOKASSA_MARKING_FIELD_META, true );
    $icon_class     = 'new';
    if ( is_array( $marking_fields ) && ! empty( $marking_fields ) ) {
        $filled_count = 0;
        foreach ( $marking_fields as $marking_value ) {
            if ( is_scalar( $marking_value ) && '' !== (string) $marking_value ) {
                $filled_count++;
            }
        }
        $icon_class = $filled_count === $quantity ? 'filled' : 'not-filled';
    }

    printf(
        '<td class="mark-code-column" style="text-align:center;"><button type="button" class="button yookassa-marking-button" id="yookassa-marking-button-%1$d"><span class="yookassa-mark-code-icon %2$s"></span></button></td>',
        absint( $item_id ),
        esc_attr( $icon_class )
    );
}

/**
 * Return the native popup payload for a productless individual item.
 *
 * The YooKassa JS, popup markup and nonce remain owned by the plugin.
 */
function loraleya_yookassa_get_individual_item_meta() {
    $item_id = isset( $_POST['itemId'] ) ? absint( $_POST['itemId'] ) : 0;
    $item    = $item_id ? WC_Order_Factory::get_order_item( $item_id ) : false;
    $params  = $item ? loraleya_yookassa_individual_marking_params( $item ) : null;

    if ( null === $params ) {
        return;
    }

    check_ajax_referer( 'woocommerce_get_oder_item_meta_nonce', 'security' );

    $quantity = loraleya_yookassa_remaining_quantity( $item );
    if ( $quantity <= 0 ) {
        wp_send_json_error( __( 'Что-то пошло не так. Обновите страницу — если ошибка не уйдёт, напишите нам на cms@yoomoney.ru', 'yookassa' ) );
    }

    $title = sprintf(
        __( 'Маркировка для %s', 'yookassa' ),
        esc_html( $item->get_name() )
    );
    $meta  = wc_get_order_item_meta( $item_id, LORALEYA_YOOKASSA_MARKING_FIELD_META, true );

    wp_send_json_success( array(
        'quantity' => $quantity,
        'title'    => $title,
        'fields'   => array(
            array(
                'name'        => 'marking_field',
                'placeholder' => __( 'Отсканируйте маркировку с упаковки', 'yookassa' ),
                'validate'    => array(
                    'pattern'     => addslashes( '^[A-Za-z0-9!"%&\'()*+,-./_:;=<>?\\\\]+$' ),
                    'isEmpty'     => true,
                    'isDuplicate' => true,
                    'denominator' => $params['denominator'],
                ),
            ),
        ),
        'jsonMeta' => wp_json_encode( is_array( $meta ) ? $meta : array() ),
    ) );
}
add_action( 'wp_ajax_woocommerce_get_oder_item_meta', 'loraleya_yookassa_get_individual_item_meta', 0 );

/**
 * Validate and save individual-item codes in YooKassa's native order-item meta.
 */
function loraleya_yookassa_save_individual_marking_meta() {
    $item_id = isset( $_POST['orderItemId'] ) ? absint( $_POST['orderItemId'] ) : 0;
    $item    = $item_id ? WC_Order_Factory::get_order_item( $item_id ) : false;
    $params  = $item ? loraleya_yookassa_individual_marking_params( $item ) : null;

    if ( null === $params ) {
        return;
    }

    check_ajax_referer( 'save_marking_meta_nonce', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
        wp_send_json_error( array(
            'message' => __( 'Нет прав на сохранение маркировки — проверьте доступы в настройках', 'yookassa' ),
            'type'    => 'permission_error',
        ) );
    }

    $marking_fields = isset( $_POST[ LORALEYA_YOOKASSA_MARKING_FIELD_META ] )
        && is_array( $_POST[ LORALEYA_YOOKASSA_MARKING_FIELD_META ] )
        ? wp_unslash( $_POST[ LORALEYA_YOOKASSA_MARKING_FIELD_META ] )
        : array();

    if ( ! $item_id || empty( $marking_fields ) ) {
        wp_send_json_error( array(
            'message' => __( 'Что-то пошло не так. Обновите страницу — если ошибка не уйдёт, напишите нам на cms@yoomoney.ru', 'yookassa' ),
            'type'    => 'invalid_data',
        ) );
    }

    $error_fields = array();
    foreach ( $marking_fields as $key => $value ) {
        if ( ! is_scalar( $value ) || '' === (string) $value ) {
            unset( $marking_fields[ $key ] );
            continue;
        }

        $value                  = (string) $value;
        $marking_fields[ $key ] = $value;

        try {
            $product_code = new \YooKassa\Helpers\ProductCode( $value );
            $code_info    = $product_code->getType();
            if ( $params['mark_code_info'] !== $code_info ) {
                $error_fields[ $key ] = array(
                    'value'         => $value,
                    'expected_type' => $params['mark_code_info'],
                    'actual_type'   => 'unknown' !== $code_info ? $code_info : null,
                );
            }
        } catch ( Exception $exception ) {
            $error_fields[ $key ] = array(
                'value'         => $value,
                'expected_type' => $params['mark_code_info'],
                'actual_type'   => null,
                'error'         => __( 'Слишком много символов — проверьте, пожалуйста', 'yookassa' ),
            );
        }
    }

    if ( ! empty( $error_fields ) ) {
        wp_send_json_error( array(
            'message' => __( 'Где-то указаны неверные данные. Нужно указать код маркировки (не штрихкод, QR-код или другой текст)', 'yookassa' ),
            'type'    => 'validation_error',
            'fields'  => $error_fields,
        ) );
    }

    wc_update_order_item_meta( $item_id, LORALEYA_YOOKASSA_MARKING_FIELD_META, $marking_fields );
    wp_send_json_success( array(
        'message' => __( 'Готово — сохранили', 'yookassa' ),
    ) );
}
add_action( 'wp_ajax_save_marking_meta', 'loraleya_yookassa_save_individual_marking_meta', 0 );

/**
 * Find an existing physical WooCommerce product with exact YooKassa metadata.
 *
 * @param bool $marked Whether the product must have the textile marking config.
 * @return int
 */
function loraleya_yookassa_find_proxy_product_id( $marked ) {
    static $cache = array();

    $cache_key = $marked ? 'marked' : 'unmarked';
    if ( array_key_exists( $cache_key, $cache ) ) {
        return $cache[ $cache_key ];
    }

    $meta_query = $marked
        ? array(
            'relation' => 'AND',
            array( 'key' => '_yookassa_marking_category', 'value' => 'light_industry' ),
            array( 'key' => '_yookassa_marking_measure', 'value' => 'piece' ),
            array( 'key' => '_yookassa_marking_denominator', 'value' => '1' ),
            array( 'key' => '_yookassa_mark_code_info', 'value' => 'gs_1m' ),
        )
        : array(
            array( 'key' => '_yookassa_marking_category', 'compare' => 'NOT EXISTS' ),
        );

    $product_ids = get_posts( array(
        'post_type'              => array( 'product', 'product_variation' ),
        'post_status'            => array( 'publish', 'private', 'draft' ),
        'fields'                 => 'ids',
        'posts_per_page'         => 20,
        'orderby'                => 'ID',
        'order'                  => 'ASC',
        'no_found_rows'          => true,
        'suppress_filters'       => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'meta_query'             => $meta_query,
    ) );

    $cache[ $cache_key ] = 0;
    foreach ( $product_ids as $product_id ) {
        $product = wc_get_product( $product_id );
        if ( $product && ! $product->is_virtual() && ! $product->is_downloadable() ) {
            $cache[ $cache_key ] = (int) $product_id;
            break;
        }
    }

    return $cache[ $cache_key ];
}

/**
 * Run YooKassa's original completed callback with a request-local item bridge.
 *
 * No order item is saved: cloned objects exist only while YooKassa builds the
 * second receipt. This keeps product_id = 0 in WooCommerce and leaves the first
 * receipt untouched.
 *
 * @param int $order_id Order ID.
 */
function loraleya_yookassa_completed_with_individual_items( $order_id ) {
    $callback = isset( $GLOBALS['loraleya_yookassa_completed_callback'] )
        ? $GLOBALS['loraleya_yookassa_completed_callback']
        : null;
    if ( ! is_callable( $callback ) ) {
        return;
    }

    $GLOBALS['loraleya_yookassa_second_receipt_bridge'] = array(
        'order_id'         => absint( $order_id ),
        'marked_product'   => loraleya_yookassa_find_proxy_product_id( true ),
        'unmarked_product' => loraleya_yookassa_find_proxy_product_id( false ),
    );

    try {
        call_user_func( $callback, $order_id );
    } finally {
        unset( $GLOBALS['loraleya_yookassa_second_receipt_bridge'] );
    }
}

/**
 * Supply request-local product references only to YooKassa's second receipt.
 *
 * @param array             $items Order items.
 * @param WC_Abstract_Order $order Order.
 * @param array|string      $types Requested item types.
 * @return array
 */
function loraleya_yookassa_bridge_second_receipt_items( $items, $order, $types ) {
    $context = isset( $GLOBALS['loraleya_yookassa_second_receipt_bridge'] )
        ? $GLOBALS['loraleya_yookassa_second_receipt_bridge']
        : null;

    if (
        ! is_array( $context )
        || ! $order instanceof WC_Abstract_Order
        || (int) $order->get_id() !== (int) $context['order_id']
    ) {
        return $items;
    }

    foreach ( $items as $item_id => $item ) {
        if ( ! loraleya_yookassa_is_individual_item( $item ) ) {
            continue;
        }

        $is_marked        = null !== loraleya_yookassa_individual_marking_params( $item );
        $proxy_product_id = $is_marked
            ? (int) $context['marked_product']
            : (int) $context['unmarked_product'];

        if ( $proxy_product_id <= 0 ) {
            error_log( sprintf(
                '[LoraLeya] YooKassa marking bridge: no %s proxy product for order item %d.',
                $is_marked ? 'marked' : 'unmarked',
                $item->get_id()
            ) );
            continue;
        }

        $proxy_item = clone $item;
        $proxy_item->set_product_id( $proxy_product_id );
        $proxy_item->set_variation_id( 0 );
        $items[ $item_id ] = $proxy_item;
    }

    return $items;
}
add_filter( 'woocommerce_order_get_items', 'loraleya_yookassa_bridge_second_receipt_items', 10, 3 );
