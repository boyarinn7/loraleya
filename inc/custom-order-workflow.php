<?php
/**
 * Persistent workflow for individual-order requests and their conversion to
 * editable WooCommerce orders.
 */

defined( 'ABSPATH' ) || exit;

function loraleya_custom_order_statuses() {
    return array(
        'new'         => 'Новая',
        'in_progress' => 'В работе',
        'agreed'      => 'Согласована',
        'converted'   => 'Создан заказ',
        'cancelled'   => 'Отменена',
    );
}

function loraleya_custom_order_delivery_services() {
    return array(
        'fivepost' => '5Post',
        'cdek'     => 'СДЭК',
        'yandex'   => 'Яндекс Доставка',
    );
}

function loraleya_custom_order_register_post_type() {
    $manage_capability = 'manage_woocommerce';

    register_post_type( 'll_custom_request', array(
        'labels' => array(
            'name'               => 'Индивидуальные заказы',
            'singular_name'      => 'Индивидуальный заказ',
            'menu_name'          => 'Индивидуальные заказы',
            'all_items'          => 'Индивидуальные заказы',
            'edit_item'          => 'Редактировать заявку',
            'view_item'          => 'Просмотреть заявку',
            'search_items'       => 'Найти заявку',
            'not_found'          => 'Заявки не найдены',
            'not_found_in_trash' => 'В корзине заявок нет',
        ),
        'public'              => false,
        'publicly_queryable'  => false,
        'exclude_from_search' => true,
        'show_ui'             => true,
        'show_in_menu'        => 'woocommerce',
        'show_in_rest'        => false,
        'query_var'           => false,
        'rewrite'             => false,
        'has_archive'         => false,
        'supports'            => array(),
        'map_meta_cap'        => false,
        'capabilities'        => array(
            'edit_post'              => $manage_capability,
            'read_post'              => $manage_capability,
        'delete_post'            => 'manage_options',
            'edit_posts'             => $manage_capability,
            'edit_others_posts'      => $manage_capability,
            'publish_posts'          => $manage_capability,
            'read_private_posts'     => $manage_capability,
        'delete_posts'           => 'manage_options',
        'delete_private_posts'   => 'manage_options',
        'delete_published_posts' => 'manage_options',
        'delete_others_posts'    => 'manage_options',
            'edit_private_posts'     => $manage_capability,
            'edit_published_posts'   => $manage_capability,
            'create_posts'           => 'do_not_allow',
        ),
    ) );
}
add_action( 'init', 'loraleya_custom_order_register_post_type' );

function loraleya_custom_order_number( $request_id ) {
    $number = get_post_meta( $request_id, '_ll_request_number', true );
    return $number ? $number : 'ИЗ-' . absint( $request_id );
}

function loraleya_custom_order_meta( $request_id, $key, $default = '' ) {
    $value = get_post_meta( $request_id, $key, true );
    return '' === $value ? $default : $value;
}

/** Acquire a database-unique WordPress option lock without putting PII in its name. */
function loraleya_custom_order_acquire_option_lock( $option_name, $ttl = 300 ) {
    $owner = wp_generate_uuid4();
    $value = array(
        'owner' => $owner,
        'time'  => time(),
    );

    if ( add_option( $option_name, $value, '', 'no' ) ) {
        return $owner;
    }

    $current      = get_option( $option_name );
    $current_time = is_array( $current ) && isset( $current['time'] ) ? absint( $current['time'] ) : 0;
    if ( $current_time && ( time() - $current_time ) >= absint( $ttl ) ) {
        delete_option( $option_name );
        if ( add_option( $option_name, $value, '', 'no' ) ) {
            return $owner;
        }
    }

    return new WP_Error( 'option_lock_busy', 'Операция уже выполняется.' );
}

function loraleya_custom_order_release_option_lock( $option_name, $owner ) {
    $current = get_option( $option_name );
    if (
        is_array( $current )
        && isset( $current['owner'] )
        && hash_equals( (string) $current['owner'], (string) $owner )
    ) {
        delete_option( $option_name );
    }
}

function loraleya_custom_order_request_token_hash( $token ) {
    return hash_hmac( 'sha256', (string) $token, wp_salt( 'nonce' ) );
}

function loraleya_custom_order_find_request_by_token_hash( $token_hash, $complete_only = true ) {
    $ids = get_posts( array(
        'post_type'      => 'll_custom_request',
        'post_status'    => array( 'publish', 'private', 'draft', 'pending', 'future', 'trash' ),
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'orderby'        => 'ID',
        'order'          => 'ASC',
        'meta_key'       => '_ll_request_token_hash',
        'meta_value'     => $token_hash,
        'no_found_rows'  => true,
    ) );

    if ( ! $ids ) {
        return 0;
    }

    $request_id = absint( $ids[0] );
    return ! $complete_only || 'yes' === get_post_meta( $request_id, '_ll_request_storage_complete', true ) ? $request_id : 0;
}

function loraleya_custom_order_rate_limit_key() {
    $source = isset( $_SERVER['REMOTE_ADDR'] ) ? trim( (string) $_SERVER['REMOTE_ADDR'] ) : '';
    if ( '' === $source ) {
        return '';
    }

    return 'll_custom_rate_' . substr( hash_hmac( 'sha256', $source, wp_salt( 'nonce' ) ), 0, 40 );
}

function loraleya_custom_order_check_rate_limit() {
    $key = loraleya_custom_order_rate_limit_key();
    if ( '' === $key ) {
        return true;
    }

    $count = absint( get_transient( $key ) );
    return $count < 10
        ? true
        : new WP_Error( 'custom_request_rate_limited', 'Слишком много заявок за короткое время. Попробуйте немного позже.' );
}

function loraleya_custom_order_record_rate_limit() {
    $key = loraleya_custom_order_rate_limit_key();
    if ( '' !== $key ) {
        set_transient( $key, absint( get_transient( $key ) ) + 1, 10 * MINUTE_IN_SECONDS );
    }
}

function loraleya_custom_order_add_history( $request_id, $action, $user_id = null ) {
    $history = get_post_meta( $request_id, '_ll_history', true );
    $history = is_array( $history ) ? $history : array();

    if ( null === $user_id ) {
        $user_id = get_current_user_id();
    }

    $user_name = 'Сайт';
    if ( $user_id ) {
        $user = get_userdata( $user_id );
        if ( $user ) {
            $user_name = $user->display_name;
        }
    }

    $history[] = array(
        'time'      => current_time( 'mysql' ),
        'user_id'   => absint( $user_id ),
        'user_name' => sanitize_text_field( $user_name ),
        'action'    => sanitize_text_field( $action ),
    );

    update_post_meta( $request_id, '_ll_history', $history );
}

/**
 * Create the permanent request before any email is attempted.
 */
