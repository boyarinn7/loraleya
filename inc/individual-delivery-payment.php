<?php
/**
 * Separate delivery-payment orders for individual orders.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function loraleya_individual_delivery_is_main_order( $order ) {
    return $order instanceof WC_Order
        && 'yes' === $order->get_meta( '_ll_individual_order' )
        && 'yes' !== $order->get_meta( '_ll_delivery_payment_order' );
}

function loraleya_individual_delivery_is_payment_order( $order ) {
    return $order instanceof WC_Order
        && 'yes' === $order->get_meta( '_ll_delivery_payment_order' );
}

function loraleya_individual_delivery_carrier_labels() {
    return array(
        ''         => 'Не определён',
        'fivepost' => '5Post',
        'cdek'     => 'СДЭК',
        'yandex'   => 'Яндекс Доставка',
        'other'    => 'Другое',
    );
}

function loraleya_individual_delivery_condition_labels() {
    return array(
        ''        => 'Не определено',
        'free'    => 'Бесплатная',
        'invoice' => 'Выставить к оплате',
        'buyer'   => 'За счёт покупателя',
    );
}

function loraleya_individual_delivery_get_order( $order_id, $main_order_id = 0 ) {
    $order = $order_id && function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : false;
    if ( ! loraleya_individual_delivery_is_payment_order( $order ) ) {
        return false;
    }
    if ( $main_order_id && absint( $order->get_meta( '_ll_delivery_parent_order_id' ) ) !== absint( $main_order_id ) ) {
        return false;
    }
    return $order;
}

function loraleya_individual_delivery_active_order( $main_order ) {
    if ( ! loraleya_individual_delivery_is_main_order( $main_order ) ) {
        return false;
    }
    return loraleya_individual_delivery_get_order(
        absint( $main_order->get_meta( '_ll_active_delivery_payment_order_id' ) ),
        $main_order->get_id()
    );
}

function loraleya_individual_delivery_latest_order( $main_order ) {
    $active = loraleya_individual_delivery_active_order( $main_order );
    if ( $active ) {
        return $active;
    }

    $ids = $main_order instanceof WC_Order ? $main_order->get_meta( '_ll_delivery_payment_order_ids' ) : array();
    $ids = is_array( $ids ) ? array_reverse( array_map( 'absint', $ids ) ) : array();
    foreach ( $ids as $order_id ) {
        $order = loraleya_individual_delivery_get_order( $order_id, $main_order->get_id() );
        if ( $order ) {
            return $order;
        }
    }
    return false;
}

function loraleya_individual_delivery_history_orders( $main_order ) {
    if ( ! loraleya_individual_delivery_is_main_order( $main_order ) ) {
        return array();
    }

    $ids    = $main_order->get_meta( '_ll_delivery_payment_order_ids' );
    $ids    = is_array( $ids ) ? array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) ) : array();
    $orders = array();
    foreach ( $ids as $order_id ) {
        $delivery_order = loraleya_individual_delivery_get_order( $order_id, $main_order->get_id() );
        if ( $delivery_order ) {
            $orders[] = $delivery_order;
        }
    }
    return $orders;
}

function loraleya_individual_delivery_display_number( $delivery_order ) {
    if ( ! loraleya_individual_delivery_is_payment_order( $delivery_order ) ) {
        return '';
    }

    $main_order = wc_get_order( absint( $delivery_order->get_meta( '_ll_delivery_parent_order_id' ) ) );
    if ( ! loraleya_individual_delivery_is_main_order( $main_order ) ) {
        return (string) $delivery_order->get_order_number();
    }

    $sequence = 1;
    foreach ( loraleya_individual_delivery_history_orders( $main_order ) as $index => $history_order ) {
        if ( $history_order->get_id() === $delivery_order->get_id() ) {
            $sequence = $index + 1;
            break;
        }
    }

    return $main_order->get_order_number() . '/Д' . ( $sequence > 1 ? '-' . $sequence : '' );
}

function loraleya_individual_delivery_current_pay_order() {
    global $wp;

    if ( ! function_exists( 'is_wc_endpoint_url' ) || ! is_wc_endpoint_url( 'order-pay' ) ) {
        return false;
    }
    $order_id = isset( $wp->query_vars['order-pay'] ) ? absint( $wp->query_vars['order-pay'] ) : 0;
    $order    = $order_id ? wc_get_order( $order_id ) : false;
    return loraleya_individual_delivery_is_payment_order( $order ) ? $order : false;
}

function loraleya_individual_delivery_is_moscow( $order ) {
    if ( ! $order instanceof WC_Order || ! function_exists( 'loraleya_is_moscow_shipping_destination' ) ) {
        return false;
    }
    return loraleya_is_moscow_shipping_destination( array(
        'city'    => (string) $order->get_meta( '_ll_delivery_city' ),
        'address' => (string) $order->get_meta( '_ll_delivery_location' ),
        'state'   => '',
    ) );
}

function loraleya_individual_delivery_is_paid( $order ) {
    return $order instanceof WC_Order
        && ( $order->is_paid() || $order->get_date_paid() );
}

function loraleya_individual_delivery_status_label( $order ) {
    if ( ! $order instanceof WC_Order ) {
        return 'не создан';
    }
    if ( loraleya_individual_delivery_is_paid( $order ) ) {
        return 'оплачено';
    }
    $labels = array(
        'pending'    => 'ожидает оплаты',
        'on-hold'    => 'ожидает оплаты',
        'failed'     => 'ошибка оплаты',
        'cancelled'  => 'отменён',
        'refunded'   => 'возвращён',
        'processing' => 'оплачено',
        'completed'  => 'оплачено',
    );
    $status = $order->get_status();
    return isset( $labels[ $status ] ) ? $labels[ $status ] : wc_get_order_status_name( $status );
}

function loraleya_individual_delivery_request_number( $order ) {
    if ( ! $order instanceof WC_Order ) {
        return '';
    }
    $number = trim( (string) $order->get_meta( '_ll_custom_request_number' ) );
    if ( '' === $number ) {
        return '';
    }
    return 0 === strpos( $number, 'ИЗ-' ) ? $number : 'ИЗ-' . $number;
}

function loraleya_individual_delivery_admin_error( $message ) {
    if ( class_exists( 'WC_Admin_Meta_Boxes' ) ) {
        WC_Admin_Meta_Boxes::add_error( $message );
    }
}

function loraleya_individual_delivery_admin_nonce_is_valid() {
    return ! empty( $_POST['loraleya_individual_order_nonce'] )
        && is_scalar( $_POST['loraleya_individual_order_nonce'] )
        && wp_verify_nonce(
            sanitize_text_field( wp_unslash( $_POST['loraleya_individual_order_nonce'] ) ),
            'loraleya_save_individual_order'
        );
}

function loraleya_individual_delivery_add_payment_order_metabox( $screen_id = '', $object = null ) {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        return;
    }
    $order = function_exists( 'loraleya_custom_order_resolve_order' )
        ? loraleya_custom_order_resolve_order( $object )
        : false;
    if ( ! $order && function_exists( 'loraleya_custom_order_resolve_order' ) ) {
        $order = loraleya_custom_order_resolve_order( $screen_id );
    }
    if ( ! loraleya_individual_delivery_is_payment_order( $order ) ) {
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
        'll_delivery_payment_order_details',
        'Счёт на доставку LoraLeya',
        'loraleya_individual_delivery_render_payment_order_metabox',
        $current_screen_id,
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'loraleya_individual_delivery_add_payment_order_metabox', 20, 2 );

function loraleya_individual_delivery_render_payment_order_metabox( $object ) {
    $order = function_exists( 'loraleya_custom_order_resolve_order' )
        ? loraleya_custom_order_resolve_order( $object )
        : false;
    if ( ! loraleya_individual_delivery_is_payment_order( $order ) ) {
        return;
    }
    $main           = wc_get_order( absint( $order->get_meta( '_ll_delivery_parent_order_id' ) ) );
    $request_id     = absint( $order->get_meta( '_ll_custom_request_id' ) );
    $request_number = loraleya_individual_delivery_request_number( $order );
    $carrier_labels = loraleya_individual_delivery_carrier_labels();
    $carrier        = (string) $order->get_meta( '_ll_delivery_carrier' );
    $display_number = loraleya_individual_delivery_display_number( $order );
    ?>
    <p><strong>Отдельная оплата только доставки.</strong> Товары основного заказа сюда не копируются.</p>
    <p><strong>Публичный номер счёта:</strong> №<?php echo esc_html( $display_number ); ?><br>
    <span style="color:#646970">Технический WooCommerce order: №<?php echo esc_html( $order->get_order_number() ); ?></span></p>
    <p><strong>Основной заказ:</strong>
        <?php if ( $main ) : ?>
            <a href="<?php echo esc_url( $main->get_edit_order_url() ); ?>">№<?php echo esc_html( $main->get_order_number() ); ?></a>
        <?php else : ?>—<?php endif; ?>
    </p>
    <p><strong>Индивидуальная заявка:</strong>
        <?php if ( $request_id && 'll_custom_request' === get_post_type( $request_id ) ) : ?>
            <a href="<?php echo esc_url( get_edit_post_link( $request_id ) ); ?>"><?php echo esc_html( $request_number ? $request_number : 'открыть' ); ?></a>
        <?php else : ?><?php echo esc_html( $request_number ? $request_number : '—' ); ?><?php endif; ?>
    </p>
    <p><strong>Перевозчик:</strong> <?php echo esc_html( isset( $carrier_labels[ $carrier ] ) ? $carrier_labels[ $carrier ] : $carrier ); ?><br>
    <strong>Сумма:</strong> <?php echo wp_kses_post( wc_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ) ); ?><br>
    <strong>Статус:</strong> <?php echo esc_html( loraleya_individual_delivery_status_label( $order ) ); ?></p>
    <?php
}

function loraleya_individual_delivery_render_admin_block( $order ) {
    if ( ! loraleya_individual_delivery_is_main_order( $order ) ) {
        return;
    }

    $preference_labels = array(
        'pickup'  => 'Пункт выдачи',
        'courier' => 'Курьер',
    );
    $preference = (string) $order->get_meta( '_ll_delivery_preference' );
    $rows = array(
        'ФИО получателя'   => $order->get_meta( '_ll_delivery_recipient_name' ),
        'Телефон'          => $order->get_meta( '_ll_delivery_recipient_phone' ),
        'Город'            => $order->get_meta( '_ll_delivery_city' ),
        'Получение'        => isset( $preference_labels[ $preference ] ) ? $preference_labels[ $preference ] : $preference,
        'Адрес / ориентир' => $order->get_meta( '_ll_delivery_location' ),
    );
    $note             = (string) $order->get_meta( '_ll_delivery_request_note' );
    $carrier          = (string) $order->get_meta( '_ll_delivery_carrier' );
    $condition        = (string) $order->get_meta( '_ll_delivery_payment_condition' );
    $amount           = (string) $order->get_meta( '_ll_delivery_payment_amount' );
    $carrier_labels   = loraleya_individual_delivery_carrier_labels();
    $condition_labels = loraleya_individual_delivery_condition_labels();
    $active_order     = loraleya_individual_delivery_active_order( $order );
    $linked_order     = $active_order ? $active_order : loraleya_individual_delivery_latest_order( $order );
    $history_orders   = loraleya_individual_delivery_history_orders( $order );
    $active_paid      = $active_order && loraleya_individual_delivery_is_paid( $active_order );
    $active_unpaid    = $active_order && ! $active_paid && ! $active_order->has_status( array( 'cancelled', 'refunded' ) );
    $is_locked        = $active_paid || $active_unpaid;
    $can_cancel       = $active_unpaid;
    $can_create       = ! $is_locked && 'invoice' === $condition && is_numeric( $amount ) && (float) $amount > 0;
    $is_moscow        = loraleya_individual_delivery_is_moscow( $order );
    ?>
    <style>
        .ll-future-delivery{margin:16px 0;padding:14px 16px;border-left:4px solid #2271b1;background:#f0f6fc}.ll-future-delivery h3{margin:0 0 10px}.ll-future-delivery h4{margin:18px 0 8px}.ll-future-delivery dl{display:grid;grid-template-columns:minmax(160px,220px) 1fr;gap:7px 14px;margin:0}.ll-future-delivery dt{font-weight:600}.ll-future-delivery dd{margin:0}.ll-delivery-edit-toggle{margin-top:12px}.ll-delivery-details-editor,.ll-delivery-management{display:grid;grid-template-columns:repeat(3,minmax(180px,1fr));gap:12px;margin-top:16px;padding-top:14px;border-top:1px solid #c3c4c7}.ll-delivery-details-editor[hidden]{display:none}.ll-delivery-details-editor p,.ll-delivery-management p{margin:0}.ll-delivery-details-editor .wide{grid-column:1/-1}.ll-delivery-details-editor label,.ll-delivery-management label{display:block;font-weight:600;margin-bottom:4px}.ll-delivery-details-editor input,.ll-delivery-details-editor select,.ll-delivery-details-editor textarea,.ll-delivery-management select,.ll-delivery-management input{width:100%}.ll-delivery-state{grid-column:1/-1;padding:10px 12px;background:#fff;border:1px solid #c3c4c7}.ll-delivery-state.is-warning{border-left:4px solid #dba617;background:#fff8e5}.ll-delivery-state.is-success{border-left:4px solid #00a32a;background:#edfaef}.ll-delivery-actions{grid-column:1/-1;display:flex;gap:8px;align-items:center;flex-wrap:wrap}.ll-delivery-help{display:block;margin-top:4px;color:#646970}.ll-delivery-history{margin:8px 0 0;border-collapse:collapse;width:100%;background:#fff}.ll-delivery-history th,.ll-delivery-history td{padding:8px 10px;text-align:left;border-bottom:1px solid #dcdcde}.ll-delivery-history tr.is-active td{background:#edfaef;font-weight:600}.ll-delivery-history .ll-technical-order{display:block;color:#646970;font-weight:400;font-size:12px}@media(max-width:782px){.ll-future-delivery dl,.ll-delivery-details-editor,.ll-delivery-management{grid-template-columns:1fr}.ll-future-delivery dl{gap:3px}.ll-future-delivery dd{margin-bottom:7px}.ll-delivery-details-editor .wide{grid-column:auto}}
    </style>
    <div class="ll-future-delivery">
        <h3>Доставка</h3>
        <dl>
            <?php foreach ( $rows as $label => $value ) : ?>
                <dt><?php echo esc_html( $label ); ?></dt>
                <dd><?php echo '' !== trim( (string) $value ) ? nl2br( esc_html( $value ) ) : '—'; ?></dd>
            <?php endforeach; ?>
            <?php if ( '' !== trim( $note ) ) : ?>
                <dt>Комментарий</dt>
                <dd><?php echo nl2br( esc_html( $note ) ); ?></dd>
            <?php endif; ?>
        </dl>

        <?php if ( $active_unpaid ) : ?>
            <p class="ll-delivery-state is-warning"><strong>Данные доставки заблокированы активным неоплаченным счётом.</strong> Чтобы изменить их, сначала отмените счёт.</p>
        <?php else : ?>
            <button type="button" class="button ll-delivery-edit-toggle" id="ll_delivery_edit_toggle" aria-expanded="false" aria-controls="ll_delivery_details_editor">Изменить данные доставки</button>
            <div class="ll-delivery-details-editor" id="ll_delivery_details_editor" hidden>
                <?php if ( $active_paid ) : ?>
                    <div class="ll-delivery-state is-warning"><strong>Счёт уже оплачен.</strong> Можно исправить только ФИО и телефон получателя. Изменение города, способа получения или адреса может повлиять на стоимость и требует ручного согласования; доплата и возврат здесь не рассчитываются.</div>
                <?php endif; ?>
                <p>
                    <label for="ll_delivery_recipient_name">ФИО получателя</label>
                    <input id="ll_delivery_recipient_name" name="ll_delivery_details[recipient_name]" value="<?php echo esc_attr( $order->get_meta( '_ll_delivery_recipient_name' ) ); ?>">
                </p>
                <p>
                    <label for="ll_delivery_recipient_phone">Телефон</label>
                    <input type="tel" id="ll_delivery_recipient_phone" name="ll_delivery_details[recipient_phone]" value="<?php echo esc_attr( $order->get_meta( '_ll_delivery_recipient_phone' ) ); ?>">
                </p>
                <p>
                    <label for="ll_delivery_city">Город</label>
                    <input id="ll_delivery_city" name="ll_delivery_details[city]" value="<?php echo esc_attr( $order->get_meta( '_ll_delivery_city' ) ); ?>" <?php disabled( $active_paid ); ?>>
                </p>
                <p>
                    <label for="ll_delivery_preference">Получение</label>
                    <select id="ll_delivery_preference" name="ll_delivery_details[preference]" <?php disabled( $active_paid ); ?>>
                        <option value="">Не определено</option>
                        <?php foreach ( $preference_labels as $value => $label ) : ?>
                            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $preference, $value ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p class="wide">
                    <label for="ll_delivery_location">Адрес / ПВЗ / ориентир</label>
                    <textarea rows="2" id="ll_delivery_location" name="ll_delivery_details[location]" <?php disabled( $active_paid ); ?>><?php echo esc_textarea( $order->get_meta( '_ll_delivery_location' ) ); ?></textarea>
                </p>
                <p class="wide">
                    <label for="ll_delivery_request_note">Комментарий по доставке</label>
                    <textarea rows="2" id="ll_delivery_request_note" name="ll_delivery_details[request_note]" <?php disabled( $active_paid ); ?>><?php echo esc_textarea( $note ); ?></textarea>
                </p>
                <div class="ll-delivery-actions">
                    <button type="submit" class="button button-primary" name="ll_delivery_payment_action" value="save_details">Сохранить данные доставки</button>
                    <span class="ll-delivery-help">Актуальные значения сохраняются в основном WooCommerce-заказе.</span>
                </div>
            </div>
        <?php endif; ?>

        <div class="ll-delivery-management" data-moscow="<?php echo $is_moscow ? 'yes' : 'no'; ?>" data-locked="<?php echo $is_locked ? 'yes' : 'no'; ?>">
            <p>
                <label for="ll_delivery_carrier">Перевозчик</label>
                <select id="ll_delivery_carrier" name="ll_delivery_payment[carrier]" <?php disabled( $is_locked ); ?>>
                    <?php foreach ( $carrier_labels as $value => $label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $carrier, $value ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label for="ll_delivery_payment_condition">Оплата доставки</label>
                <select id="ll_delivery_payment_condition" name="ll_delivery_payment[condition]" <?php disabled( $is_locked ); ?>>
                    <?php foreach ( $condition_labels as $value => $label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $condition, $value ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label for="ll_delivery_payment_amount">Сумма для клиента, ₽</label>
                <input type="number" min="0" step="0.01" id="ll_delivery_payment_amount" name="ll_delivery_payment[amount]" value="<?php echo esc_attr( $amount ); ?>" <?php disabled( $is_locked || 'invoice' !== $condition ); ?>>
                <span class="ll-delivery-help">Указывается только для отдельного счёта.</span>
            </p>

            <?php if ( '' === $condition ) : ?>
                <div class="ll-delivery-state is-warning"><strong>Условия доставки ещё не определены.</strong> Основной заказ нельзя переводить в «Выполнен».</div>
            <?php elseif ( 'free' === $condition ) : ?>
                <div class="ll-delivery-state is-success"><strong>Бесплатная доставка.</strong> К оплате: 0 ₽. Оплата не требуется.</div>
            <?php elseif ( 'buyer' === $condition ) : ?>
                <div class="ll-delivery-state is-success"><strong>За счёт покупателя.</strong> Покупатель оплачивает перевозчику напрямую; счёт LoraLeya не требуется.</div>
            <?php else : ?>
                <div class="ll-delivery-state <?php echo $active_order && loraleya_individual_delivery_is_paid( $active_order ) ? 'is-success' : 'is-warning'; ?>">
                    <strong>Выставить к оплате.</strong>
                    Сумма: <?php echo wp_kses_post( wc_price( (float) $amount, array( 'currency' => $order->get_currency() ) ) ); ?>.
                    <?php if ( $linked_order ) : ?>
                        Счёт <a href="<?php echo esc_url( $linked_order->get_edit_order_url() ); ?>">№<?php echo esc_html( loraleya_individual_delivery_display_number( $linked_order ) ); ?></a> — <?php echo esc_html( loraleya_individual_delivery_status_label( $linked_order ) ); ?>.
                        <?php if ( ! $active_order ) : ?>Текущего активного счёта нет.<?php endif; ?>
                    <?php else : ?>
                        Счёт ещё не создан.
                    <?php endif; ?>
                    <?php if ( ! $active_order || ! loraleya_individual_delivery_is_paid( $active_order ) ) : ?>Основной заказ нельзя переводить в «Выполнен».<?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="ll-delivery-actions">
                <?php if ( ! $is_locked ) : ?>
                    <button type="submit" class="button button-primary" id="ll_create_delivery_invoice" name="ll_delivery_payment_action" value="create_invoice" <?php disabled( ! $can_create ); ?>>Выставить счёт на доставку</button>
                    <span class="ll-delivery-help">Сначала проверьте перевозчика, условие и сумму. Эти данные фиксируются в счёте.</span>
                <?php endif; ?>
                <?php if ( $can_cancel ) : ?>
                    <button type="submit" class="button" name="ll_delivery_payment_action" value="cancel_invoice" onclick="return window.confirm('Отменить неоплаченный счёт на доставку?');">Отменить счёт</button>
                <?php endif; ?>
                <?php if ( $active_order && loraleya_individual_delivery_is_paid( $active_order ) ) : ?>
                    <strong>Оплаченный счёт зафиксирован. Изменение суммы и отмена здесь недоступны.</strong>
                <?php endif; ?>
            </div>
        </div>

        <?php if ( $history_orders ) : ?>
            <h4>История счетов доставки</h4>
            <table class="ll-delivery-history">
                <thead><tr><th>Счёт</th><th>Сумма</th><th>Статус</th></tr></thead>
                <tbody>
                <?php foreach ( $history_orders as $history_order ) : ?>
                    <?php $is_active_history = $active_order && $active_order->get_id() === $history_order->get_id(); ?>
                    <tr class="<?php echo $is_active_history ? 'is-active' : ''; ?>">
                        <td><a href="<?php echo esc_url( $history_order->get_edit_order_url() ); ?>">№<?php echo esc_html( loraleya_individual_delivery_display_number( $history_order ) ); ?></a><?php if ( $is_active_history ) : ?> — активный<?php endif; ?><span class="ll-technical-order">Woo order №<?php echo esc_html( $history_order->get_order_number() ); ?></span></td>
                        <td><?php echo wp_kses_post( wc_price( $history_order->get_total(), array( 'currency' => $history_order->get_currency() ) ) ); ?></td>
                        <td><?php echo esc_html( loraleya_individual_delivery_status_label( $history_order ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <script>
    (function () {
        var block = document.querySelector('.ll-delivery-management');
        var toggle = document.getElementById('ll_delivery_edit_toggle');
        var editor = document.getElementById('ll_delivery_details_editor');
        if (toggle && editor) {
            toggle.addEventListener('click', function () {
                var willOpen = editor.hasAttribute('hidden');
                if (willOpen) editor.removeAttribute('hidden');
                else editor.setAttribute('hidden', 'hidden');
                toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                toggle.textContent = willOpen ? 'Скрыть редактирование' : 'Изменить данные доставки';
            });
        }

        if (!block || block.getAttribute('data-locked') === 'yes') return;
        var carrier = document.getElementById('ll_delivery_carrier');
        var condition = document.getElementById('ll_delivery_payment_condition');
        var amount = document.getElementById('ll_delivery_payment_amount');
        var city = document.getElementById('ll_delivery_city');
        var location = document.getElementById('ll_delivery_location');
        var createButton = document.getElementById('ll_create_delivery_invoice');
        function isMoscow() {
            var value = city && location
                ? (city.value + ' ' + location.value).toLowerCase().replace(/ё/g, 'е')
                : '';
            return value ? /москв|московск|moscow|moskva|moskovsk/.test(value) : block.getAttribute('data-moscow') === 'yes';
        }
        function refresh() {
            var needsInvoice = condition.value === 'invoice';
            amount.disabled = !needsInvoice;
            if (createButton) createButton.disabled = !needsInvoice || !(parseFloat(amount.value) > 0);
        }
        function applyCarrierDefault() {
            if (carrier.value === 'fivepost') {
                condition.value = isMoscow() ? 'free' : 'invoice';
                amount.value = isMoscow() ? '0' : '250';
            } else if (carrier.value === 'cdek' || carrier.value === 'yandex') {
                condition.value = 'buyer';
                amount.value = '';
            } else {
                condition.value = '';
                amount.value = '';
            }
            refresh();
        }
        carrier.addEventListener('change', applyCarrierDefault);
        if (city) city.addEventListener('change', function () { if (carrier.value === 'fivepost') applyCarrierDefault(); });
        if (location) location.addEventListener('change', function () { if (carrier.value === 'fivepost') applyCarrierDefault(); });
        condition.addEventListener('change', function () {
            if (condition.value === 'free') amount.value = '0';
            if (condition.value !== 'invoice' && condition.value !== 'free') amount.value = '';
            refresh();
        });
        amount.addEventListener('input', refresh);
        refresh();
    }());
    </script>
    <?php
}

function loraleya_individual_delivery_save_admin_block( $order_id, $object = null ) {
    if (
        ! current_user_can( 'manage_woocommerce' )
        || ! loraleya_individual_delivery_admin_nonce_is_valid()
    ) {
        return;
    }

    $order = $object instanceof WC_Order ? $object : wc_get_order( $order_id );
    if ( ! loraleya_individual_delivery_is_main_order( $order ) ) {
        return;
    }

    $action = isset( $_POST['ll_delivery_payment_action'] ) && is_scalar( $_POST['ll_delivery_payment_action'] )
        ? sanitize_key( wp_unslash( $_POST['ll_delivery_payment_action'] ) )
        : '';
    if ( 'cancel_invoice' === $action ) {
        $result = loraleya_individual_delivery_cancel_invoice( $order );
        if ( is_wp_error( $result ) ) {
            loraleya_individual_delivery_admin_error( $result->get_error_message() );
        }
        return;
    }

    $active          = loraleya_individual_delivery_active_order( $order );
    $active_paid     = $active && loraleya_individual_delivery_is_paid( $active );
    $active_unpaid   = $active && ! $active_paid && ! $active->has_status( array( 'cancelled', 'refunded' ) );
    if ( $active && ! $active_paid && ! $active_unpaid ) {
        $active = false;
    }
    $details_changed = false;
    $route_changed   = false;

    if ( isset( $_POST['ll_delivery_details'] ) && is_array( $_POST['ll_delivery_details'] ) ) {
        if ( $active_unpaid ) {
            if ( 'save_details' === $action ) {
                loraleya_individual_delivery_admin_error( 'Данные не изменены: сначала отмените активный неоплаченный счёт на доставку.' );
            }
        } else {
            $details_input = wp_unslash( $_POST['ll_delivery_details'] );
            $old_details   = array(
                'recipient_name'  => (string) $order->get_meta( '_ll_delivery_recipient_name' ),
                'recipient_phone' => (string) $order->get_meta( '_ll_delivery_recipient_phone' ),
                'city'            => (string) $order->get_meta( '_ll_delivery_city' ),
                'preference'      => (string) $order->get_meta( '_ll_delivery_preference' ),
                'location'        => (string) $order->get_meta( '_ll_delivery_location' ),
                'request_note'    => (string) $order->get_meta( '_ll_delivery_request_note' ),
            );
            $new_details   = $old_details;

            $new_details['recipient_name'] = isset( $details_input['recipient_name'] ) && is_scalar( $details_input['recipient_name'] )
                ? sanitize_text_field( $details_input['recipient_name'] )
                : $old_details['recipient_name'];
            if ( isset( $details_input['recipient_phone'] ) && is_scalar( $details_input['recipient_phone'] ) ) {
                $phone_input = sanitize_text_field( $details_input['recipient_phone'] );
                if ( $phone_input !== $old_details['recipient_phone'] ) {
                    $phone = function_exists( 'loraleya_normalize_custom_order_phone' )
                        ? loraleya_normalize_custom_order_phone( $phone_input )
                        : $phone_input;
                    if ( '' !== $phone_input && '' === $phone ) {
                        loraleya_individual_delivery_admin_error( 'Телефон получателя не сохранён: проверьте формат номера.' );
                    } else {
                        $new_details['recipient_phone'] = $phone;
                    }
                }
            }

            if ( ! $active_paid ) {
                $preferences = array( '', 'pickup', 'courier' );
                $preference  = isset( $details_input['preference'] ) && is_scalar( $details_input['preference'] )
                    ? sanitize_key( $details_input['preference'] )
                    : $old_details['preference'];
                $new_details['city'] = isset( $details_input['city'] ) && is_scalar( $details_input['city'] )
                    ? sanitize_text_field( $details_input['city'] )
                    : $old_details['city'];
                $new_details['preference'] = in_array( $preference, $preferences, true ) ? $preference : '';
                $new_details['location'] = isset( $details_input['location'] ) && is_scalar( $details_input['location'] )
                    ? sanitize_textarea_field( $details_input['location'] )
                    : $old_details['location'];
                $new_details['request_note'] = isset( $details_input['request_note'] ) && is_scalar( $details_input['request_note'] )
                    ? sanitize_textarea_field( $details_input['request_note'] )
                    : $old_details['request_note'];
            }

            $meta_keys = array(
                'recipient_name'  => '_ll_delivery_recipient_name',
                'recipient_phone' => '_ll_delivery_recipient_phone',
                'city'            => '_ll_delivery_city',
                'preference'      => '_ll_delivery_preference',
                'location'        => '_ll_delivery_location',
                'request_note'    => '_ll_delivery_request_note',
            );
            foreach ( $meta_keys as $key => $meta_key ) {
                if ( $new_details[ $key ] !== $old_details[ $key ] ) {
                    $order->update_meta_data( $meta_key, $new_details[ $key ] );
                    $details_changed = true;
                    if ( in_array( $key, array( 'city', 'location' ), true ) ) {
                        $route_changed = true;
                    }
                }
            }
        }
    }

    $financial_changed = false;
    if ( ! $active && isset( $_POST['ll_delivery_payment'] ) && is_array( $_POST['ll_delivery_payment'] ) ) {
        $input      = wp_unslash( $_POST['ll_delivery_payment'] );
        $carriers   = array_keys( loraleya_individual_delivery_carrier_labels() );
        $conditions = array_keys( loraleya_individual_delivery_condition_labels() );
        $carrier    = isset( $input['carrier'] ) && is_scalar( $input['carrier'] ) ? sanitize_key( $input['carrier'] ) : '';
        $condition  = isset( $input['condition'] ) && is_scalar( $input['condition'] ) ? sanitize_key( $input['condition'] ) : '';
        $amount     = isset( $input['amount'] ) && is_scalar( $input['amount'] ) ? wc_format_decimal( $input['amount'], wc_get_price_decimals() ) : '';
        $carrier    = in_array( $carrier, $carriers, true ) ? $carrier : '';
        $condition  = in_array( $condition, $conditions, true ) ? $condition : '';

        if ( 'free' === $condition ) {
            $amount = '0';
        } elseif ( 'invoice' !== $condition ) {
            $amount = '';
        }

        if ( $route_changed && 'fivepost' === $carrier ) {
            $condition = loraleya_individual_delivery_is_moscow( $order ) ? 'free' : 'invoice';
            $amount    = 'free' === $condition ? '0' : '250';
        }

        $financial_changed = $carrier !== (string) $order->get_meta( '_ll_delivery_carrier' )
            || $condition !== (string) $order->get_meta( '_ll_delivery_payment_condition' )
            || $amount !== (string) $order->get_meta( '_ll_delivery_payment_amount' );

        $order->update_meta_data( '_ll_delivery_carrier', $carrier );
        $order->update_meta_data( '_ll_delivery_payment_condition', $condition );
        $order->update_meta_data( '_ll_delivery_payment_amount', $amount );
    }

    if ( $details_changed || $financial_changed ) {
        $order->save();
        $request_id = absint( $order->get_meta( '_ll_custom_request_id' ) );
        if ( $details_changed ) {
            $order->add_order_note( 'Актуальные данные доставки обновлены менеджером.' );
            if ( $request_id && function_exists( 'loraleya_custom_order_add_history' ) ) {
                loraleya_custom_order_add_history( $request_id, 'Актуальные данные доставки обновлены в WooCommerce-заказе №' . $order->get_order_number() );
            }
        }
        if ( $financial_changed && $request_id && function_exists( 'loraleya_custom_order_add_history' ) ) {
            loraleya_custom_order_add_history( $request_id, 'Условия доставки обновлены в WooCommerce-заказе №' . $order->get_order_number() );
        }
    }

    if ( 'create_invoice' === $action ) {
        $result = loraleya_individual_delivery_create_invoice( $order );
        if ( is_wp_error( $result ) ) {
            loraleya_individual_delivery_admin_error( $result->get_error_message() );
        }
    }
}
add_action( 'woocommerce_process_shop_order_meta', 'loraleya_individual_delivery_save_admin_block', 25, 2 );

function loraleya_individual_delivery_creation_context( $active = null ) {
    static $is_active = false;
    if ( null !== $active ) {
        $is_active = (bool) $active;
    }
    return $is_active;
}

function loraleya_individual_delivery_disable_new_order_email( $enabled, $order ) {
    if ( loraleya_individual_delivery_creation_context() || loraleya_individual_delivery_is_payment_order( $order ) ) {
        return false;
    }
    return $enabled;
}
add_filter( 'woocommerce_email_enabled_new_order', 'loraleya_individual_delivery_disable_new_order_email', 100, 2 );

function loraleya_individual_delivery_create_invoice( $main_order ) {
    if ( ! loraleya_individual_delivery_is_main_order( $main_order ) ) {
        return new WP_Error( 'invalid_main_order', 'Счёт можно создать только для основного индивидуального заказа.' );
    }

    $lock_name = '_ll_delivery_invoice_lock_' . $main_order->get_id();
    $lock      = function_exists( 'loraleya_custom_order_acquire_option_lock' )
        ? loraleya_custom_order_acquire_option_lock( $lock_name, 120 )
        : new WP_Error( 'lock_unavailable', 'Механизм блокировки недоступен.' );
    if ( is_wp_error( $lock ) ) {
        return new WP_Error( 'invoice_locked', 'Создание счёта уже выполняется. Обновите страницу.' );
    }

    $delivery_order = false;
    try {
        $main_order = wc_get_order( $main_order->get_id() );
        if ( ! loraleya_individual_delivery_is_main_order( $main_order ) ) {
            throw new Exception( 'Основной индивидуальный заказ не найден.' );
        }

        $active = loraleya_individual_delivery_active_order( $main_order );
        if ( $active && ( ! $active->has_status( 'cancelled' ) || loraleya_individual_delivery_is_paid( $active ) ) ) {
            return new WP_Error( 'active_invoice_exists', 'Активный счёт на доставку уже существует: №' . loraleya_individual_delivery_display_number( $active ) . '.' );
        }
        if ( $active && $active->has_status( 'cancelled' ) ) {
            $main_order->delete_meta_data( '_ll_active_delivery_payment_order_id' );
            $main_order->save_meta_data();
        }

        $condition = (string) $main_order->get_meta( '_ll_delivery_payment_condition' );
        $carrier   = (string) $main_order->get_meta( '_ll_delivery_carrier' );
        $amount    = (string) $main_order->get_meta( '_ll_delivery_payment_amount' );
        if ( 'invoice' !== $condition ) {
            throw new Exception( 'Выберите условие «Выставить к оплате».' );
        }
        if ( ! is_numeric( $amount ) || (float) $amount <= 0 ) {
            throw new Exception( 'Укажите сумму доставки больше 0.' );
        }
        if ( ! isset( loraleya_individual_delivery_carrier_labels()[ $carrier ] ) || '' === $carrier ) {
            throw new Exception( 'Выберите перевозчика.' );
        }
        if ( ! is_email( $main_order->get_billing_email() ) ) {
            throw new Exception( 'В основном заказе отсутствует корректный email клиента.' );
        }

        loraleya_individual_delivery_creation_context( true );
        $delivery_order = wc_create_order( array(
            'customer_id' => $main_order->get_customer_id(),
            'status'      => 'pending',
        ) );
        loraleya_individual_delivery_creation_context( false );
        if ( is_wp_error( $delivery_order ) ) {
            return $delivery_order;
        }

        $delivery_order->set_created_via( 'll_delivery_payment' );
        $delivery_order->set_currency( $main_order->get_currency() );
        $delivery_order->set_address( $main_order->get_address( 'billing' ), 'billing' );
        $delivery_order->set_address( array(
            'first_name' => (string) $main_order->get_meta( '_ll_delivery_recipient_name' ),
            'phone'      => (string) $main_order->get_meta( '_ll_delivery_recipient_phone' ),
            'city'       => (string) $main_order->get_meta( '_ll_delivery_city' ),
            'address_1'  => (string) $main_order->get_meta( '_ll_delivery_location' ),
            'country'    => 'RU',
        ), 'shipping' );
        $delivery_order->set_payment_method( '' );
        $delivery_order->update_meta_data( '_ll_delivery_payment_order', 'yes' );
        $delivery_order->update_meta_data( '_ll_delivery_parent_order_id', $main_order->get_id() );
        $delivery_order->update_meta_data( '_ll_custom_request_id', absint( $main_order->get_meta( '_ll_custom_request_id' ) ) );
        $delivery_order->update_meta_data( '_ll_custom_request_number', (string) $main_order->get_meta( '_ll_custom_request_number' ) );
        $delivery_order->update_meta_data( '_ll_delivery_carrier', $carrier );
        $delivery_order->update_meta_data( '_ll_delivery_payment_condition', 'invoice' );
        $delivery_order->update_meta_data( '_ll_delivery_payment_amount', $amount );

        $shipping_item = new WC_Order_Item_Shipping();
        $shipping_item->set_method_title( 'Доставка по заказу №' . $main_order->get_order_number() );
        $shipping_item->set_method_id( 'll_individual_delivery' );
        $shipping_item->set_total( $amount );
        $shipping_item->add_meta_data( '_ll_delivery_carrier', $carrier, true );
        $delivery_order->add_item( $shipping_item );
        $delivery_order->calculate_totals( false );
        $delivery_order->save();

        $history   = $main_order->get_meta( '_ll_delivery_payment_order_ids' );
        $history   = is_array( $history ) ? array_map( 'absint', $history ) : array();
        $history[] = $delivery_order->get_id();
        $main_order->update_meta_data( '_ll_active_delivery_payment_order_id', $delivery_order->get_id() );
        $main_order->update_meta_data( '_ll_delivery_payment_order_ids', array_values( array_unique( $history ) ) );
        $main_order->update_meta_data( '_ll_delivery_payment_status', $delivery_order->get_status() );
        $main_order->save_meta_data();

        $display_number = loraleya_individual_delivery_display_number( $delivery_order );
        $delivery_order->add_order_note( 'Публичный номер счёта на доставку №' . $display_number . '; основной заказ №' . $main_order->get_order_number() . '.' );
        $main_order->add_order_note( 'Создан отдельный счёт на доставку №' . $display_number . ' на сумму ' . wp_strip_all_tags( wc_price( $amount, array( 'currency' => $main_order->get_currency() ) ) ) . '.' );

        $request_id = absint( $main_order->get_meta( '_ll_custom_request_id' ) );
        if ( $request_id && function_exists( 'loraleya_custom_order_add_history' ) ) {
            loraleya_custom_order_add_history( $request_id, 'Создан счёт на доставку №' . $display_number . ' для WooCommerce-заказа №' . $main_order->get_order_number() );
        }

        $mailer = WC()->mailer();
        $emails = $mailer ? $mailer->get_emails() : array();
        if ( isset( $emails['WC_Email_Customer_Invoice'] ) ) {
            $emails['WC_Email_Customer_Invoice']->trigger( $delivery_order->get_id(), $delivery_order );
            $delivery_order->update_meta_data( '_ll_delivery_invoice_email_sent_at', current_time( 'mysql' ) );
            $delivery_order->save_meta_data();
        }

        return $delivery_order;
    } catch ( Throwable $error ) {
        loraleya_individual_delivery_creation_context( false );
        if ( $delivery_order instanceof WC_Order && ! loraleya_individual_delivery_is_paid( $delivery_order ) ) {
            $delivery_order->update_status( 'cancelled', 'Создание счёта не завершено: ' . sanitize_text_field( $error->getMessage() ) );
        }
        return new WP_Error( 'delivery_invoice_failed', $error->getMessage() );
    } finally {
        if ( function_exists( 'loraleya_custom_order_release_option_lock' ) ) {
            loraleya_custom_order_release_option_lock( $lock_name, $lock );
        }
    }
}

function loraleya_individual_delivery_cancel_invoice( $main_order ) {
    if ( ! loraleya_individual_delivery_is_main_order( $main_order ) ) {
        return new WP_Error( 'invalid_main_order', 'Основной индивидуальный заказ не найден.' );
    }
    $delivery_order = loraleya_individual_delivery_active_order( $main_order );
    if ( ! $delivery_order ) {
        return new WP_Error( 'invoice_not_found', 'Активный счёт на доставку не найден.' );
    }
    if ( loraleya_individual_delivery_is_paid( $delivery_order ) || $delivery_order->has_status( array( 'processing', 'completed', 'refunded' ) ) ) {
        return new WP_Error( 'paid_invoice', 'Оплаченный счёт нельзя отменить этой кнопкой.' );
    }
    if ( ! $delivery_order->has_status( 'cancelled' ) ) {
        $delivery_order->update_status( 'cancelled', 'Счёт на доставку отменён менеджером.', true );
    }
    $main_order = wc_get_order( $main_order->get_id() );
    if ( absint( $main_order->get_meta( '_ll_active_delivery_payment_order_id' ) ) === $delivery_order->get_id() ) {
        $main_order->delete_meta_data( '_ll_active_delivery_payment_order_id' );
        $main_order->update_meta_data( '_ll_delivery_payment_status', 'cancelled' );
        $main_order->save_meta_data();
    }
    $display_number = loraleya_individual_delivery_display_number( $delivery_order );
    $main_order->add_order_note( 'Счёт на доставку №' . $display_number . ' отменён. Условия доставки разблокированы.' );
    $request_id = absint( $main_order->get_meta( '_ll_custom_request_id' ) );
    if ( $request_id && function_exists( 'loraleya_custom_order_add_history' ) ) {
        loraleya_custom_order_add_history( $request_id, 'Счёт на доставку №' . $display_number . ' отменён' );
    }
    return true;
}

function loraleya_individual_delivery_sync_status( $order_id, $old_status, $new_status, $order ) {
    if ( ! loraleya_individual_delivery_is_payment_order( $order ) ) {
        return;
    }
    $main_order = wc_get_order( absint( $order->get_meta( '_ll_delivery_parent_order_id' ) ) );
    if ( ! loraleya_individual_delivery_is_main_order( $main_order ) ) {
        return;
    }

    $status = loraleya_individual_delivery_is_paid( $order ) ? 'paid' : $new_status;
    $main_order->update_meta_data( '_ll_delivery_payment_status', $status );
    if ( 'cancelled' === $new_status && ! loraleya_individual_delivery_is_paid( $order ) && absint( $main_order->get_meta( '_ll_active_delivery_payment_order_id' ) ) === $order->get_id() ) {
        $main_order->delete_meta_data( '_ll_active_delivery_payment_order_id' );
    }
    $main_order->save_meta_data();
    $main_order->add_order_note( 'Статус счёта на доставку №' . loraleya_individual_delivery_display_number( $order ) . ': ' . loraleya_individual_delivery_status_label( $order ) . '.' );
}
add_action( 'woocommerce_order_status_changed', 'loraleya_individual_delivery_sync_status', 5, 4 );

function loraleya_individual_delivery_completion_block_reason( $order ) {
    if ( ! loraleya_individual_delivery_is_main_order( $order ) ) {
        return '';
    }
    $condition = (string) $order->get_meta( '_ll_delivery_payment_condition' );
    if ( ! in_array( $condition, array( 'free', 'invoice', 'buyer' ), true ) ) {
        return 'Условия доставки ещё не определены.';
    }
    if ( 'invoice' === $condition ) {
        $delivery_order = loraleya_individual_delivery_active_order( $order );
        if ( ! $delivery_order || ! loraleya_individual_delivery_is_paid( $delivery_order ) ) {
            return 'Счёт на доставку ещё не оплачен.';
        }
    }
    return '';
}

/** Stop the admin status before WC_Order::set_status() and before YooKassa status callbacks. */
function loraleya_individual_delivery_guard_admin_completed( $order_id, $object = null ) {
    if (
        ! current_user_can( 'manage_woocommerce' )
        || ! loraleya_individual_delivery_admin_nonce_is_valid()
        || ! isset( $_POST['order_status'] )
        || ! is_scalar( $_POST['order_status'] )
    ) {
        return;
    }
    $requested = sanitize_key( wp_unslash( $_POST['order_status'] ) );
    if ( 'completed' !== str_replace( 'wc-', '', $requested ) ) {
        return;
    }
    $order = $object instanceof WC_Order ? $object : wc_get_order( $order_id );
    $reason = loraleya_individual_delivery_completion_block_reason( $order );
    if ( '' === $reason ) {
        return;
    }
    $_POST['order_status'] = 0 === strpos( $requested, 'wc-' ) ? 'wc-' . $order->get_status() : $order->get_status();
    loraleya_individual_delivery_admin_error( 'Статус «Выполнен» не сохранён. ' . $reason );
}
add_action( 'woocommerce_process_shop_order_meta', 'loraleya_individual_delivery_guard_admin_completed', 1, 2 );

