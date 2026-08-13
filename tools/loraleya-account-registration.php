<?php
/**
 * Plugin Name: LoraLeya Account Registration
 * Description: Registration form, consent storage and e-mail verification for LoraLeya customers.
 */
defined( 'ABSPATH' ) || exit;

function ll_reg_post( $key ) {
    return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
}

function ll_reg_input( $name, $label, $type = 'text', $autocomplete = '' ) {
    $id    = 'reg_' . $name;
    $value = 'password' === $type ? '' : ll_reg_post( $name );
    echo '<p class="woocommerce-form-row form-row form-row-wide ll-reg-field ll-reg-' . esc_attr( $name ) . '">'
        . '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . ' <span class="required">*</span></label>'
        . '<input class="woocommerce-Input input-text" type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" autocomplete="' . esc_attr( $autocomplete ) . '" value="' . esc_attr( $value ) . '" required aria-required="true">'
        . '</p>';
}

add_action( 'woocommerce_register_form_start', function () {
    ll_reg_input( 'last_name', 'Фамилия', 'text', 'family-name' );
    ll_reg_input( 'first_name', 'Имя', 'text', 'given-name' );
    ll_reg_input( 'billing_phone', 'Телефон', 'tel', 'tel' );
} );

add_action( 'woocommerce_register_form', function () {
    ll_reg_input( 'password_confirm', 'Подтвердите пароль', 'password', 'new-password' );
}, 5 );

add_action( 'woocommerce_register_form', function () {
    echo '<p class="form-row ll-reg-consent"><label class="woocommerce-form__label woocommerce-form__label-for-checkbox">'
        . '<input type="checkbox" name="loraleya_marketing_consent" value="1" ' . checked( ! empty( $_POST['loraleya_marketing_consent'] ), true, false ) . '>'
        . '<span>Я разрешаю отправлять мне информационные и рекламные рассылки по электронной почте.</span></label></p>';
}, 20 );

add_filter( 'woocommerce_process_registration_errors', function ( $errors, $username, $password, $email ) {
    $phone  = ll_reg_post( 'billing_phone' );
    $digits = preg_replace( '/\D+/', '', $phone );
    $repeat = isset( $_POST['password_confirm'] ) ? (string) wp_unslash( $_POST['password_confirm'] ) : '';

    if ( '' === ll_reg_post( 'last_name' ) ) $errors->add( 'll_last_name', 'Укажите фамилию.' );
    if ( '' === ll_reg_post( 'first_name' ) ) $errors->add( 'll_first_name', 'Укажите имя.' );
    if ( '' === $phone ) $errors->add( 'll_phone', 'Укажите телефон.' );
    elseif ( strlen( $digits ) < 10 || strlen( $digits ) > 15 ) $errors->add( 'll_phone_format', 'Проверьте номер телефона.' );
    if ( strlen( (string) $password ) < 8 ) $errors->add( 'll_password_length', 'Пароль должен содержать не менее 8 символов.' );
    if ( '' === $repeat || ! hash_equals( (string) $password, $repeat ) ) $errors->add( 'll_password_repeat', 'Пароли не совпадают.' );
    if ( empty( $_POST['loraleya_privacy_consent'] ) ) $errors->add( 'll_privacy', 'Для регистрации необходимо согласие на обработку персональных данных.' );
    return $errors;
}, 20, 4 );

add_filter( 'woocommerce_new_customer_data', function ( $data ) {
    if ( ! empty( $_POST['register'] ) ) {
        $data['first_name'] = ll_reg_post( 'first_name' );
        $data['last_name']  = ll_reg_post( 'last_name' );
    }
    return $data;
} );

function ll_reg_token_hash( $token ) {
    return hash_hmac( 'sha256', (string) $token, wp_salt( 'auth' ) );
}

function ll_reg_mail( $user_id, $subject, $message ) {
    $user = get_user_by( 'id', $user_id );
    return $user ? wp_mail( $user->user_email, $subject, $message ) : false;
}

function ll_reg_confirmation_mail( $user_id, $token ) {
    $name = get_user_meta( $user_id, 'first_name', true );
    $url  = add_query_arg( array( 'll_verify' => 1, 'uid' => $user_id, 'token' => $token ), wc_get_page_permalink( 'myaccount' ) );
    return ll_reg_mail(
        $user_id,
        'Подтвердите адрес электронной почты — LoraLeya',
        ( $name ? 'Здравствуйте, ' . $name . '!' : 'Здравствуйте!' ) . "\n\n"
        . "Спасибо за регистрацию на сайте LoraLeya. Подтвердите адрес электронной почты по ссылке:\n" . $url . "\n\n"
        . 'Ссылка действительна 48 часов. Если вы не регистрировались на сайте, проигнорируйте это письмо.'
    );
}

