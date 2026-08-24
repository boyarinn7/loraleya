<?php
/**
 * Template Name: Индивидуальный заказ
 *
 * Привязывается к странице /custom-order/ по slug, либо назначается
 * вручную через атрибут шаблона страницы в админке.
 */

get_header();

$colors = get_terms([
    'taxonomy'   => 'pa_fabric_color',
    'hide_empty' => false,
    'orderby'    => 'name',
]);

$item_types = [
    'tablecloth' => 'Скатерть',
    'runner'     => 'Дорожка',
    'napkins'    => 'Салфетки',
    'kuverts'    => 'Куверты',
    'curtains'   => 'Шторы',
    'other'      => 'Другое',
];

$render_item_card = static function ($is_template = false) use ($colors, $item_types) {
    ?>
    <article class="co-product-card" data-item-card>
        <div class="co-product-card-head">
            <h3 class="co-product-title">Изделие <span data-item-number>1</span></h3>
            <button type="button" class="co-product-remove" data-remove-item <?php echo $is_template ? '' : 'hidden'; ?>>Удалить</button>
        </div>

        <div class="co-product-grid">
            <div class="co-ct-field">
                <label class="co-ct-label">Изделие</label>
                <select class="co-ct-input" data-item-field="item_type" required>
                    <option value="">Выберите изделие</option>
                    <?php foreach ($item_types as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="co-ct-field co-other-name" data-other-name hidden>
                <label class="co-ct-label">Название изделия</label>
                <input type="text" class="co-ct-input" data-item-field="item_name" maxlength="120" placeholder="Например: подхват для штор">
            </div>
            <div class="co-ct-field co-product-size">
                <label class="co-ct-label">Размер / параметры</label>
                <input type="text" class="co-ct-input" data-item-field="size" maxlength="160" placeholder="Например: 140×40 см, 150×250 см, 150×270 см" required>
            </div>
        </div>

        <div class="co-product-field">
            <div class="co-ct-label">Цвет</div>
            <div class="co-item-swatches" data-item-colors>
                <?php if (!empty($colors) && !is_wp_error($colors)) : ?>
                    <?php foreach ($colors as $color) : ?>
                        <?php
                        $hex = get_term_meta($color->term_id, 'color_hex', true) ?: '#888';
                        $swatch_url = function_exists('loraleya_color_swatch_url')
                            ? loraleya_color_swatch_url($color->slug)
                            : '';
                        $bg_style = $swatch_url
                            ? 'background-image:url(' . esc_url($swatch_url) . ');background-color:' . esc_attr($hex) . ';'
                            : 'background:' . esc_attr($hex) . ';';
                        ?>
                        <button
                            type="button"
                            class="co-item-swatch"
                            data-color-slug="<?php echo esc_attr($color->slug); ?>"
                            data-color-name="<?php echo esc_attr($color->name); ?>"
                            aria-pressed="false"
                        >
                            <span class="co-item-swatch-chip" style="<?php echo $bg_style; ?>"></span>
                            <span class="co-item-swatch-name"><?php echo esc_html($color->name); ?></span>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="co-product-grid co-product-grid--bottom">
            <div class="co-ct-field">
                <label class="co-ct-label">Количество</label>
                <div class="co-qty">
                    <button type="button" class="co-qty-btn" data-qty-delta="-1" aria-label="Уменьшить количество">−</button>
                    <input type="number" class="co-qty-val" data-item-field="quantity" value="1" min="1" step="1" inputmode="numeric" required>
                    <button type="button" class="co-qty-btn" data-qty-delta="1" aria-label="Увеличить количество">+</button>
                </div>
            </div>
            <div class="co-ct-field co-product-comment">
                <label class="co-ct-label">Комментарий к изделию</label>
                <textarea class="co-ct-input" data-item-field="comment" maxlength="2000" placeholder="Особенности пошива, отделка, крепление и другие пожелания"></textarea>
            </div>
        </div>
    </article>
    <?php
};
?>

<section class="co-hero">
    <div class="co-container">
        <div class="co-eyebrow">Индивидуальный заказ</div>
        <h1 class="co-h1">Создадим текстиль <em>специально для вас</em></h1>
        <p class="co-hero-desc">Добавьте одно или несколько изделий в заявку, укажите размер, цвет, количество и пожелания. Мы свяжемся с вами, согласуем детали, стоимость, сроки и доставку — и только после этого оформим заказ.</p>

        <div class="co-features">
            <div class="co-feature">
                <div class="co-feature-icon">✂</div>
                <div class="co-feature-text"><strong>Любые изделия</strong>Скатерти, дорожки, салфетки, куверты, шторы и многое другое</div>
            </div>
            <div class="co-feature">
                <div class="co-feature-icon">◇</div>
                <div class="co-feature-text"><strong>Каждое изделие — отдельно</strong>Укажите свой размер, цвет, количество и пожелания</div>
            </div>
            <div class="co-feature">
                <div class="co-feature-icon">✓</div>
                <div class="co-feature-text"><strong>Сначала согласуем</strong>Уточним состав, стоимость, сроки и доставку до оформления заказа</div>
            </div>
        </div>
    </div>
</section>

<section class="co-config">
    <div class="co-container">
        <div class="co-config-box">
            <form id="customOrderForm" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" novalidate>
                <input type="hidden" name="action" value="loraleya_custom_order">

                <div class="co-step">
                    <div class="co-step-head">
                        <div class="co-step-num">1</div>
                        <div class="co-step-title">Изделия</div>
                    </div>
                    <div class="co-step-hint">Если изделия отличаются по размеру, цвету или другим параметрам, добавьте их отдельными позициями.</div>
                    <div class="co-product-list" id="coProductList">
                        <?php $render_item_card(false); ?>
                    </div>
                    <button type="button" class="co-add-product" id="coAddProduct"><span class="co-add-product-plus" aria-hidden="true">+</span><span>Добавить ещё изделие</span></button>
                    <template id="coProductTemplate"><?php $render_item_card(true); ?></template>
                </div>

                <div class="co-step">
                    <div class="co-step-head">
                        <div class="co-step-num">2</div>
                        <div class="co-step-title">Контактные данные</div>
                    </div>
                    <div class="co-contact">
                        <div class="co-ct-row">
                            <div class="co-ct-field">
                                <label class="co-ct-label" for="coName">ФИО</label>
                                <input type="text" id="coName" name="customer_name" class="co-ct-input" placeholder="Фамилия, имя и отчество" maxlength="120" autocomplete="name" required>
                            </div>
                            <div class="co-ct-field">
                                <label class="co-ct-label" for="coContact">Телефон</label>
                                <input type="tel" id="coContact" name="customer_contact" class="co-ct-input" placeholder="+79991234567" inputmode="tel" autocomplete="tel" required>
                            </div>
                        </div>
                        <div class="co-ct-row">
                            <div class="co-ct-field">
                                <label class="co-ct-label" for="coEmail">Email</label>
                                <input type="email" id="coEmail" name="customer_email" class="co-ct-input" placeholder="name@example.ru" autocomplete="email" required>
                            </div>
                            <div class="co-ct-field">
                                <label class="co-ct-label" for="coDeliveryAddress">Адрес доставки</label>
                                <input type="text" id="coDeliveryAddress" name="delivery_address" class="co-ct-input" placeholder="Город, улица, дом, квартира" maxlength="500" autocomplete="street-address" required>
                                <span class="co-field-hint">Используем для будущего согласования доставки. Стоимость сейчас не рассчитывается.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="co-summary" id="coSummary">
                    <div class="co-sum-title">Ваша заявка</div>
                    <div class="co-sum-items" id="coSumItems" aria-live="polite"></div>
                </div>

                <div class="co-order-comment">
                    <div class="co-step-title">Комментарий к заказу</div>
                    <div class="co-ct-field">
                        <label class="co-ct-label" for="coNotes">Общий комментарий</label>
                        <textarea id="coNotes" name="customer_notes" class="co-ct-input" maxlength="2000" placeholder="Пожелания ко всему заказу или дополнительная информация"></textarea>
                    </div>
                </div>

                <input type="text" name="website" class="co-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">
                <?php wp_nonce_field('loraleya_custom_order', 'co_nonce'); ?>

                <div class="co-consent">
                    <input type="checkbox" class="co-opt-cb" id="coConsent" name="consent" value="1" required>
                    <label for="coConsent">
                        <div class="co-opt-label">Согласен с <a href="<?php echo esc_url(get_privacy_policy_url() ?: home_url('/privacy-policy/')); ?>" target="_blank" rel="noopener">политикой обработки персональных данных</a></div>
                    </label>
                </div>

                <div class="co-submit-area">
                    <div class="co-submit-info">После отправки заявки мы свяжемся с вами, уточним детали, стоимость, сроки и доставку. Заказ будет оформлен только после согласования.</div>
                    <button type="submit" class="co-btn-submit">Отправить заявку</button>
                </div>

                <div class="co-result co-result--success" id="coResultSuccess" hidden></div>
                <div class="co-result co-result--error" id="coResultError" hidden>
                    <strong>Ошибка отправки.</strong> Попробуйте ещё раз или напишите нам напрямую.
                </div>
            </form>
        </div>
    </div>
</section>

<section class="co-faq-sec">
    <div class="co-container">
        <h2 class="co-faq-title">Частые вопросы</h2>

        <div class="co-faq">
            <div class="co-faq-q">Какие сроки изготовления?</div>
            <div class="co-faq-a">Стандартный срок — 7–14 рабочих дней. Для срочных заказов возможно изготовление за 3–5 дней с доплатой 30%.</div>
        </div>
        <div class="co-faq">
            <div class="co-faq-q">Можно ли заказать цвет, которого нет в палитре?</div>
            <div class="co-faq-a">Мы работаем с 17 цветами жаккардовой ткани. Если нужен оттенок вне палитры — напишите в комментарии, подберём ближайший вариант или предложим альтернативу.</div>
        </div>
        <div class="co-faq">
            <div class="co-faq-q">Как происходит оплата?</div>
            <div class="co-faq-a">После согласования всех параметров индивидуального заказа и его стоимости мы сформируем заказ и отправим ссылку на оплату. До начала изготовления оплачивается 100% стоимости изделия. Стоимость доставки в эту сумму не входит.</div>
        </div>
        <div class="co-faq">
            <div class="co-faq-q">Как оплачивается доставка?</div>
            <div class="co-faq-a">Для индивидуальных заказов при доставке СДЭК или Яндекс стоимость доставки оплачивается покупателем при получении по тарифам выбранной службы доставки. Передача готового заказа службе доставки со стороны LoraLeya осуществляется бесплатно. При доставке 5Post по Москве и Московской области доставка бесплатна. В другие регионы стоимость доставки составляет 250 ₽ и оплачивается отдельно перед отправкой. Доставка через 5Post возможна при условии, что фактические габариты и вес готового заказа соответствуют требованиям службы. Если готовый заказ не соответствует требованиям 5Post по габаритам или весу, покупателю предлагается другой доступный способ доставки.</div>
        </div>
        <div class="co-faq">
            <div class="co-faq-q">Насколько точно соблюдаются размеры?</div>
            <div class="co-faq-a">Мы шьём изделие по согласованным с вами размерам. Поскольку изделие изготавливается вручную, допустима технологическая погрешность в пределах 0,5–1,5 см. Если отклонение превышает указанную погрешность по нашей вине, мы исправим изделие бесплатно.</div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