function loraleya_individual_delivery_guard_bulk_completed( $order_ids, $action, $object_type ) {
    if ( 'mark_completed' !== $action || 'order' !== $object_type || ! current_user_can( 'manage_woocommerce' ) ) {
        return $order_ids;
    }
    $allowed = array();
    $blocked = 0;
    foreach ( (array) $order_ids as $order_id ) {
        $order = wc_get_order( $order_id );
        if ( loraleya_individual_delivery_completion_block_reason( $order ) ) {
            ++$blocked;
        } else {
            $allowed[] = $order_id;
        }
    }
    if ( $blocked ) {
        set_transient( '_ll_delivery_bulk_blocked_' . get_current_user_id(), $blocked, 60 );
    }
    return $allowed;
}
add_filter( 'woocommerce_bulk_action_ids', 'loraleya_individual_delivery_guard_bulk_completed', 1, 3 );

function loraleya_individual_delivery_bulk_admin_notice() {
    $key     = '_ll_delivery_bulk_blocked_' . get_current_user_id();
    $blocked = absint( get_transient( $key ) );
    if ( ! $blocked ) {
        return;
    }
    delete_transient( $key );
    echo '<div class="notice notice-error is-dismissible"><p>'
        . esc_html( sprintf( 'Не переведено в «Выполнен» индивидуальных заказов: %d. Проверьте условия и оплату доставки.', $blocked ) )
        . '</p></div>';
}
add_action( 'admin_notices', 'loraleya_individual_delivery_bulk_admin_notice' );