function ll_reg_welcome_mail( $user_id ) {
    $user = get_user_by( 'id', $user_id );
    if ( ! $user ) return false;
    $name = get_user_meta( $user_id, 'first_name', true );
    return ll_reg_mail(
        $user_id,
        'Спасибо за регистрацию — LoraLeya',
        ( $name ? 'Здравствуйте, ' . $name . '!' : 'Здравствуйте!' ) . "\n\n"
        . "Спасибо за регистрацию. Ваш адрес подтверждён, личный кабинет активирован.\n\n"
        . 'Логин: ' . $user->user_login . "\nПароль: тот, который вы указали при регистрации.\n\n"
        . "В целях безопасности пароль не хранится и не отправляется в письмах.\n"
        . 'Войти: ' . wc_get_page_permalink( 'myaccount' )
    );
}

add_action( 'woocommerce_created_customer', function ( $customer_id, $data, $generated ) {
    if ( empty( $_POST['register'] ) ) return;
    $first = ll_reg_post( 'first_name' );
    $last  = ll_reg_post( 'last_name' );
    $token = wp_generate_password( 40, false, false );
    update_user_meta( $customer_id, 'billing_first_name', $first );
    update_user_meta( $customer_id, 'billing_last_name', $last );
    update_user_meta( $customer_id, 'billing_phone', ll_reg_post( 'billing_phone' ) );
    update_user_meta( $customer_id, '_ll_privacy_consent', 'yes' );
    update_user_meta( $customer_id, '_ll_privacy_consent_time', current_time( 'mysql', true ) );
    update_user_meta( $customer_id, '_ll_marketing_consent', empty( $_POST['loraleya_marketing_consent'] ) ? 'no' : 'yes' );
    if ( ! empty( $_POST['loraleya_marketing_consent'] ) ) update_user_meta( $customer_id, '_ll_marketing_consent_time', current_time( 'mysql', true ) );
    update_user_meta( $customer_id, '_ll_email_verified', 'no' );
    update_user_meta( $customer_id, '_ll_verify_hash', ll_reg_token_hash( $token ) );
    update_user_meta( $customer_id, '_ll_verify_expires', time() + 2 * DAY_IN_SECONDS );
    update_user_meta( $customer_id, '_ll_verify_sent', ll_reg_confirmation_mail( $customer_id, $token ) ? 'yes' : 'no' );
}, 1, 3 );

add_filter( 'woocommerce_email_enabled_customer_new_account', function ( $enabled, $user ) {
    return $user instanceof WP_User && 'no' === get_user_meta( $user->ID, '_ll_email_verified', true ) ? false : $enabled;
}, 10, 2 );

add_filter( 'woocommerce_registration_auth_new_customer', function ( $auth, $customer_id ) {
    wc_clear_notices();
    $sent = 'yes' === get_user_meta( $customer_id, '_ll_verify_sent', true );
    wc_add_notice( $sent ? 'Учётная запись создана. Подтвердите адрес электронной почты по ссылке из письма.' : 'Учётная запись создана, но письмо не удалось отправить. Пожалуйста, свяжитесь с нами.', $sent ? 'success' : 'error' );
    return false;
}, 10, 2 );

add_filter( 'wp_authenticate_user', function ( $user ) {
    if ( ! is_wp_error( $user ) && 'no' === get_user_meta( $user->ID, '_ll_email_verified', true ) ) {
        return new WP_Error( 'll_email_unverified', 'Сначала подтвердите адрес электронной почты по ссылке из письма.' );
    }
    return $user;
}, 30 );

add_action( 'template_redirect', function () {
    if ( empty( $_GET['ll_verify'] ) ) return;
    $uid   = isset( $_GET['uid'] ) ? absint( $_GET['uid'] ) : 0;
    $token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
    $user  = $uid ? get_user_by( 'id', $uid ) : false;
    $error = '';

    if ( ! $user || ! $token ) $error = 'Ссылка подтверждения недействительна.';
    elseif ( 'yes' === get_user_meta( $uid, '_ll_email_verified', true ) ) {
        wc_add_notice( 'Адрес электронной почты уже подтверждён. Теперь вы можете войти.', 'success' );
    } else {
        $hash = (string) get_user_meta( $uid, '_ll_verify_hash', true );
        $time = (int) get_user_meta( $uid, '_ll_verify_expires', true );
        if ( ! $hash || ! hash_equals( $hash, ll_reg_token_hash( $token ) ) ) $error = 'Ссылка подтверждения недействительна.';
        elseif ( time() > $time ) $error = 'Срок действия ссылки истёк. Свяжитесь с нами для повторной отправки.';
        else {
            update_user_meta( $uid, '_ll_email_verified', 'yes' );
            update_user_meta( $uid, '_ll_email_verified_time', current_time( 'mysql', true ) );
            delete_user_meta( $uid, '_ll_verify_hash' );
            delete_user_meta( $uid, '_ll_verify_expires' );
            ll_reg_welcome_mail( $uid );
            wc_add_notice( 'Адрес электронной почты подтверждён. Теперь вы можете войти в личный кабинет.', 'success' );
        }
    }
    if ( $error ) wc_add_notice( $error, 'error' );
    wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
    exit;
}, 5 );