function loraleya_custom_order_create_request( $data, $request_token ) {
    $token_hash = loraleya_custom_order_request_token_hash( $request_token );
    $existing   = loraleya_custom_order_find_request_by_token_hash( $token_hash );
    if ( $existing ) {
        return array( 'request_id' => $existing, 'created' => false );
    }

    $rate_limit = loraleya_custom_order_check_rate_limit();
    if ( is_wp_error( $rate_limit ) ) {
        return $rate_limit;
    }

    $lock_name = 'll_custom_request_lock_' . substr( $token_hash, 0, 40 );
    $lock      = loraleya_custom_order_acquire_option_lock( $lock_name );
    if ( is_wp_error( $lock ) ) {
        $existing = loraleya_custom_order_find_request_by_token_hash( $token_hash );
        return $existing
            ? array( 'request_id' => $existing, 'created' => false )
            : new WP_Error( 'custom_request_in_progress', 'Заявка уже сохраняется. Повторите отправку через несколько секунд.' );
    }

    $post_id = 0;
    try {
        $existing = loraleya_custom_order_find_request_by_token_hash( $token_hash, false );
        if ( $existing ) {
            if ( 'yes' === get_post_meta( $existing, '_ll_request_storage_complete', true ) ) {
                return array( 'request_id' => $existing, 'created' => false );
            }
            wp_delete_post( $existing, true );
            if ( get_post( $existing ) ) {
                return new WP_Error( 'custom_request_storage_failed', 'Не удалось очистить незавершённую заявку. Попробуйте позже.' );
            }
        }

        $currency = function_exists( 'get_woocommerce_currency' ) ? strtoupper( sanitize_text_field( get_woocommerce_currency() ) ) : '';
        if ( '' === $currency ) {
            throw new Exception( 'WooCommerce currency is unavailable.' );
        }

        $created_at = current_time( 'mysql' );
        $post_id    = wp_insert_post( array(
            'post_type'   => 'll_custom_request',
            'post_status' => 'publish',
            'post_title'  => 'Новая индивидуальная заявка',
        ), true );
        if ( is_wp_error( $post_id ) || ! $post_id ) {
            throw new Exception( 'Failed to insert custom request.' );
        }

        update_post_meta( $post_id, '_ll_request_token_hash', $token_hash );
        update_post_meta( $post_id, '_ll_request_storage_complete', 'no' );
        if (
            ! hash_equals( $token_hash, (string) get_post_meta( $post_id, '_ll_request_token_hash', true ) )
            || 'no' !== get_post_meta( $post_id, '_ll_request_storage_complete', true )
        ) {
            throw new Exception( 'Failed to mark incomplete custom request.' );
        }

        $number       = 'ИЗ-' . $post_id;
        $request_name = $number . ' — ' . $data['customer_name'];
        $title_result = wp_update_post( array(
            'ID'         => $post_id,
            'post_title' => $request_name,
        ), true );
        if ( is_wp_error( $title_result ) || $post_id !== absint( $title_result ) || $request_name !== get_post_field( 'post_title', $post_id ) ) {
            throw new Exception( 'Failed to store custom request number.' );
        }

        $meta = array(
            '_ll_request_number'        => $number,
            '_ll_request_status'        => 'new',
            '_ll_request_currency'      => $currency,
            '_ll_customer_name'         => $data['customer_name'],
            '_ll_phone'                 => $data['phone'],
            '_ll_email'                 => $data['email'],
            '_ll_shape'                 => $data['shape'],
            '_ll_length'                => $data['length'],
            '_ll_width'                 => $data['width'],
            '_ll_persons'               => $data['persons'],
            '_ll_color'                 => $data['color'],
            '_ll_items_summary'         => $data['items_summary'],
            '_ll_options'               => $data['options'],
            '_ll_customer_notes'        => $data['customer_notes'],
            '_ll_created_at'            => $created_at,
            '_ll_privacy_consent'       => 'yes',
            '_ll_privacy_consent_time'  => $created_at,
            '_ll_owner_email_status'    => 'pending',
            '_ll_customer_email_status' => 'pending',
        );
        foreach ( $meta as $key => $value ) {
            update_post_meta( $post_id, $key, $value );
        }

        $critical_meta = array(
            '_ll_request_number'   => $number,
            '_ll_request_currency' => $currency,
            '_ll_customer_name'    => $data['customer_name'],
            '_ll_phone'            => $data['phone'],
            '_ll_email'            => $data['email'],
        );
        foreach ( $critical_meta as $key => $expected ) {
            if ( (string) get_post_meta( $post_id, $key, true ) !== (string) $expected ) {
                throw new Exception( 'Failed to store required custom request metadata.' );
            }
        }

        $snapshot = array(
            'request_number'       => $number,
            'currency'             => $currency,
            'customer_name'        => $data['customer_name'],
            'phone'                => $data['phone'],
            'email'                => $data['email'],
            'shape'                => $data['shape'],
            'length'               => $data['length'],
            'width'                => $data['width'],
            'persons'              => $data['persons'],
            'color'                => $data['color'],
            'items_summary'        => $data['items_summary'],
            'options'              => $data['options'],
            'customer_notes'       => $data['customer_notes'],
            'created_at'           => $created_at,
            'privacy_consent'      => 'yes',
            'privacy_consent_time' => $created_at,
        );
        if ( ! add_post_meta( $post_id, '_ll_initial_snapshot', $snapshot, true ) || get_post_meta( $post_id, '_ll_initial_snapshot', true ) !== $snapshot ) {
            throw new Exception( 'Failed to store initial custom request snapshot.' );
        }

        update_post_meta( $post_id, '_ll_request_storage_complete', 'yes' );
        if (
            'yes' !== get_post_meta( $post_id, '_ll_request_storage_complete', true )
            || ! hash_equals( $token_hash, (string) get_post_meta( $post_id, '_ll_request_token_hash', true ) )
        ) {
            throw new Exception( 'Failed to finalize custom request storage.' );
        }

        loraleya_custom_order_add_history( $post_id, 'Заявка создана через форму сайта', 0 );
        loraleya_custom_order_record_rate_limit();

        return array( 'request_id' => $post_id, 'created' => true );
    } catch ( Throwable $exception ) {
        if ( $post_id ) {
            wp_delete_post( $post_id, true );
            if ( get_post( $post_id ) ) {
                error_log( '[LoraLeya] Failed to remove incomplete custom request #' . absint( $post_id ) . '.' );
            }
        }
        error_log( '[LoraLeya] Custom request storage failed: ' . $exception->getMessage() );
        return new WP_Error( 'custom_request_storage_failed', 'Не удалось надёжно сохранить заявку. Попробуйте позже.' );
    } finally {
        loraleya_custom_order_release_option_lock( $lock_name, $lock );
    }
}

function loraleya_custom_order_send_customer_receipt( $email, $subject, $body ) {
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: LoraLeya <noreply@loraleya.ru>',
    );
    $sent = wp_mail( $email, $subject, $body, $headers );

    if ( ! $sent ) {
        error_log( '[LoraLeya] Custom request customer email failed for request receipt.' );
    }

    return $sent;
}

function loraleya_custom_order_columns( $columns ) {
    return array(
        'cb'               => isset( $columns['cb'] ) ? $columns['cb'] : '<input type="checkbox" />',
        'll_number'        => '№ заявки',
        'date'             => 'Дата',
        'll_customer'      => 'Клиент',
        'll_phone'         => 'Телефон',
        'll_email'         => 'Email',
        'll_status'        => 'Статус',
        'll_products_total'=> 'Согласованная стоимость',
        'll_delivery'      => 'Доставка',
        'll_wc_order'      => 'WooCommerce-заказ',
    );
}
add_filter( 'manage_ll_custom_request_posts_columns', 'loraleya_custom_order_columns' );

function loraleya_custom_order_column_content( $column, $post_id ) {
    if ( 'll_number' === $column ) {
        echo '<strong><a href="' . esc_url( get_edit_post_link( $post_id ) ) . '">' . esc_html( loraleya_custom_order_number( $post_id ) ) . '</a></strong>';
    } elseif ( 'll_customer' === $column ) {
        echo esc_html( loraleya_custom_order_meta( $post_id, '_ll_customer_name', '—' ) );
    } elseif ( 'll_phone' === $column ) {
        echo esc_html( loraleya_custom_order_meta( $post_id, '_ll_phone', '—' ) );
    } elseif ( 'll_email' === $column ) {
        echo esc_html( loraleya_custom_order_meta( $post_id, '_ll_email', '—' ) );
    } elseif ( 'll_status' === $column ) {
        $statuses = loraleya_custom_order_statuses();
        $status   = loraleya_custom_order_meta( $post_id, '_ll_request_status', 'new' );
        echo esc_html( isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status );
    } elseif ( 'll_products_total' === $column ) {
        $total = loraleya_custom_order_meta( $post_id, '_ll_agreed_products_total' );
        $currency = loraleya_custom_order_meta( $post_id, '_ll_request_currency', function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '' );
        echo '' === $total ? '—' : wp_kses_post( wc_price( $total, array( 'currency' => $currency ) ) );
    } elseif ( 'll_delivery' === $column ) {
        $services = loraleya_custom_order_delivery_services();
        $service  = loraleya_custom_order_meta( $post_id, '_ll_delivery_service' );
        $cost     = loraleya_custom_order_meta( $post_id, '_ll_delivery_cost' );
        if ( ! $service ) {
            echo '—';
        } else {
            echo esc_html( isset( $services[ $service ] ) ? $services[ $service ] : $service );
            if ( '' !== $cost ) {
                $currency = loraleya_custom_order_meta( $post_id, '_ll_request_currency', function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '' );
                echo '<br>' . wp_kses_post( wc_price( $cost, array( 'currency' => $currency ) ) );
            }
        }
    } elseif ( 'll_wc_order' === $column ) {
        $order_id = absint( get_post_meta( $post_id, '_ll_wc_order_id', true ) );
        $order    = $order_id && function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : false;
        if ( $order ) {
            echo '<a href="' . esc_url( $order->get_edit_order_url() ) . '">Заказ №' . esc_html( $order->get_order_number() ) . '</a>';
        } else {
            echo '—';
        }
    }
}
add_action( 'manage_ll_custom_request_posts_custom_column', 'loraleya_custom_order_column_content', 10, 2 );

function loraleya_custom_order_remove_quick_edit( $actions, $post ) {
    if ( $post instanceof WP_Post && 'll_custom_request' === $post->post_type ) {
        unset( $actions['inline hide-if-no-js'] );
        if ( ! current_user_can( 'manage_options' ) ) {
            unset( $actions['trash'], $actions['delete'] );
        }
    }
    return $actions;
}
add_filter( 'post_row_actions', 'loraleya_custom_order_remove_quick_edit', 10, 2 );

function loraleya_custom_order_limit_bulk_actions( $actions ) {
    if ( ! current_user_can( 'manage_options' ) ) {
        unset( $actions['trash'], $actions['delete'] );
    }
    return $actions;
}
add_filter( 'bulk_actions-edit-ll_custom_request', 'loraleya_custom_order_limit_bulk_actions' );

function loraleya_custom_order_add_request_metaboxes() {
    add_meta_box( 'll_custom_request_details', 'Заявка и согласование', 'loraleya_custom_order_render_request_metabox', 'll_custom_request', 'normal', 'high' );
    add_meta_box( 'll_custom_request_conversion', 'WooCommerce-заказ', 'loraleya_custom_order_render_conversion_metabox', 'll_custom_request', 'side', 'high' );
    add_meta_box( 'll_custom_request_history', 'История заявки', 'loraleya_custom_order_render_history_metabox', 'll_custom_request', 'normal', 'default' );
}
add_action( 'add_meta_boxes_ll_custom_request', 'loraleya_custom_order_add_request_metaboxes' );