function loraleya_individual_delivery_hide_completed_row_action( $actions, $order = null ) {
    if ( loraleya_individual_delivery_completion_block_reason( $order ) ) {
        unset( $actions['complete'], $actions['completed'] );
    }
    return $actions;
}
add_filter( 'woocommerce_admin_order_actions', 'loraleya_individual_delivery_hide_completed_row_action', 20, 2 );

function loraleya_individual_delivery_payment_complete_status( $status, $order_id, $order ) {
    if ( loraleya_individual_delivery_is_payment_order( $order ) ) {
        return 'processing';
    }
    if ( 'completed' === $status && loraleya_individual_delivery_completion_block_reason( $order ) ) {
        return 'processing';
    }
    return $status;
}
add_filter( 'woocommerce_payment_complete_order_status', 'loraleya_individual_delivery_payment_complete_status', 5, 3 );

function loraleya_individual_delivery_invoice_email_context( $order = null ) {
    static $current_order = false;
    if ( null !== $order ) {
        $current_order = $order;
    }
    return $current_order;
}

function loraleya_individual_delivery_invoice_subject( $subject, $order ) {
    if ( loraleya_individual_delivery_is_payment_order( $order ) ) {
        $main = wc_get_order( absint( $order->get_meta( '_ll_delivery_parent_order_id' ) ) );
        return 'Доставка по заказу №' . ( $main ? $main->get_order_number() : $order->get_order_number() ) . ' готова к оплате';
    }
    return $subject;
}
add_filter( 'woocommerce_email_subject_customer_invoice', 'loraleya_individual_delivery_invoice_subject', 100, 2 );