add_action( 'woocommerce_login_form_end', function () {
    echo '<p class="ll-account-switch ll-account-switch-register"><a href="#register" data-ll-panel="register">Зарегистрироваться</a></p>';
} );
add_action( 'woocommerce_register_form_end', function () {
    echo '<p class="ll-account-switch ll-account-switch-login">Уже зарегистрированы? <a href="#login" data-ll-panel="login">Войти</a></p>';
} );

add_action( 'wp_footer', function () {
    if ( ! is_account_page() || is_user_logged_in() ) return;
    $posted = ! empty( $_POST['register'] ) ? 'true' : 'false';
    $privacy = ! empty( $_POST['loraleya_privacy_consent'] ) ? 'true' : 'false';
    ?>
    <script>
    document.addEventListener('DOMContentLoaded',function(){
        var root=document.getElementById('customer_login'); if(!root)return;
        var login=root.querySelector('.u-column1'),reg=root.querySelector('.u-column2'); if(!login||!reg)return;
        var phone=document.getElementById('reg_billing_phone'),email=document.getElementById('reg_email');
        if(phone&&email)email.closest('.form-row').after(phone.closest('.form-row'));
        var consent=document.getElementById('loraleya_privacy_consent'); if(consent){consent.required=true;consent.setAttribute('aria-required','true');consent.checked=<?php echo $privacy; ?>;}
        function show(registration,focus){login.hidden=registration;reg.hidden=!registration;if(focus){var f=document.getElementById(registration?'reg_last_name':'username');if(f)f.focus();}}
        root.addEventListener('click',function(e){var a=e.target.closest('[data-ll-panel]');if(!a)return;e.preventDefault();var r=a.dataset.llPanel==='register';history.replaceState(null,'',r?'#register':'#login');show(r,true);});
        show(<?php echo $posted; ?>||location.hash==='#register',false);
    });
    </script>
    <?php
}, 30 );

add_action( 'wp_head', function () {
    if ( ! is_account_page() || is_user_logged_in() ) return;
    ?>
    <style>
    .woocommerce-account #customer_login{max-width:520px}
    .woocommerce-account #customer_login .u-column1,.woocommerce-account #customer_login .u-column2{float:none;width:100%;max-width:520px;padding:0;background:transparent;border:0}
    .woocommerce-account #customer_login .woocommerce-form-login,.woocommerce-account #customer_login .woocommerce-form-register{width:100%;max-width:520px;margin:0}
    .woocommerce-account #customer_login [hidden]{display:none!important}
    .woocommerce-account .woocommerce-form-login .woocommerce-LostPassword,.woocommerce-account .woocommerce-form-login .ll-account-switch-register{display:inline;margin:0}
    .woocommerce-account .woocommerce-form-login .ll-account-switch-register:before{content:" · ";color:var(--text-muted)}
    .woocommerce-account .ll-account-switch a{color:var(--gold);text-decoration:none}
    .woocommerce-account .ll-account-switch a:hover{color:var(--gold-light)}
    .woocommerce-account .ll-account-switch-login{margin:1rem 0 0;font-size:.85rem;color:var(--text-muted)}
    .woocommerce-account .ll-reg-consent{margin:1rem 0}
    .woocommerce-account .ll-reg-consent label,.woocommerce-account .woocommerce-form-register label.woocommerce-form__label-for-checkbox{display:flex;align-items:flex-start;gap:.55rem;margin:0;color:var(--text);font-size:.82rem;line-height:1.45;letter-spacing:0;text-transform:none}
    .woocommerce-account .ll-reg-consent input[type=checkbox],.woocommerce-account .woocommerce-form-register #loraleya_privacy_consent{flex:0 0 auto;width:auto;margin:.2rem 0 0}
    .woocommerce-account .woocommerce-form-register .woocommerce-password-strength,.woocommerce-account .woocommerce-form-register .woocommerce-password-hint{font-size:.72rem;line-height:1.45}
    @media(max-width:600px){.woocommerce-account #customer_login .woocommerce-form-login,.woocommerce-account #customer_login .woocommerce-form-register{padding:1.15rem}}
    </style>
    <?php
}, 30 );