function loraleya_custom_order_render_request_metabox( $post ) {
    wp_nonce_field( 'loraleya_save_custom_request', 'loraleya_custom_request_nonce' );
    $statuses = loraleya_custom_order_statuses();
    $services = loraleya_custom_order_delivery_services();
    $status   = loraleya_custom_order_meta( $post->ID, '_ll_request_status', 'new' );
    $service  = loraleya_custom_order_meta( $post->ID, '_ll_delivery_service' );
    $mode     = loraleya_custom_order_meta( $post->ID, '_ll_delivery_mode' );
    $currency = loraleya_custom_order_meta( $post->ID, '_ll_request_currency', function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '' );
    $order_id = absint( get_post_meta( $post->ID, '_ll_wc_order_id', true ) );
    $linked_order = $order_id && function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : false;
    $is_converted = $linked_order instanceof WC_Order && 'yes' === $linked_order->get_meta( '_ll_custom_conversion_complete' );
    ?>
    <style>
        .ll-request-fields{border:0;padding:0;margin:0;min-width:0}.ll-request-grid{display:grid;grid-template-columns:repeat(2,minmax(240px,1fr));gap:16px}.ll-request-grid .wide{grid-column:1/-1}.ll-request-grid label{display:block;font-weight:600;margin-bottom:4px}.ll-request-grid input,.ll-request-grid select,.ll-request-grid textarea{width:100%}.ll-request-section{margin:20px 0 10px;padding-top:14px;border-top:1px solid #ddd}.ll-request-section:first-of-type{border-top:0;margin-top:0}.ll-request-help{color:#646970;font-size:12px}.ll-request-converted{padding:12px 14px;border-left:4px solid #2271b1;background:#f0f6fc}@media(max-width:782px){.ll-request-grid{grid-template-columns:1fr}}
    </style>
    <?php if ( $is_converted ) : ?>
        <p class="ll-request-converted"><strong>Создан WooCommerce-заказ №<?php echo esc_html( $linked_order->get_order_number() ); ?>.</strong> Дальнейшие изменения заказа выполняются в WooCommerce. <a href="<?php echo esc_url( $linked_order->get_edit_order_url() ); ?>">Открыть заказ №<?php echo esc_html( $linked_order->get_order_number() ); ?></a></p>
    <?php endif; ?>
    <fieldset class="ll-request-fields" <?php disabled( $is_converted ); ?>>
    <h3 class="ll-request-section">Клиент</h3>
    <div class="ll-request-grid">
        <p><label for="ll_customer_name">Имя</label><input id="ll_customer_name" name="ll_request[customer_name]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_customer_name' ) ); ?>"></p>
        <p><label for="ll_phone">Телефон</label><input id="ll_phone" name="ll_request[phone]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_phone' ) ); ?>"></p>
        <p><label for="ll_email">Email</label><input type="email" id="ll_email" name="ll_request[email]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_email' ) ); ?>"></p>
        <p><label for="ll_status">Статус</label><select id="ll_status" name="ll_request[status]" <?php disabled( (bool) $linked_order ); ?>>
            <?php foreach ( $statuses as $key => $label ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
        </select><?php if ( $linked_order ) : ?><input type="hidden" name="ll_request[status]" value="converted"><?php endif; ?></p>
    </div>

    <h3 class="ll-request-section">Параметры</h3>
    <div class="ll-request-grid">
        <p><label for="ll_shape">Форма стола</label><input id="ll_shape" name="ll_request[shape]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_shape' ) ); ?>"></p>
        <p><label for="ll_color">Цвет</label><input id="ll_color" name="ll_request[color]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_color' ) ); ?>"></p>
        <p><label for="ll_length">Длина, см</label><input type="number" min="0" id="ll_length" name="ll_request[length]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_length' ) ); ?>"></p>
        <p><label for="ll_width">Ширина, см</label><input type="number" min="0" id="ll_width" name="ll_request[width]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_width' ) ); ?>"></p>
        <p><label for="ll_persons">Количество персон</label><input type="number" min="0" id="ll_persons" name="ll_request[persons]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_persons' ) ); ?>"></p>
        <p class="wide"><label for="ll_items_summary">Комплектация</label><textarea id="ll_items_summary" rows="3" name="ll_request[items_summary]"><?php echo esc_textarea( loraleya_custom_order_meta( $post->ID, '_ll_items_summary' ) ); ?></textarea></p>
        <p class="wide"><label for="ll_options">Дополнительные опции</label><textarea id="ll_options" rows="2" name="ll_request[options]"><?php echo esc_textarea( loraleya_custom_order_meta( $post->ID, '_ll_options' ) ); ?></textarea></p>
        <p class="wide"><label for="ll_customer_notes">Комментарий клиента</label><textarea id="ll_customer_notes" rows="4" name="ll_request[customer_notes]"><?php echo esc_textarea( loraleya_custom_order_meta( $post->ID, '_ll_customer_notes' ) ); ?></textarea></p>
    </div>

    <h3 class="ll-request-section">Коммерческие условия и доставка</h3>
    <div class="ll-request-grid">
        <p><label for="ll_products_total">Согласованная стоимость изделий (<?php echo esc_html( $currency ); ?>)</label><input type="text" inputmode="decimal" id="ll_products_total" name="ll_request[products_total]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_agreed_products_total' ) ); ?>"></p>
        <p><label for="ll_delivery_cost">Стоимость доставки (<?php echo esc_html( $currency ); ?>)</label><input type="text" inputmode="decimal" id="ll_delivery_cost" name="ll_request[delivery_cost]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_delivery_cost' ) ); ?>"><span class="ll-request-help">Можно указать 0.</span></p>
        <p><label for="ll_delivery_service">Сервис доставки</label><select id="ll_delivery_service" name="ll_request[delivery_service]"><option value="">— Выберите —</option>
            <?php foreach ( $services as $key => $label ) : ?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( $service, $key ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?>
        </select></p>
        <p><label for="ll_delivery_mode">Способ получения</label><select id="ll_delivery_mode" name="ll_request[delivery_mode]"><option value="">— Выберите —</option><option value="pvz" <?php selected( $mode, 'pvz' ); ?>>ПВЗ</option><option value="courier" <?php selected( $mode, 'courier' ); ?>>Курьер</option></select></p>
        <p><label for="ll_delivery_state">Регион</label><input id="ll_delivery_state" name="ll_request[delivery_state]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_delivery_state' ) ); ?>"></p>
        <p><label for="ll_delivery_city">Город</label><input id="ll_delivery_city" name="ll_request[delivery_city]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_delivery_city' ) ); ?>"></p>
        <p class="wide"><label for="ll_pickup_address">ПВЗ</label><input id="ll_pickup_address" name="ll_request[pickup_address]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_pickup_address' ) ); ?>"></p>
        <p class="wide"><label for="ll_fivepost_point_id">Код / ID ПВЗ 5Post</label><input id="ll_fivepost_point_id" name="ll_request[fivepost_point_id]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_request_fivepost_point_id' ) ); ?>"><span class="ll-request-help">Обязателен только для 5Post.</span></p>
        <p class="wide"><label for="ll_delivery_address_1">Адрес</label><input id="ll_delivery_address_1" name="ll_request[delivery_address_1]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_delivery_address_1' ) ); ?>"></p>
        <p><label for="ll_delivery_address_2">Квартира / офис</label><input id="ll_delivery_address_2" name="ll_request[delivery_address_2]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_delivery_address_2' ) ); ?>"></p>
        <p><label for="ll_delivery_postcode">Индекс</label><input id="ll_delivery_postcode" name="ll_request[delivery_postcode]" value="<?php echo esc_attr( loraleya_custom_order_meta( $post->ID, '_ll_delivery_postcode' ) ); ?>"></p>
    </div>

    <h3 class="ll-request-section">Внутренняя заметка</h3>
    <div class="ll-request-grid"><p class="wide"><textarea rows="5" name="ll_request[internal_note]"><?php echo esc_textarea( loraleya_custom_order_meta( $post->ID, '_ll_internal_note' ) ); ?></textarea><span class="ll-request-help">Покупателю не показывается.</span></p></div>
    </fieldset>
    <?php
}

function loraleya_custom_order_render_conversion_metabox( $post ) {
    $order_id = absint( get_post_meta( $post->ID, '_ll_wc_order_id', true ) );
    $order    = $order_id && function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : false;

    if ( $order instanceof WC_Order && 'yes' === $order->get_meta( '_ll_custom_conversion_complete' ) ) {
        echo '<p><strong>Заказ WooCommerce №' . esc_html( $order->get_order_number() ) . '</strong></p>';
        echo '<p><a class="button button-primary" href="' . esc_url( $order->get_edit_order_url() ) . '">Открыть заказ</a></p>';
        return;
    }

    if ( $order instanceof WC_Order ) {
        echo '<p><strong>Найден незавершённый WooCommerce-заказ.</strong> При повторной конвертации он будет безопасно проверен и удалён перед созданием нового.</p>';
    }

    if ( 'cancelled' === get_post_meta( $post->ID, '_ll_request_status', true ) ) {
        echo '<p>Отменённую заявку нельзя конвертировать.</p>';
        return;
    }

    echo '<p>Перед созданием заказа сохраните согласованные данные заявки.</p>';
    echo '<input type="hidden" name="request_id" value="' . esc_attr( $post->ID ) . '">';
    wp_nonce_field( 'loraleya_convert_custom_request_' . $post->ID, 'loraleya_conversion_nonce' );
    echo '<p><button type="submit" name="action" value="loraleya_convert_custom_request" class="button button-primary" formaction="' . esc_url( admin_url( 'admin-post.php' ) ) . '" formmethod="post" formnovalidate>Создать заказ WooCommerce</button></p>';
}

function loraleya_custom_order_render_history_metabox( $post ) {
    $history = get_post_meta( $post->ID, '_ll_history', true );
    $history = is_array( $history ) ? array_reverse( $history ) : array();

    if ( ! $history ) {
        echo '<p>История пока пуста.</p>';
        return;
    }

    echo '<ul class="ll-request-history">';
    foreach ( $history as $entry ) {
        $time = isset( $entry['time'] ) ? $entry['time'] : '';
        $user = isset( $entry['user_name'] ) ? $entry['user_name'] : 'Сайт';
        $text = isset( $entry['action'] ) ? $entry['action'] : '';
        echo '<li><strong>' . esc_html( $time ) . '</strong> — ' . esc_html( $user ) . ': ' . esc_html( $text ) . '</li>';
    }
    echo '</ul>';
}

function loraleya_custom_order_decimal( $value ) {
    $normalized = preg_replace( '/[\s\x{00A0}\x{202F}]+/u', '', trim( (string) $value ) );
    if ( '' === $normalized ) {
        return '';
    }

    $normalized = str_replace( ',', '.', $normalized );
    if ( ! preg_match( '/^\d+(?:\.\d+)?$/', $normalized ) || ! function_exists( 'wc_format_decimal' ) ) {
        return '';
    }

    $decimals = function_exists( 'wc_get_price_decimals' ) ? wc_get_price_decimals() : 2;
    return wc_format_decimal( $normalized, $decimals );
}

/** Convert a WooCommerce decimal string to minor units without an intermediate float. */
function loraleya_custom_order_minor_units( $value ) {
    $decimal = loraleya_custom_order_decimal( $value );
    if ( '' === $decimal ) {
        return null;
    }

    $decimals = function_exists( 'wc_get_price_decimals' ) ? wc_get_price_decimals() : 2;
    $parts    = explode( '.', $decimal, 2 );
    $whole    = ltrim( $parts[0], '0' );
    $whole    = '' === $whole ? '0' : $whole;
    $fraction = isset( $parts[1] ) ? substr( str_pad( $parts[1], $decimals, '0' ), 0, $decimals ) : str_repeat( '0', $decimals );
    $minor    = ltrim( $whole . $fraction, '0' );
    $minor    = '' === $minor ? '0' : $minor;

    if ( strlen( $minor ) > strlen( (string) PHP_INT_MAX ) || ( strlen( $minor ) === strlen( (string) PHP_INT_MAX ) && strcmp( $minor, (string) PHP_INT_MAX ) > 0 ) ) {
        return null;
    }

    return (int) $minor;
}

function loraleya_custom_order_save_request( $post_id, $post ) {
    static $saving = false;

    if (
        $saving
        || ! $post instanceof WP_Post
        || 'll_custom_request' !== $post->post_type
        || wp_is_post_autosave( $post_id )
        || wp_is_post_revision( $post_id )
        || ! current_user_can( 'manage_woocommerce' )
        || empty( $_POST['loraleya_custom_request_nonce'] )
        || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['loraleya_custom_request_nonce'] ) ), 'loraleya_save_custom_request' )
        || empty( $_POST['ll_request'] )
        || ! is_array( $_POST['ll_request'] )
    ) {
        return;
    }

    $linked_order_id = absint( get_post_meta( $post_id, '_ll_wc_order_id', true ) );
    if ( $linked_order_id && function_exists( 'wc_get_order' ) ) {
        $linked_order = wc_get_order( $linked_order_id );
        if ( $linked_order instanceof WC_Order && 'yes' === $linked_order->get_meta( '_ll_custom_conversion_complete' ) ) {
            return;
        }
    }

    $saving = true;
    $input  = wp_unslash( $_POST['ll_request'] );

    $statuses = loraleya_custom_order_statuses();
    $services = loraleya_custom_order_delivery_services();
    $old      = array(
        'customer_name'     => loraleya_custom_order_meta( $post_id, '_ll_customer_name' ),
        'phone'             => loraleya_custom_order_meta( $post_id, '_ll_phone' ),
        'email'             => loraleya_custom_order_meta( $post_id, '_ll_email' ),
        'status'            => loraleya_custom_order_meta( $post_id, '_ll_request_status', 'new' ),
        'shape'             => loraleya_custom_order_meta( $post_id, '_ll_shape' ),
        'length'            => loraleya_custom_order_meta( $post_id, '_ll_length' ),
        'width'             => loraleya_custom_order_meta( $post_id, '_ll_width' ),
        'persons'           => loraleya_custom_order_meta( $post_id, '_ll_persons' ),
        'color'             => loraleya_custom_order_meta( $post_id, '_ll_color' ),
        'items_summary'     => loraleya_custom_order_meta( $post_id, '_ll_items_summary' ),
        'options'           => loraleya_custom_order_meta( $post_id, '_ll_options' ),
        'customer_notes'    => loraleya_custom_order_meta( $post_id, '_ll_customer_notes' ),
        'products_total'    => loraleya_custom_order_meta( $post_id, '_ll_agreed_products_total' ),
        'delivery_service'  => loraleya_custom_order_meta( $post_id, '_ll_delivery_service' ),
        'delivery_cost'     => loraleya_custom_order_meta( $post_id, '_ll_delivery_cost' ),
        'delivery_state'    => loraleya_custom_order_meta( $post_id, '_ll_delivery_state' ),
        'delivery_city'     => loraleya_custom_order_meta( $post_id, '_ll_delivery_city' ),
        'delivery_mode'     => loraleya_custom_order_meta( $post_id, '_ll_delivery_mode' ),
        'pickup_address'    => loraleya_custom_order_meta( $post_id, '_ll_pickup_address' ),
        'fivepost_point_id' => loraleya_custom_order_meta( $post_id, '_ll_request_fivepost_point_id' ),
        'delivery_address_1'=> loraleya_custom_order_meta( $post_id, '_ll_delivery_address_1' ),
        'delivery_address_2'=> loraleya_custom_order_meta( $post_id, '_ll_delivery_address_2' ),
        'delivery_postcode' => loraleya_custom_order_meta( $post_id, '_ll_delivery_postcode' ),
        'internal_note'     => loraleya_custom_order_meta( $post_id, '_ll_internal_note' ),
    );

    $phone_input = isset( $input['phone'] ) ? sanitize_text_field( $input['phone'] ) : '';
    $phone       = function_exists( 'loraleya_normalize_custom_order_phone' ) ? loraleya_normalize_custom_order_phone( $phone_input ) : $phone_input;
    $email       = isset( $input['email'] ) ? sanitize_email( $input['email'] ) : '';
    $status      = isset( $input['status'] ) ? sanitize_key( $input['status'] ) : $old['status'];
    $service     = isset( $input['delivery_service'] ) ? sanitize_key( $input['delivery_service'] ) : '';
    $mode        = isset( $input['delivery_mode'] ) ? sanitize_key( $input['delivery_mode'] ) : '';

    if ( ! isset( $statuses[ $status ] ) || ( 'converted' === $status && ! get_post_meta( $post_id, '_ll_wc_order_id', true ) ) ) {
        $status = $old['status'];
    }
    $linked_order_id = absint( get_post_meta( $post_id, '_ll_wc_order_id', true ) );
    if ( $linked_order_id && function_exists( 'wc_get_order' ) ) {
        $linked_order = wc_get_order( $linked_order_id );
        if ( $linked_order instanceof WC_Order && 'yes' === $linked_order->get_meta( '_ll_custom_conversion_complete' ) ) {
            $status = 'converted';
        }
    }
    if ( ! isset( $services[ $service ] ) ) {
        $service = '';
    }
    if ( ! in_array( $mode, array( 'pvz', 'courier' ), true ) ) {
        $mode = '';
    }

    $new = array(
        'customer_name'      => isset( $input['customer_name'] ) ? sanitize_text_field( $input['customer_name'] ) : '',
        'phone'              => $phone,
        'email'              => $email,
        'status'             => $status,
        'shape'              => isset( $input['shape'] ) ? sanitize_text_field( $input['shape'] ) : '',
        'length'             => isset( $input['length'] ) ? absint( $input['length'] ) : 0,
        'width'              => isset( $input['width'] ) ? absint( $input['width'] ) : 0,
        'persons'            => isset( $input['persons'] ) ? absint( $input['persons'] ) : 0,
        'color'              => isset( $input['color'] ) ? sanitize_text_field( $input['color'] ) : '',
        'items_summary'      => isset( $input['items_summary'] ) ? sanitize_textarea_field( $input['items_summary'] ) : '',
        'options'            => isset( $input['options'] ) ? sanitize_textarea_field( $input['options'] ) : '',
        'customer_notes'     => isset( $input['customer_notes'] ) ? sanitize_textarea_field( $input['customer_notes'] ) : '',
        'products_total'     => loraleya_custom_order_decimal( isset( $input['products_total'] ) ? $input['products_total'] : '' ),
        'delivery_service'   => $service,
        'delivery_cost'      => loraleya_custom_order_decimal( isset( $input['delivery_cost'] ) ? $input['delivery_cost'] : '' ),
        'delivery_state'     => isset( $input['delivery_state'] ) ? sanitize_text_field( $input['delivery_state'] ) : '',
        'delivery_city'      => isset( $input['delivery_city'] ) ? sanitize_text_field( $input['delivery_city'] ) : '',
        'delivery_mode'      => $mode,
        'pickup_address'     => isset( $input['pickup_address'] ) ? sanitize_text_field( $input['pickup_address'] ) : '',
        'fivepost_point_id'  => isset( $input['fivepost_point_id'] ) ? sanitize_text_field( $input['fivepost_point_id'] ) : '',
        'delivery_address_1' => isset( $input['delivery_address_1'] ) ? sanitize_text_field( $input['delivery_address_1'] ) : '',
        'delivery_address_2' => isset( $input['delivery_address_2'] ) ? sanitize_text_field( $input['delivery_address_2'] ) : '',
        'delivery_postcode'  => isset( $input['delivery_postcode'] ) ? sanitize_text_field( $input['delivery_postcode'] ) : '',
        'internal_note'      => isset( $input['internal_note'] ) ? sanitize_textarea_field( $input['internal_note'] ) : '',
    );

    $meta_map = array(
        'customer_name'      => '_ll_customer_name',
        'phone'              => '_ll_phone',
        'email'              => '_ll_email',
        'status'             => '_ll_request_status',
        'shape'              => '_ll_shape',
        'length'             => '_ll_length',
        'width'              => '_ll_width',
        'persons'            => '_ll_persons',
        'color'              => '_ll_color',
        'items_summary'      => '_ll_items_summary',
        'options'            => '_ll_options',
        'customer_notes'     => '_ll_customer_notes',
        'products_total'     => '_ll_agreed_products_total',
        'delivery_service'   => '_ll_delivery_service',
        'delivery_cost'      => '_ll_delivery_cost',
        'delivery_state'     => '_ll_delivery_state',
        'delivery_city'      => '_ll_delivery_city',
        'delivery_mode'      => '_ll_delivery_mode',
        'pickup_address'     => '_ll_pickup_address',
        'fivepost_point_id'  => '_ll_request_fivepost_point_id',
        'delivery_address_1' => '_ll_delivery_address_1',
        'delivery_address_2' => '_ll_delivery_address_2',
        'delivery_postcode'  => '_ll_delivery_postcode',
        'internal_note'      => '_ll_internal_note',
    );

    foreach ( $meta_map as $field => $meta_key ) {
        update_post_meta( $post_id, $meta_key, $new[ $field ] );
    }

    wp_update_post( array(
        'ID'         => $post_id,
        'post_title' => loraleya_custom_order_number( $post_id ) . ' — ' . $new['customer_name'],
    ) );

    if ( $old['status'] !== $new['status'] ) {
        $old_status = isset( $statuses[ $old['status'] ] ) ? $statuses[ $old['status'] ] : $old['status'];
        $new_status = isset( $statuses[ $new['status'] ] ) ? $statuses[ $new['status'] ] : $new['status'];
        loraleya_custom_order_add_history( $post_id, 'Статус изменён: ' . $old_status . ' → ' . $new_status );
    }

    $parameter_labels = array(
        'customer_name'  => 'клиент',
        'phone'          => 'телефон',
        'email'          => 'email',
        'shape'          => 'форма',
        'length'         => 'длина',
        'width'          => 'ширина',
        'persons'        => 'количество персон',
        'color'          => 'цвет',
        'items_summary'  => 'комплектация',
        'options'        => 'дополнительные опции',
        'customer_notes' => 'комментарий клиента',
    );
    $changed_parameters = array();
    foreach ( $parameter_labels as $field => $label ) {
        if ( (string) $old[ $field ] !== (string) $new[ $field ] ) {
            $changed_parameters[] = $label;
        }
    }
    if ( $changed_parameters ) {
        loraleya_custom_order_add_history( $post_id, 'Изменены параметры заявки: ' . implode( ', ', $changed_parameters ) );
    }

    if ( (string) $old['products_total'] !== (string) $new['products_total'] ) {
        $currency = loraleya_custom_order_meta( $post_id, '_ll_request_currency', function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '' );
        $price = '' === $new['products_total'] ? 'не указана' : wp_strip_all_tags( wc_price( $new['products_total'], array( 'currency' => $currency ) ) );
        loraleya_custom_order_add_history( $post_id, 'Согласованная стоимость изделий: ' . $price );
    }

    $delivery_fields = array( 'delivery_service', 'delivery_cost', 'delivery_state', 'delivery_city', 'delivery_mode', 'pickup_address', 'fivepost_point_id', 'delivery_address_1', 'delivery_address_2', 'delivery_postcode' );
    foreach ( $delivery_fields as $field ) {
        if ( (string) $old[ $field ] !== (string) $new[ $field ] ) {
            loraleya_custom_order_add_history( $post_id, 'Изменены условия доставки' );
            break;
        }
    }

    if ( (string) $old['internal_note'] !== (string) $new['internal_note'] ) {
        loraleya_custom_order_add_history( $post_id, 'Изменена внутренняя заметка' );
    }

    $saving = false;
}
add_action( 'save_post_ll_custom_request', 'loraleya_custom_order_save_request', 20, 2 );