function loraleya_individual_delivery_invoice_hooks( $activate ) {
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
        add_action( 'woocommerce_email_order_details', 'loraleya_individual_delivery_invoice_details', 10, 4 );
        add_action( 'woocommerce_email_customer_details', 'loraleya_individual_delivery_invoice_restore', PHP_INT_MAX, 4 );
        $is_active = true;
        return;
    }

    if ( ! $is_active ) {
        return;
    }
    remove_action( 'woocommerce_email_order_details', 'loraleya_individual_delivery_invoice_details', 10 );
    remove_action( 'woocommerce_email_customer_details', 'loraleya_individual_delivery_invoice_restore', PHP_INT_MAX );
    foreach ( $removed_hooks as $hook ) {
        add_action( $hook[0], $hook[1], $hook[2], $hook[3] );
    }
    $removed_hooks = array();
    $is_active     = false;
}

function loraleya_individual_delivery_invoice_heading( $heading, $order ) {
    if ( loraleya_individual_delivery_is_payment_order( $order ) ) {
        loraleya_individual_delivery_invoice_email_context( $order );
        loraleya_individual_delivery_invoice_hooks( true );
        $main = wc_get_order( absint( $order->get_meta( '_ll_delivery_parent_order_id' ) ) );
        return 'Доставка по заказу №' . ( $main ? $main->get_order_number() : '—' ) . ' готова к оплате';
    }
    return $heading;
}
add_filter( 'woocommerce_email_heading_customer_invoice', 'loraleya_individual_delivery_invoice_heading', 100, 2 );