function loraleya_custom_order_request_data( $request_id ) {
    $length = absint( get_post_meta( $request_id, '_ll_length', true ) );
    $width  = absint( get_post_meta( $request_id, '_ll_width', true ) );

    return array(
        'request_id'        => $request_id,
        'request_number'    => loraleya_custom_order_number( $request_id ),
        'status'            => loraleya_custom_order_meta( $request_id, '_ll_request_status', 'new' ),
        'customer_name'     => loraleya_custom_order_meta( $request_id, '_ll_customer_name' ),
        'phone'             => loraleya_custom_order_meta( $request_id, '_ll_phone' ),
        'email'             => loraleya_custom_order_meta( $request_id, '_ll_email' ),
        'shape'             => loraleya_custom_order_meta( $request_id, '_ll_shape' ),
        'length'            => $length,
        'width'             => $width,
        'size'              => $length && $width ? $length . ' × ' . $width . ' см' : '',
        'persons'           => absint( get_post_meta( $request_id, '_ll_persons', true ) ),
        'color'             => loraleya_custom_order_meta( $request_id, '_ll_color' ),
        'items_summary'     => loraleya_custom_order_meta( $request_id, '_ll_items_summary' ),
        'options'           => loraleya_custom_order_meta( $request_id, '_ll_options' ),
        'customer_notes'    => loraleya_custom_order_meta( $request_id, '_ll_customer_notes' ),
        'internal_note'     => loraleya_custom_order_meta( $request_id, '_ll_internal_note' ),
        'currency'          => strtoupper( loraleya_custom_order_meta( $request_id, '_ll_request_currency', function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '' ) ),
        'products_total'    => loraleya_custom_order_meta( $request_id, '_ll_agreed_products_total' ),
        'delivery_service'  => loraleya_custom_order_meta( $request_id, '_ll_delivery_service' ),
        'delivery_cost'     => loraleya_custom_order_meta( $request_id, '_ll_delivery_cost' ),
        'delivery_state'    => loraleya_custom_order_meta( $request_id, '_ll_delivery_state' ),
        'delivery_city'     => loraleya_custom_order_meta( $request_id, '_ll_delivery_city' ),
        'delivery_mode'     => loraleya_custom_order_meta( $request_id, '_ll_delivery_mode' ),
        'pickup_address'    => loraleya_custom_order_meta( $request_id, '_ll_pickup_address' ),
        'fivepost_point_id' => loraleya_custom_order_meta( $request_id, '_ll_request_fivepost_point_id' ),
        'delivery_address_1'=> loraleya_custom_order_meta( $request_id, '_ll_delivery_address_1' ),
        'delivery_address_2'=> loraleya_custom_order_meta( $request_id, '_ll_delivery_address_2' ),
        'delivery_postcode' => loraleya_custom_order_meta( $request_id, '_ll_delivery_postcode' ),
        'consent_time'      => loraleya_custom_order_meta( $request_id, '_ll_privacy_consent_time' ),
    );
}

function loraleya_custom_order_validate_conversion( $request_id ) {
    $post = get_post( $request_id );
    if ( ! $post || 'll_custom_request' !== $post->post_type ) {
        return new WP_Error( 'invalid_request', 'Заявка не найдена.' );
    }
    if ( ! function_exists( 'wc_create_order' ) || ! class_exists( 'WC_Order_Item_Product' ) || ! class_exists( 'WC_Order_Item_Shipping' ) ) {
        return new WP_Error( 'woocommerce_unavailable', 'WooCommerce недоступен.' );
    }

    $data = loraleya_custom_order_request_data( $request_id );
    if ( 'agreed' !== $data['status'] ) {
        return new WP_Error( 'request_not_agreed', 'Создать новый заказ можно только из согласованной заявки.' );
    }
    if ( '' === trim( $data['customer_name'] ) ) {
        return new WP_Error( 'missing_name', 'Заполните имя клиента.' );
    }
    $phone = function_exists( 'loraleya_normalize_custom_order_phone' ) ? loraleya_normalize_custom_order_phone( $data['phone'] ) : '';
    if ( ! $phone ) {
        return new WP_Error( 'invalid_phone', 'Проверьте телефон клиента.' );
    }
    if ( ! is_email( $data['email'] ) ) {
        return new WP_Error( 'invalid_email', 'Проверьте email клиента.' );
    }
    $data['products_total'] = loraleya_custom_order_decimal( $data['products_total'] );
    $products_minor         = loraleya_custom_order_minor_units( $data['products_total'] );
    if ( null === $products_minor || $products_minor <= 0 ) {
        return new WP_Error( 'missing_price', 'Укажите согласованную стоимость изделий больше 0.' );
    }
    if ( ! preg_match( '/^[A-Z]{3}$/', $data['currency'] ) ) {
        return new WP_Error( 'invalid_currency', 'Не удалось определить валюту заявки.' );
    }
    $services = loraleya_custom_order_delivery_services();
    if ( ! isset( $services[ $data['delivery_service'] ] ) ) {
        return new WP_Error( 'missing_delivery_service', 'Выберите сервис доставки.' );
    }
    if ( ! $data['delivery_state'] || ! $data['delivery_city'] ) {
        return new WP_Error( 'missing_delivery_location', 'Укажите регион и город доставки.' );
    }
    if ( ! in_array( $data['delivery_mode'], array( 'pvz', 'courier' ), true ) ) {
        return new WP_Error( 'missing_delivery_mode', 'Выберите ПВЗ или курьерскую доставку.' );
    }
    if ( 'fivepost' === $data['delivery_service'] && 'pvz' !== $data['delivery_mode'] ) {
        return new WP_Error( 'invalid_fivepost_mode', 'Для 5Post выберите получение в ПВЗ.' );
    }
    if ( 'pvz' === $data['delivery_mode'] && ! $data['pickup_address'] ) {
        return new WP_Error( 'missing_pickup', 'Укажите пункт выдачи.' );
    }
    if ( 'fivepost' === $data['delivery_service'] && ! $data['fivepost_point_id'] ) {
        return new WP_Error( 'missing_fivepost_point_id', 'Укажите код или ID ПВЗ 5Post.' );
    }
    if ( 'courier' === $data['delivery_mode'] && ( ! $data['delivery_address_1'] || ! $data['delivery_postcode'] ) ) {
        return new WP_Error( 'missing_courier_address', 'Для курьерской доставки укажите адрес и индекс.' );
    }

    $data['phone'] = $phone;
    return $data;
}