function loraleya_individual_delivery_invoice_gettext( $translation, $text, $domain ) {
    $order = loraleya_individual_delivery_invoice_email_context();
    if ( ! loraleya_individual_delivery_is_payment_order( $order ) || 'woocommerce' !== $domain ) {
        return $translation;
    }
    if ( 'Pay for this order' === $text ) {
        return 'Оплатить доставку';
    }
    $invoice_texts = array(
        'An order has been created for you on %1$s. Your order details are below, with a link to make payment when you’re ready: %2$s',
        "An order has been created for you on %1\$s. Your order details are below, with a link to make payment when you're ready: %2\$s",
        'An order has been created for you on %1$s. Your invoice is below, with a link to make payment when you’re ready: %2$s',
        "An order has been created for you on %1\$s. Your invoice is below, with a link to make payment when you're ready: %2\$s",
        "An order has been created for you on %s. The order details are as follows, with a link to make payment when you're ready: %s",
        'Sorry, your order on %1$s was unsuccessful. Your order details are below, with a link to try your payment again: %2$s',
    );
    if ( in_array( $text, $invoice_texts, true ) ) {
        return '';
    }
    return $translation;
}
add_filter( 'gettext', 'loraleya_individual_delivery_invoice_gettext', 100, 3 );

function loraleya_individual_delivery_invoice_details( $order, $sent_to_admin, $plain_text, $email ) {
    if ( $sent_to_admin || ! $email instanceof WC_Email_Customer_Invoice || ! loraleya_individual_delivery_is_payment_order( $order ) ) {
        return;
    }
    $main           = wc_get_order( absint( $order->get_meta( '_ll_delivery_parent_order_id' ) ) );
    $carrier_labels = loraleya_individual_delivery_carrier_labels();
    $carrier        = (string) $order->get_meta( '_ll_delivery_carrier' );
    $carrier_label  = isset( $carrier_labels[ $carrier ] ) ? $carrier_labels[ $carrier ] : $carrier;
    $request_number = loraleya_individual_delivery_request_number( $order );
    $amount         = wc_price( $order->get_total(), array( 'currency' => $order->get_currency() ) );
    $display_number = loraleya_individual_delivery_display_number( $order );
    $payment_url    = $order->get_checkout_payment_url();
    if ( $plain_text ) {
        echo 'Счёт на доставку: №' . esc_html( $display_number ) . "\n";
        echo 'Основной заказ: №' . esc_html( $main ? $main->get_order_number() : '—' ) . "\n";
        if ( $request_number ) {
            echo 'Индивидуальная заявка: ' . esc_html( $request_number ) . "\n";
        }
        echo 'Перевозчик: ' . esc_html( $carrier_label ) . "\n";
        echo 'Сумма доставки: ' . wp_strip_all_tags( $amount ) . "\n\n";
        echo "Это отдельная оплата доставки. Изделия повторно оплачивать не нужно.\n\n";
        echo 'Оплатить доставку: ' . esc_url_raw( $payment_url ) . "\n\n";
        return;
    }
    echo '<div style="margin:0 0 24px;padding:16px;border-left:4px solid #b08d57;background:#f7f4ef">';
    echo '<p style="margin:0 0 10px"><strong>Счёт на доставку: №' . esc_html( $display_number ) . '</strong><br>';
    echo 'Основной заказ: №' . esc_html( $main ? $main->get_order_number() : '—' ) . '<br>';
    if ( $request_number ) {
        echo 'Индивидуальная заявка: ' . esc_html( $request_number ) . '<br>';
    }
    echo 'Перевозчик: ' . esc_html( $carrier_label ) . '<br>Сумма доставки: ' . wp_kses_post( $amount ) . '</p>';
    echo '<p style="margin:0 0 14px">Это отдельная оплата доставки. Изделия повторно оплачивать не нужно.</p>';
    echo '<p style="margin:0"><a href="' . esc_url( $payment_url ) . '" style="display:inline-block;padding:11px 18px;background:#2c241f;color:#fff;text-decoration:none;border:1px solid #b08d57">Оплатить доставку</a></p></div>';
}

function loraleya_individual_delivery_invoice_restore( $order, $sent_to_admin, $plain_text, $email ) {
    if ( ! $sent_to_admin && $email instanceof WC_Email_Customer_Invoice && loraleya_individual_delivery_is_payment_order( $order ) ) {
        loraleya_individual_delivery_invoice_hooks( false );
        loraleya_individual_delivery_invoice_email_context( false );
    }
}

function loraleya_individual_delivery_invoice_additional_content( $content, $order ) {
    return loraleya_individual_delivery_is_payment_order( $order ) ? '' : $content;
}
add_filter( 'woocommerce_email_additional_content_customer_invoice', 'loraleya_individual_delivery_invoice_additional_content', 100, 2 );

function loraleya_individual_delivery_order_totals( $totals, $order ) {
    if ( ! loraleya_individual_delivery_is_payment_order( $order ) ) {
        return $totals;
    }
    if ( isset( $totals['shipping'] ) ) {
        $totals['shipping']['label'] = 'Доставка:';
    }
    unset( $totals['payment_method'] );
    if ( loraleya_individual_delivery_current_pay_order() ) {
        unset( $totals['cart_subtotal'] );
    }
    return $totals;
}
add_filter( 'woocommerce_get_order_item_totals', 'loraleya_individual_delivery_order_totals', 100, 2 );