function loraleya_custom_order_shipping_method_data( $service ) {
    $methods = array(
        'fivepost' => array(
            'title'   => '5Post',
            'method'  => 'fivepost_shipping_method',
            'rate_id' => 'fivepost_shipping_method:custom_request',
        ),
        'cdek' => array(
            'title'   => 'СДЭК',
            'method'  => 'll_cdek',
            'rate_id' => 'll_cdek:manager',
        ),
        'yandex' => array(
            'title'   => 'Яндекс Доставка',
            'method'  => 'll_yandex',
            'rate_id' => 'll_yandex:manager',
        ),
    );

    return isset( $methods[ $service ] ) ? $methods[ $service ] : array();
}

function loraleya_custom_order_add_visible_item_meta( $item, $data ) {
    $fields = array(
        'Форма стола'          => $data['shape'],
        'Размер'               => $data['size'],
        'Количество персон'    => $data['persons'],
        'Цвет'                 => $data['color'],
        'Комплектация'         => $data['items_summary'],
        'Дополнительные опции' => $data['options'],
    );

    foreach ( $fields as $label => $value ) {
        if ( '' !== trim( (string) $value ) && 0 !== $value ) {
            $item->add_meta_data( $label, $value, true );
        }
    }
    $item->add_meta_data( '_ll_individual_line_item', 'yes', true );
}

function loraleya_custom_order_restore_order_link( $request_id, $order ) {
    if ( ! $order instanceof WC_Order || 'yes' !== $order->get_meta( '_ll_custom_conversion_complete' ) ) {
        return new WP_Error( 'incomplete_order', 'Найден незавершённый WooCommerce-заказ.' );
    }

    $old_order_id = absint( get_post_meta( $request_id, '_ll_wc_order_id', true ) );
    $old_status   = get_post_meta( $request_id, '_ll_request_status', true );
    update_post_meta( $request_id, '_ll_wc_order_id', $order->get_id() );
    update_post_meta( $request_id, '_ll_request_status', 'converted' );

    if (
        absint( get_post_meta( $request_id, '_ll_wc_order_id', true ) ) !== $order->get_id()
        || 'converted' !== get_post_meta( $request_id, '_ll_request_status', true )
    ) {
        return new WP_Error( 'conversion_link_failed', 'Не удалось восстановить связь заявки с WooCommerce-заказом.' );
    }

    if ( $old_order_id !== $order->get_id() || 'converted' !== $old_status ) {
        loraleya_custom_order_add_history( $request_id, 'Связь с WooCommerce-заказом №' . $order->get_order_number() . ' восстановлена' );
    }

    return $order->get_id();
}

function loraleya_custom_order_find_linked_orders( $request_id ) {
    $orders    = array();
    $linked_id = absint( get_post_meta( $request_id, '_ll_wc_order_id', true ) );
    if ( $linked_id ) {
        $linked = wc_get_order( $linked_id );
        if ( $linked instanceof WC_Order ) {
            $orders[ $linked->get_id() ] = $linked;
        }
    }

    $found = wc_get_orders( array(
        'type'       => 'shop_order',
        'limit'      => -1,
        'return'     => 'objects',
        'orderby'    => 'ID',
        'order'      => 'ASC',
        'meta_query' => array(
            array(
                'key'     => '_ll_custom_request_id',
                'value'   => (string) absint( $request_id ),
                'compare' => '=',
            ),
        ),
    ) );
    foreach ( is_array( $found ) ? $found : array() as $order ) {
        if ( $order instanceof WC_Order ) {
            $orders[ $order->get_id() ] = $order;
        }
    }

    ksort( $orders, SORT_NUMERIC );
    return array_values( $orders );
}

function loraleya_custom_order_delete_incomplete_order( $order ) {
    if ( ! $order instanceof WC_Order || ! $order->get_id() ) {
        return true;
    }
    if ( 'yes' === $order->get_meta( '_ll_custom_conversion_complete' ) ) {
        return new WP_Error( 'complete_order_delete_refused', 'Завершённый заказ не удалён.' );
    }

    $order_id = $order->get_id();
    try {
        $order->delete( true );
    } catch ( Throwable $exception ) {
        error_log( '[LoraLeya] Failed to delete incomplete individual order #' . $order_id . '.' );
        return new WP_Error( 'incomplete_order_cleanup_failed', 'Не удалось удалить незавершённый заказ. Новый заказ не создан.' );
    }

    if ( wc_get_order( $order_id ) ) {
        error_log( '[LoraLeya] Incomplete individual order #' . $order_id . ' still exists after delete.' );
        return new WP_Error( 'incomplete_order_cleanup_failed', 'Не удалось удалить незавершённый заказ. Новый заказ не создан.' );
    }

    return true;
}

function loraleya_custom_order_reconcile_order( $request_id ) {
    $orders         = loraleya_custom_order_find_linked_orders( $request_id );
    $complete_order = false;

    foreach ( $orders as $order ) {
        if ( 'yes' === $order->get_meta( '_ll_custom_conversion_complete' ) ) {
            if ( ! $complete_order ) {
                $complete_order = $order;
            } else {
                error_log( '[LoraLeya] Multiple complete WooCommerce orders found for custom request #' . absint( $request_id ) . '.' );
            }
            continue;
        }

        $deleted = loraleya_custom_order_delete_incomplete_order( $order );
        if ( is_wp_error( $deleted ) ) {
            return $deleted;
        }
    }

    if ( $complete_order ) {
        return loraleya_custom_order_restore_order_link( $request_id, $complete_order );
    }

    delete_post_meta( $request_id, '_ll_wc_order_id' );
    return 0;
}

function loraleya_custom_order_convert_to_order( $request_id ) {
    $request = get_post( $request_id );
    if ( ! $request || 'll_custom_request' !== $request->post_type ) {
        return new WP_Error( 'invalid_request', 'Заявка не найдена.' );
    }
    if ( ! function_exists( 'wc_get_order' ) || ! function_exists( 'wc_get_orders' ) ) {
        return new WP_Error( 'woocommerce_unavailable', 'WooCommerce недоступен.' );
    }

    $existing_id = absint( get_post_meta( $request_id, '_ll_wc_order_id', true ) );
    if ( $existing_id && function_exists( 'wc_get_order' ) ) {
        $existing_order = wc_get_order( $existing_id );
        if ( $existing_order instanceof WC_Order && 'yes' === $existing_order->get_meta( '_ll_custom_conversion_complete' ) ) {
            return loraleya_custom_order_restore_order_link( $request_id, $existing_order );
        }
    }

    $lock_name = 'll_custom_conversion_lock_' . absint( $request_id );
    $lock      = loraleya_custom_order_acquire_option_lock( $lock_name );
    if ( is_wp_error( $lock ) ) {
        return new WP_Error( 'conversion_locked', 'Создание заказа уже выполняется.' );
    }

    $order               = null;
    $conversion_complete = false;
    $old_status          = loraleya_custom_order_meta( $request_id, '_ll_request_status', 'new' );

    try {
        $reconciled = loraleya_custom_order_reconcile_order( $request_id );
        if ( is_wp_error( $reconciled ) ) {
            return $reconciled;
        }
        if ( $reconciled ) {
            return $reconciled;
        }

        $data = loraleya_custom_order_validate_conversion( $request_id );
        if ( is_wp_error( $data ) ) {
            return $data;
        }

        $user        = get_user_by( 'email', $data['email'] );
        $customer_id = $user instanceof WP_User ? $user->ID : 0;
        $order       = wc_create_order( array(
            'customer_id' => $customer_id,
            'status'      => 'pending',
        ) );
        if ( is_wp_error( $order ) ) {
            throw new Exception( $order->get_error_message() );
        }
        if ( ! $order instanceof WC_Order ) {
            throw new Exception( 'wc_create_order did not return WC_Order.' );
        }

        $order->update_meta_data( '_ll_individual_order', 'yes' );
        $order->update_meta_data( '_ll_custom_request_id', $request_id );
        $order->update_meta_data( '_ll_custom_request_number', $data['request_number'] );
        $order->update_meta_data( '_ll_custom_conversion_complete', 'no' );
        $order->save();
        if (
            'yes' !== $order->get_meta( '_ll_individual_order' )
            || absint( $order->get_meta( '_ll_custom_request_id' ) ) !== absint( $request_id )
            || 'no' !== $order->get_meta( '_ll_custom_conversion_complete' )
        ) {
            throw new Exception( 'Failed to mark incomplete individual order.' );
        }

        if ( method_exists( $order, 'set_created_via' ) ) {
            $order->set_created_via( 'll_custom_request' );
        }
        $order->set_customer_id( $customer_id );
        $order->set_billing_first_name( $data['customer_name'] );
        $order->set_billing_phone( $data['phone'] );
        $order->set_billing_email( $data['email'] );
        $order->set_billing_country( 'RU' );
        $order->set_billing_state( $data['delivery_state'] );
        $order->set_billing_city( $data['delivery_city'] );
        $order->set_billing_address_1( 'pvz' === $data['delivery_mode'] ? $data['pickup_address'] : $data['delivery_address_1'] );
        $order->set_billing_address_2( $data['delivery_address_2'] );
        $order->set_billing_postcode( $data['delivery_postcode'] );
        $order->set_currency( $data['currency'] );
        $order->set_payment_method( 'll_manager_confirmation' );
        $order->set_payment_method_title( 'Подтверждение заказа менеджером' );

        $order->update_meta_data( '_ll_manager_confirmation_required', 'yes' );
        $order->update_meta_data( '_ll_delivery_service', $data['delivery_service'] );
        $order->update_meta_data( '_ll_delivery_mode', $data['delivery_mode'] );
        $order->update_meta_data( '_ll_pickup_address', $data['pickup_address'] );
        $order->update_meta_data( '_ll_fivepost_point_id', 'fivepost' === $data['delivery_service'] ? $data['fivepost_point_id'] : '' );
        $order->update_meta_data( '_ll_privacy_consent', 'yes' );
        $order->update_meta_data( '_ll_privacy_consent_time', $data['consent_time'] );
        $order->update_meta_data( '_ll_preliminary_shipping_cost', 'fivepost' === $data['delivery_service'] ? 'informational' : 'manager' );
        $order->update_meta_data( '_ll_individual_shape', $data['shape'] );
        $order->update_meta_data( '_ll_individual_size', $data['size'] );
        $order->update_meta_data( '_ll_individual_persons', $data['persons'] );
        $order->update_meta_data( '_ll_individual_color', $data['color'] );
        $order->update_meta_data( '_ll_individual_items_summary', $data['items_summary'] );
        $order->update_meta_data( '_ll_individual_options', $data['options'] );
        $order->update_meta_data( '_ll_individual_customer_comment', $data['customer_notes'] );
        $order->update_meta_data( '_ll_individual_production_note', $data['internal_note'] );

        $method = loraleya_custom_order_shipping_method_data( $data['delivery_service'] );
        $order->update_meta_data( '_ll_delivery_rate_id', $method['rate_id'] );

        $product_item = new WC_Order_Item_Product();
        $product_item->set_name( 'Индивидуальный заказ — столовый текстиль' );
        $product_item->set_product_id( 0 );
        $product_item->set_variation_id( 0 );
        $product_item->set_quantity( 1 );
        $product_item->set_subtotal( $data['products_total'] );
        $product_item->set_total( $data['products_total'] );
        loraleya_custom_order_add_visible_item_meta( $product_item, $data );
        $order->add_item( $product_item );

        $shipping_item = new WC_Order_Item_Shipping();
        $shipping_item->set_method_title( $method['title'] );
        $shipping_item->set_method_id( $method['method'] );
        $shipping_item->set_total( '0' );
        $shipping_item->set_taxes( array() );
        $order->add_item( $shipping_item );

        $order->calculate_totals( false );
        $order->set_status( 'on-hold' );
        $order->save();

        $products_minor = loraleya_custom_order_minor_units( $data['products_total'] );
        $actual_minor   = loraleya_custom_order_minor_units( $order->get_total() );
        if (
            null === $products_minor
            || null === $actual_minor
            || $products_minor !== $actual_minor
        ) {
            throw new Exception( 'WooCommerce order total does not match the agreed products total.' );
        }

        $order->update_meta_data( '_ll_custom_conversion_complete', 'yes' );
        $order->save();
        if ( 'yes' !== $order->get_meta( '_ll_custom_conversion_complete' ) ) {
            throw new Exception( 'Failed to mark individual order conversion complete.' );
        }
        $conversion_complete = true;
        $order->add_order_note( 'Создан из индивидуальной заявки ' . $data['request_number'] . '.' );

        update_post_meta( $request_id, '_ll_wc_order_id', $order->get_id() );
        update_post_meta( $request_id, '_ll_request_status', 'converted' );
        if (
            absint( get_post_meta( $request_id, '_ll_wc_order_id', true ) ) !== $order->get_id()
            || 'converted' !== get_post_meta( $request_id, '_ll_request_status', true )
        ) {
            throw new Exception( 'Failed to link WooCommerce order to custom request.' );
        }

        $statuses = loraleya_custom_order_statuses();
        $from     = isset( $statuses[ $old_status ] ) ? $statuses[ $old_status ] : $old_status;
        loraleya_custom_order_add_history( $request_id, 'Статус изменён: ' . $from . ' → ' . $statuses['converted'] );
        loraleya_custom_order_add_history( $request_id, 'Создан WooCommerce-заказ №' . $order->get_order_number() );

        return $order->get_id();
    } catch ( Throwable $exception ) {
        if ( $order instanceof WC_Order && $order->get_id() && ! $conversion_complete ) {
            $deleted = loraleya_custom_order_delete_incomplete_order( $order );
            if ( is_wp_error( $deleted ) ) {
                error_log( '[LoraLeya] Partial individual order cleanup requires attention for request #' . absint( $request_id ) . '.' );
                return $deleted;
            }
        }
        if ( ! $conversion_complete ) {
            delete_post_meta( $request_id, '_ll_wc_order_id' );
            update_post_meta( $request_id, '_ll_request_status', $old_status );
        }
        error_log( '[LoraLeya] Individual request conversion failed: ' . $exception->getMessage() );
        return new WP_Error( 'conversion_failed', 'Не удалось полностью создать заказ. Техническая ошибка записана в журнал.' );
    } finally {
        loraleya_custom_order_release_option_lock( $lock_name, $lock );
    }
}

function loraleya_custom_order_convert_action() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( 'Недостаточно прав.' );
    }

    if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) ) {
        wp_die( 'Для создания заказа требуется POST-запрос.' );
    }

    $request_id = isset( $_POST['request_id'] ) ? absint( $_POST['request_id'] ) : 0;
    check_admin_referer( 'loraleya_convert_custom_request_' . $request_id, 'loraleya_conversion_nonce' );

    $result = loraleya_custom_order_convert_to_order( $request_id );
    $args   = array( 'post' => $request_id, 'action' => 'edit' );

    if ( is_wp_error( $result ) ) {
        $args['ll_custom_order_notice'] = $result->get_error_code();
    } else {
        $args['ll_custom_order_notice'] = 'converted';
        $args['ll_order_id']            = absint( $result );
    }

    wp_safe_redirect( add_query_arg( $args, admin_url( 'post.php' ) ) );
    exit;
}
add_action( 'admin_post_loraleya_convert_custom_request', 'loraleya_custom_order_convert_action' );

function loraleya_custom_order_admin_notice() {
    if ( empty( $_GET['ll_custom_order_notice'] ) || ! current_user_can( 'manage_woocommerce' ) ) {
        return;
    }

    $code     = sanitize_key( wp_unslash( $_GET['ll_custom_order_notice'] ) );
    $messages = array(
        'converted'                 => 'WooCommerce-заказ создан или уже был создан ранее.',
        'invalid_request'            => 'Заявка не найдена.',
        'woocommerce_unavailable'    => 'WooCommerce недоступен.',
        'request_not_agreed'         => 'Создать новый заказ можно только из заявки со статусом «Согласовано».',
        'missing_name'               => 'Заполните имя клиента.',
        'invalid_phone'              => 'Проверьте телефон клиента.',
        'invalid_email'              => 'Проверьте email клиента.',
        'missing_price'              => 'Укажите согласованную стоимость изделий больше 0.',
        'invalid_currency'           => 'Не удалось определить валюту заявки.',
        'missing_delivery_service'   => 'Выберите сервис доставки.',
        'missing_delivery_location'  => 'Укажите регион и город доставки.',
        'missing_delivery_mode'      => 'Выберите ПВЗ или курьерскую доставку.',
        'invalid_fivepost_mode'      => 'Для 5Post выберите получение в ПВЗ.',
        'missing_pickup'             => 'Укажите пункт выдачи.',
        'missing_fivepost_point_id'  => 'Укажите код или ID ПВЗ 5Post.',
        'missing_courier_address'    => 'Для курьерской доставки укажите адрес и индекс.',
        'conversion_locked'          => 'Создание заказа уже выполняется. Обновите страницу.',
        'incomplete_order_cleanup_failed' => 'Незавершённый заказ не удалось удалить. Новый заказ не создан; проверьте журнал.',
        'conversion_link_failed'     => 'Не удалось восстановить связь с WooCommerce-заказом.',
        'conversion_failed'          => 'Не удалось полностью создать заказ. Техническая ошибка записана в журнал.',
    );
    if ( ! isset( $messages[ $code ] ) ) {
        return;
    }

    $class = 'converted' === $code ? 'notice notice-success is-dismissible' : 'notice notice-error';
    echo '<div class="' . esc_attr( $class ) . '"><p>' . esc_html( $messages[ $code ] ) . '</p></div>';
}
add_action( 'admin_notices', 'loraleya_custom_order_admin_notice' );

/** Suppress only the misleading on-hold customer email for converted requests. */
function loraleya_custom_order_disable_on_hold_email( $enabled, $order ) {
    if ( $order instanceof WC_Order && 'yes' === $order->get_meta( '_ll_individual_order' ) ) {
        return false;
    }
    return $enabled;
}
add_filter( 'woocommerce_email_enabled_customer_on_hold_order', 'loraleya_custom_order_disable_on_hold_email', 20, 2 );