function loraleya_individual_delivery_pay_button_text( $text ) {
    return loraleya_individual_delivery_current_pay_order() ? 'Оплатить доставку' : $text;
}
add_filter( 'woocommerce_pay_order_button_text', 'loraleya_individual_delivery_pay_button_text', 100 );

function loraleya_individual_delivery_pay_endpoint_title( $title ) {
    $order = loraleya_individual_delivery_current_pay_order();
    return $order ? 'Оплатить доставку №' . loraleya_individual_delivery_display_number( $order ) : $title;
}
add_filter( 'woocommerce_endpoint_order-pay_title', 'loraleya_individual_delivery_pay_endpoint_title', 100 );

/**
 * Customer-facing cleanup for main individual orders and technical delivery invoices.
 */
function loraleya_individual_customer_is_waiting_for_payment( $order ) {
    return loraleya_individual_delivery_is_main_order( $order )
        && ! loraleya_individual_delivery_is_paid( $order )
        && $order->has_status( array( 'pending', 'on-hold' ) );
}

function loraleya_individual_customer_email_order_context( $order = null ) {
    static $current_order = false;
    if ( null !== $order ) {
        $current_order = $order;
    }
    return $current_order;
}

function loraleya_individual_customer_email_context_begin( $heading, $email ) {
    $order = $email instanceof WC_Email && $email->is_customer_email() && $email->object instanceof WC_Order
        ? $email->object
        : false;
    loraleya_individual_customer_email_order_context( $order );
}
add_action( 'woocommerce_email_header', 'loraleya_individual_customer_email_context_begin', 1, 2 );