function loraleya_custom_order_resolve_order( $object ) {
    if ( ! function_exists( 'wc_get_order' ) ) {
        return false;
    }
    if ( $object instanceof WC_Order ) {
        return $object;
    }
    if ( $object instanceof WP_Post && 'shop_order' === $object->post_type ) {
        $order = wc_get_order( $object->ID );
        return $order instanceof WC_Order ? $order : false;
    }
    return false;
}

/** Keep manual individual orders out of the Fivepost admin shipment workflow. */
function loraleya_custom_order_disable_fivepost_admin_metabox( $screen_id = '', $object = null ) {
    $order = loraleya_custom_order_resolve_order( $object );
    if ( ! $order ) {
        $order = loraleya_custom_order_resolve_order( $screen_id );
    }
    if ( ! $order || 'yes' !== $order->get_meta( '_ll_individual_order' ) ) {
        return;
    }

    if ( ! class_exists( '\\WordPress\\Fivepost\\Fivepost_WP' ) ) {
        return;
    }

    $fivepost = \WordPress\Fivepost\Fivepost_WP::getInstance();
    remove_action( 'add_meta_boxes', array( $fivepost, 'actionAddMetaBoxes' ), 10 );
}
add_action( 'add_meta_boxes', 'loraleya_custom_order_disable_fivepost_admin_metabox', 1, 2 );

/** Add the panel only to individual orders, on both HPOS and legacy screens. */
function loraleya_custom_order_add_order_metabox( $screen_id = '', $object = null ) {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        return;
    }

    $order = loraleya_custom_order_resolve_order( $object );
    if ( ! $order ) {
        $order = loraleya_custom_order_resolve_order( $screen_id );
    }
    if ( ! $order || 'yes' !== $order->get_meta( '_ll_individual_order' ) ) {
        return;
    }

    $screen            = get_current_screen();
    $current_screen_id = $screen ? $screen->id : ( is_string( $screen_id ) ? $screen_id : '' );
    $screens           = array( 'shop_order' );
    if ( function_exists( 'wc_get_page_screen_id' ) ) {
        $screens[] = wc_get_page_screen_id( 'shop-order' );
    }
    if ( ! in_array( $current_screen_id, array_unique( $screens ), true ) ) {
        return;
    }

    add_meta_box(
        'll_individual_order_details',
        'Индивидуальный заказ LoraLeya',
        'loraleya_custom_order_render_order_metabox',
        $current_screen_id,
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'loraleya_custom_order_add_order_metabox', 20, 2 );

function loraleya_custom_order_render_order_metabox( $object ) {
    $order = loraleya_custom_order_resolve_order( $object );
    if ( ! $order ) {
        return;
    }

    wp_nonce_field( 'loraleya_save_individual_order', 'loraleya_individual_order_nonce' );
    $request_id = absint( $order->get_meta( '_ll_custom_request_id' ) );
    ?>
    <style>
        .ll-individual-order-grid{display:grid;grid-template-columns:repeat(2,minmax(240px,1fr));gap:14px}.ll-individual-order-grid .wide{grid-column:1/-1}.ll-individual-order-grid label{display:block;font-weight:600;margin-bottom:4px}.ll-individual-order-grid input,.ll-individual-order-grid textarea{width:100%}@media(max-width:782px){.ll-individual-order-grid{grid-template-columns:1fr}}
    </style>
    <?php if ( $request_id && 'll_custom_request' === get_post_type( $request_id ) ) : ?>
        <p><strong>Индивидуальная заявка <?php echo esc_html( loraleya_custom_order_number( $request_id ) ); ?></strong> — <a href="<?php echo esc_url( get_edit_post_link( $request_id ) ); ?>">открыть заявку</a></p>
    <?php endif; ?>
    <p>Цена позиции и доставка редактируются штатными средствами WooCommerce. После сохранения параметры ниже синхронизируются с видимыми meta позиции заказа.</p>
    <div class="ll-individual-order-grid">
        <p><label for="ll_order_shape">Форма</label><input id="ll_order_shape" name="ll_individual_order[shape]" value="<?php echo esc_attr( $order->get_meta( '_ll_individual_shape' ) ); ?>"></p>
        <p><label for="ll_order_size">Размер</label><input id="ll_order_size" name="ll_individual_order[size]" value="<?php echo esc_attr( $order->get_meta( '_ll_individual_size' ) ); ?>"></p>
        <p><label for="ll_order_persons">Количество персон</label><input type="number" min="0" id="ll_order_persons" name="ll_individual_order[persons]" value="<?php echo esc_attr( $order->get_meta( '_ll_individual_persons' ) ); ?>"></p>
        <p><label for="ll_order_color">Цвет</label><input id="ll_order_color" name="ll_individual_order[color]" value="<?php echo esc_attr( $order->get_meta( '_ll_individual_color' ) ); ?>"></p>
        <p class="wide"><label for="ll_order_items">Комплектация</label><textarea rows="3" id="ll_order_items" name="ll_individual_order[items_summary]"><?php echo esc_textarea( $order->get_meta( '_ll_individual_items_summary' ) ); ?></textarea></p>
        <p class="wide"><label for="ll_order_options">Дополнительные опции</label><textarea rows="2" id="ll_order_options" name="ll_individual_order[options]"><?php echo esc_textarea( $order->get_meta( '_ll_individual_options' ) ); ?></textarea></p>
        <p class="wide"><label for="ll_order_production_note">Производственная заметка</label><textarea rows="4" id="ll_order_production_note" name="ll_individual_order[production_note]"><?php echo esc_textarea( $order->get_meta( '_ll_individual_production_note' ) ); ?></textarea><span>Видна только менеджеру.</span></p>
    </div>
    <?php
}

function loraleya_custom_order_save_order_metabox( $order_id, $order = null ) {
    if (
        ! current_user_can( 'manage_woocommerce' )
        || empty( $_POST['loraleya_individual_order_nonce'] )
        || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['loraleya_individual_order_nonce'] ) ), 'loraleya_save_individual_order' )
        || empty( $_POST['ll_individual_order'] )
        || ! is_array( $_POST['ll_individual_order'] )
    ) {
        return;
    }

    $order = $order instanceof WC_Order ? $order : wc_get_order( $order_id );
    if ( ! $order || 'yes' !== $order->get_meta( '_ll_individual_order' ) ) {
        return;
    }

    $input = wp_unslash( $_POST['ll_individual_order'] );
    $old   = array(
        'shape'           => $order->get_meta( '_ll_individual_shape' ),
        'size'            => $order->get_meta( '_ll_individual_size' ),
        'persons'         => $order->get_meta( '_ll_individual_persons' ),
        'color'           => $order->get_meta( '_ll_individual_color' ),
        'items_summary'   => $order->get_meta( '_ll_individual_items_summary' ),
        'options'         => $order->get_meta( '_ll_individual_options' ),
        'production_note' => $order->get_meta( '_ll_individual_production_note' ),
    );
    $data  = array(
        'shape'           => isset( $input['shape'] ) ? sanitize_text_field( $input['shape'] ) : '',
        'size'            => isset( $input['size'] ) ? sanitize_text_field( $input['size'] ) : '',
        'persons'         => isset( $input['persons'] ) ? absint( $input['persons'] ) : 0,
        'color'           => isset( $input['color'] ) ? sanitize_text_field( $input['color'] ) : '',
        'items_summary'   => isset( $input['items_summary'] ) ? sanitize_textarea_field( $input['items_summary'] ) : '',
        'options'         => isset( $input['options'] ) ? sanitize_textarea_field( $input['options'] ) : '',
        'production_note' => isset( $input['production_note'] ) ? sanitize_textarea_field( $input['production_note'] ) : '',
    );

    $order->update_meta_data( '_ll_individual_shape', $data['shape'] );
    $order->update_meta_data( '_ll_individual_size', $data['size'] );
    $order->update_meta_data( '_ll_individual_persons', $data['persons'] );
    $order->update_meta_data( '_ll_individual_color', $data['color'] );
    $order->update_meta_data( '_ll_individual_items_summary', $data['items_summary'] );
    $order->update_meta_data( '_ll_individual_options', $data['options'] );
    $order->update_meta_data( '_ll_individual_production_note', $data['production_note'] );
    $order->save();

    $meta_map = array(
        'Форма стола'          => $data['shape'],
        'Размер'               => $data['size'],
        'Количество персон'    => $data['persons'],
        'Цвет'                 => $data['color'],
        'Комплектация'         => $data['items_summary'],
        'Дополнительные опции' => $data['options'],
    );

    foreach ( $order->get_items( 'line_item' ) as $item ) {
        if ( 'yes' !== $item->get_meta( '_ll_individual_line_item' ) ) {
            continue;
        }
        foreach ( $meta_map as $label => $value ) {
            if ( '' === trim( (string) $value ) || 0 === $value ) {
                $item->delete_meta_data( $label );
            } else {
                $item->update_meta_data( $label, $value );
            }
        }
        $item->save();
        break;
    }

    $request_id = absint( $order->get_meta( '_ll_custom_request_id' ) );
    $changed = false;
    foreach ( $data as $field => $value ) {
        if ( (string) $old[ $field ] !== (string) $value ) {
            $changed = true;
            break;
        }
    }
    if ( $changed && $request_id && 'll_custom_request' === get_post_type( $request_id ) ) {
        loraleya_custom_order_add_history( $request_id, 'Параметры изделия обновлены в WooCommerce-заказе №' . $order->get_order_number() );
    }
}
add_action( 'woocommerce_process_shop_order_meta', 'loraleya_custom_order_save_order_metabox', 20, 2 );