function loraleya_individual_customer_email_context_end( $email ) {
    loraleya_individual_customer_email_order_context( false );
}
add_action( 'woocommerce_email_footer', 'loraleya_individual_customer_email_context_end', PHP_INT_MAX, 1 );

function loraleya_individual_customer_current_view_order() {
    global $wp;

    if ( ! function_exists( 'is_wc_endpoint_url' ) || ! is_wc_endpoint_url( 'view-order' ) ) {
        return false;
    }
    $order_id = isset( $wp->query_vars['view-order'] ) ? absint( $wp->query_vars['view-order'] ) : 0;
    return $order_id ? wc_get_order( $order_id ) : false;
}

function loraleya_individual_customer_status_names( $statuses ) {
    $order = loraleya_individual_customer_email_order_context();
    if ( ! $order ) {
        $order = loraleya_individual_customer_current_view_order();
    }
    if ( loraleya_individual_customer_is_waiting_for_payment( $order ) ) {
        if ( isset( $statuses['wc-on-hold'] ) ) {
            $statuses['wc-on-hold'] = 'Ожидает оплаты';
        }
        if ( isset( $statuses['wc-pending'] ) ) {
            $statuses['wc-pending'] = 'Ожидает оплаты';
        }
    }
    return $statuses;
}
add_filter( 'wc_order_statuses', 'loraleya_individual_customer_status_names', 100 );

function loraleya_individual_customer_orders_status_column( $order ) {
    $status = loraleya_individual_customer_is_waiting_for_payment( $order )
        ? 'Ожидает оплаты'
        : wc_get_order_status_name( $order->get_status() );
    echo esc_html( $status );
}
add_action( 'woocommerce_my_account_my_orders_column_order-status', 'loraleya_individual_customer_orders_status_column', 10, 1 );

function loraleya_individual_customer_order_details_status( $status_text, $order ) {
    if ( ! loraleya_individual_customer_is_waiting_for_payment( $order ) ) {
        return $status_text;
    }
    return preg_replace(
        '/(<mark\s+class=["\']order-status["\']>).*?(<\/mark>)/u',
        '$1' . esc_html( 'Ожидает оплаты' ) . '$2',
        $status_text,
        1
    );
}
add_filter( 'woocommerce_order_details_status', 'loraleya_individual_customer_order_details_status', 100, 2 );

function loraleya_individual_customer_exclude_delivery_orders( $query ) {
    $exclude_delivery = array(
        'relation' => 'OR',
        array(
            'key'     => '_ll_delivery_payment_order',
            'compare' => 'NOT EXISTS',
        ),
        array(
            'key'     => '_ll_delivery_payment_order',
            'value'   => 'yes',
            'compare' => '!=',
        ),
    );

    if ( ! empty( $query['meta_query'] ) && is_array( $query['meta_query'] ) ) {
        $query['meta_query'] = array(
            'relation' => 'AND',
            $query['meta_query'],
            $exclude_delivery,
        );
    } else {
        $query['meta_query'] = array( $exclude_delivery );
    }
    return $query;
}
add_filter( 'woocommerce_my_account_my_orders_query', 'loraleya_individual_customer_exclude_delivery_orders', 100 );

function loraleya_individual_customer_is_order_surface() {
    if ( function_exists( 'is_account_page' ) && is_account_page() ) {
        return true;
    }
    if ( ! function_exists( 'is_wc_endpoint_url' ) ) {
        return false;
    }
    return is_wc_endpoint_url( 'view-order' )
        || is_wc_endpoint_url( 'order-pay' )
        || is_wc_endpoint_url( 'order-received' );
}

function loraleya_individual_customer_hide_manager_payment_method( $totals, $order ) {
    if (
        ! loraleya_individual_delivery_is_main_order( $order )
        || 'll_manager_confirmation' !== $order->get_payment_method()
    ) {
        return $totals;
    }

    $email_order        = loraleya_individual_customer_email_order_context();
    $is_customer_email  = $email_order instanceof WC_Order && $email_order->get_id() === $order->get_id();
    $is_customer_screen = ! $email_order && loraleya_individual_customer_is_order_surface();
    if ( $is_customer_email || $is_customer_screen ) {
        unset( $totals['payment_method'] );
    }
    return $totals;
}
add_filter( 'woocommerce_get_order_item_totals', 'loraleya_individual_customer_hide_manager_payment_method', 120, 2 );
