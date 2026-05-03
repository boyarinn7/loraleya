<?php
/**
 * Template Name: Индивидуальный заказ
 *
 * Привязывается к странице /custom-order/ по slug, либо назначается
 * вручную через атрибут шаблона страницы в админке.
 */

get_header();

// Получаем 17 цветов из таксономии pa_fabric_color
$colors = get_terms([
    'taxonomy'   => 'pa_fabric_color',
    'hide_empty' => false,
    'orderby'    => 'name',
]);
?>

<!-- HERO -->
<section class="co-hero">
    <div class="co-container">
        <div class="co-eyebrow">Индивидуальный заказ</div>
        <h1 class="co-h1">Создадим текстиль <em>под ваш стол</em></h1>
        <p class="co-hero-desc">Нестандартный размер стола, особый цвет, монограмма — мы сошьём именно то, что нужно. Расскажите нам о вашем столе, а мы рассчитаем стоимость и сроки.</p>

        <div class="co-features">
            <div class="co-feature">
                <div class="co-feature-icon">✂</div>
                <div class="co-feature-text">
                    <strong>Любой размер</strong>
                    Пошив под точные размеры вашего стола
                </div>
            </div>
            <div class="co-feature">
                <div class="co-feature-icon">◇</div>
                <div class="co-feature-text">
                    <strong>Монограмма</strong>
                    Инициалы или логотип на изделии
                </div>
            </div>
            <div class="co-feature">
                <div class="co-feature-icon">⟳</div>
                <div class="co-feature-text">
                    <strong>7–14 дней</strong>
                    Срок изготовления
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CONFIGURATOR -->
<section class="co-config">
    <div class="co-container">
        <div class="co-config-box">
            <form id="customOrderForm" novalidate>

                <!-- 1. Форма стола -->
                <div class="co-step">
                    <div class="co-step-head">
                        <div class="co-step-num">1</div>
                        <div class="co-step-title">Форма стола</div>
                    </div>
                    <div class="co-step-hint">Выберите форму вашего стола — от этого зависит крой скатерти</div>
                    <div class="co-shapes" data-field="shape">
                        <div class="co-shape co-shape--on" data-value="rect" data-name="Прямоугольный">
                            <svg viewBox="0 0 60 40"><rect x="5" y="5" width="50" height="30" rx="2"/></svg>
                            <div class="co-shape-label">Прямоугольный</div>
                        </div>
                        <div class="co-shape" data-value="oval" data-name="Овальный">
                            <svg viewBox="0 0 60 40"><ellipse cx="30" cy="20" rx="27" ry="15"/></svg>
                            <div class="co-shape-label">Овальный</div>
                        </div>
                        <div class="co-shape" data-value="round" data-name="Круглый">
                            <svg viewBox="0 0 60 40"><ellipse cx="30" cy="20" rx="18" ry="18"/></svg>
                            <div class="co-shape-label">Круглый</div>
                        </div>
                        <div class="co-shape" data-value="square" data-name="Квадратный">
                            <svg viewBox="0 0 60 40"><rect x="12" y="3" width="36" height="34" rx="2"/></svg>
                            <div class="co-shape-label">Квадратный</div>
                        </div>
                    </div>
                </div>

                <!-- 2. Размеры -->
                <div class="co-step">
                    <div class="co-step-head">
                        <div class="co-step-num">2</div>
                        <div class="co-step-title">Размеры стола</div>
                    </div>
                    <div class="co-step-hint">Укажите длину и ширину столешницы в сантиметрах</div>
                    <div class="co-dims">
                        <div class="co-dim-field">
                            <label class="co-dim-label" for="coDimL">Длина (см)</label>
                            <input type="number" id="coDimL" name="dim_length" class="co-dim-input" placeholder="180" min="30" max="500">
                        </div>
                        <div class="co-dim-x">×</div>
                        <div class="co-dim-field">
                            <label class="co-dim-label" for="coDimW">Ширина (см)</label>
                            <input type="number" id="coDimW" name="dim_width" class="co-dim-input" placeholder="90" min="30" max="500">
                        </div>
                    </div>
                </div>

                <!-- 3. Персоны -->
                <div class="co-step">
                    <div class="co-step-head">
                        <div class="co-step-num">3</div>
                        <div class="co-step-title">Количество персон</div>
                    </div>
                    <div class="co-persons" data-field="persons">
                        <button type="button" class="co-per co-per--on" data-value="2">2</button>
                        <button type="button" class="co-per" data-value="4">4</button>
                        <button type="button" class="co-per" data-value="6">6</button>
                        <button type="button" class="co-per" data-value="8">8</button>
                        <button type="button" class="co-per" data-value="10">10</button>
                        <button type="button" class="co-per" data-value="12">12</button>
                    </div>
                </div>

                <!-- 4. Цвет -->
                <div class="co-step">
                    <div class="co-step-head">
                        <div class="co-step-num">4</div>
                        <div class="co-step-title">Цвет</div>
                    </div>
                    <div class="co-step-hint">Выберите из палитры или опишите желаемый оттенок в комментарии</div>
                    <div class="co-swatches" data-field="color">
                        <?php
                        if (!empty($colors) && !is_wp_error($colors)) {
                            $first = true;
                            foreach ($colors as $color) {
                                $hex = get_term_meta($color->term_id, 'color_hex', true) ?: '#888';
                                $swatch_url = function_exists('loraleya_color_swatch_url')
                                    ? loraleya_color_swatch_url($color->slug)
                                    : '';
                                $bg_style = $swatch_url
                                    ? 'background-image:url(' . esc_url($swatch_url) . ');background-color:' . esc_attr($hex) . ';'
                                    : 'background:' . esc_attr($hex) . ';';
                                $cls = 'co-sw' . ($first ? ' co-sw--on' : '');
                                printf(
                                    '<div class="%s" data-value="%s" data-name="%s" style="%s" title="%s"><span class="co-sw-label">%s</span></div>',
                                    esc_attr($cls),
                                    esc_attr($color->slug),
                                    esc_attr($color->name),
                                    $bg_style,
                                    esc_attr($color->name),
                                    esc_html($color->name)
                                );
                                $first = false;
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- 5. Изделия -->
                <div class="co-step">
                    <div class="co-step-head">
                        <div class="co-step-num">5</div>
                        <div class="co-step-title">Что шьём</div>
                    </div>
                    <div class="co-step-hint">Включите нужные изделия и укажите количество</div>
                    <div class="co-items">
                        <div class="co-item-row" data-item="tablecloth" data-name="Скатерть">
                            <div>
                                <div class="co-item-name">Скатерть</div>
                                <div class="co-item-size">По размерам вашего стола</div>
                            </div>
                            <div class="co-item-right">
                                <div class="co-toggle" data-on="0"><div class="co-toggle-dot"></div></div>
                                <div class="co-qty">
                                    <button type="button" class="co-qty-btn" data-delta="-1">−</button>
                                    <span class="co-qty-val">1</span>
                                    <button type="button" class="co-qty-btn" data-delta="1">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="co-item-row" data-item="runner" data-name="Дорожка">
                            <div>
                                <div class="co-item-name">Дорожка</div>
                                <div class="co-item-size">По длине стола</div>
                            </div>
                            <div class="co-item-right">
                                <div class="co-toggle" data-on="0"><div class="co-toggle-dot"></div></div>
                                <div class="co-qty">
                                    <button type="button" class="co-qty-btn" data-delta="-1">−</button>
                                    <span class="co-qty-val">1</span>
                                    <button type="button" class="co-qty-btn" data-delta="1">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="co-item-row" data-item="napkins" data-name="Салфетки">
                            <div>
                                <div class="co-item-name">Салфетки</div>
                                <div class="co-item-size">40 × 40 см</div>
                            </div>
                            <div class="co-item-right">
                                <div class="co-toggle" data-on="0"><div class="co-toggle-dot"></div></div>
                                <div class="co-qty">
                                    <button type="button" class="co-qty-btn" data-delta="-1">−</button>
                                    <span class="co-qty-val">1</span>
                                    <button type="button" class="co-qty-btn" data-delta="1">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="co-item-row" data-item="kuverts" data-name="Куверты">
                            <div>
                                <div class="co-item-name">Куверты</div>
                                <div class="co-item-size">9 × 24 см</div>
                            </div>
                            <div class="co-item-right">
                                <div class="co-toggle" data-on="0"><div class="co-toggle-dot"></div></div>
                                <div class="co-qty">
                                    <button type="button" class="co-qty-btn" data-delta="-1">−</button>
                                    <span class="co-qty-val">1</span>
                                    <button type="button" class="co-qty-btn" data-delta="1">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. Опции -->
                <div class="co-step">
                    <div class="co-step-head">
                        <div class="co-step-num">6</div>
                        <div class="co-step-title">Дополнительные опции</div>
                    </div>
                    <div class="co-options">
                        <div class="co-opt-row">
                            <input type="checkbox" class="co-opt-cb" id="coOptMono" name="opt_monogram" value="1">
                            <label for="coOptMono">
                                <div class="co-opt-label">Монограмма / вышивка</div>
                                <div class="co-opt-hint">Инициалы, логотип или рисунок на изделии</div>
                            </label>
                        </div>
                        <div class="co-opt-row">
                            <input type="checkbox" class="co-opt-cb" id="coOptEdge" name="opt_edge" value="1">
                            <label for="coOptEdge">
                                <div class="co-opt-label">Декоративная обработка края</div>
                                <div class="co-opt-hint">Бахрома, оверлок контрастной нитью</div>
                            </label>
                        </div>
                        <div class="co-opt-row">
                            <input type="checkbox" class="co-opt-cb" id="coOptRings" name="opt_rings" value="1">
                            <label for="coOptRings">
                                <div class="co-opt-label">Кольца для салфеток</div>
                                <div class="co-opt-hint">Золото или серебро, в комплект к салфеткам</div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 7. Контакты -->
                <div class="co-step">
                    <div class="co-step-head">
                        <div class="co-step-num">7</div>
                        <div class="co-step-title">Контактные данные</div>
                    </div>
                    <div class="co-contact">
                        <div class="co-ct-row">
                            <div class="co-ct-field">
                                <label class="co-ct-label" for="coName">Имя</label>
                                <input type="text" id="coName" name="customer_name" class="co-ct-input" placeholder="Как к вам обращаться" required>
                            </div>
                            <div class="co-ct-field">
                                <label class="co-ct-label" for="coContact">Телефон или Telegram</label>
                                <input type="text" id="coContact" name="customer_contact" class="co-ct-input" placeholder="+7 (___) ___-__-__" required>
                            </div>
                        </div>
                        <div class="co-ct-row">
                            <div class="co-ct-field">
                                <label class="co-ct-label" for="coNotes">Комментарий</label>
                                <textarea id="coNotes" name="customer_notes" class="co-ct-input" placeholder="Опишите пожелания, особенности стола, или задайте вопрос"></textarea>
                            </div>
                        </div>

                        <!-- Honeypot — скрытое поле для отлова ботов -->
                        <input type="text" name="website" class="co-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">

                        <!-- WordPress nonce для защиты от CSRF -->
                        <?php wp_nonce_field('loraleya_custom_order', 'co_nonce'); ?>

                        <!-- Согласие на обработку ПД -->
                        <div class="co-consent">
                            <input type="checkbox" class="co-opt-cb" id="coConsent" name="consent" value="1" required>
                            <label for="coConsent">
                                <div class="co-opt-label">Согласен с <a href="<?php echo esc_url(get_privacy_policy_url() ?: home_url('/privacy-policy/')); ?>" target="_blank">политикой обработки персональных данных</a></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- SUMMARY -->
                <div class="co-summary" id="coSummary">
                    <div class="co-sum-title">Ваша заявка</div>
                    <div class="co-sum-row"><span class="co-sl">Форма стола</span><span class="co-sv" id="coSumShape">Прямоугольный</span></div>
                    <div class="co-sum-row"><span class="co-sl">Размер</span><span class="co-sv" id="coSumSize">Укажите выше</span></div>
                    <div class="co-sum-row"><span class="co-sl">Персоны</span><span class="co-sv" id="coSumPers">2</span></div>
                    <div class="co-sum-row"><span class="co-sl">Цвет</span><span class="co-sv" id="coSumColor"><?php echo !empty($colors) && !is_wp_error($colors) ? esc_html($colors[0]->name) : ''; ?></span></div>
                    <div class="co-sum-row"><span class="co-sl">Изделия</span><span class="co-sv" id="coSumItems">Скатерть, салфетки ×4, куверты ×4</span></div>
                    <div class="co-sum-note">Точную стоимость рассчитаем и сообщим в течение 2 часов после получения заявки. Предоплата 50%, срок изготовления 7–14 рабочих дней.</div>
                </div>

                <!-- SUBMIT -->
                <div class="co-submit-area">
                    <div class="co-submit-info">Заявка будет отправлена на почту и в Telegram. Мы свяжемся с вами в течение 2 часов для уточнения деталей и расчёта стоимости.</div>
                    <button type="submit" class="co-btn-submit">
                        Отправить заявку →
                    </button>
                </div>

                <!-- Сообщения после отправки -->
                <div class="co-result co-result--success" id="coResultSuccess" hidden>
                    <strong>Заявка отправлена!</strong> Мы свяжемся с вами в течение 2 часов.
                </div>
                <div class="co-result co-result--error" id="coResultError" hidden>
                    <strong>Ошибка отправки.</strong> Попробуйте ещё раз или напишите нам напрямую.
                </div>

            </form>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="co-faq-sec">
    <div class="co-container">
        <h2 class="co-faq-title">Частые вопросы</h2>

        <div class="co-faq">
            <div class="co-faq-q">Сколько стоит индивидуальный пошив?</div>
            <div class="co-faq-a">Стоимость зависит от размера изделия и дополнительных опций. Ориентировочно: скатерть на стол 180×90 — от 3 500 ₽, комплект салфеток на 6 персон — от 2 100 ₽. Точную цену рассчитаем после получения заявки.</div>
        </div>
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
            <div class="co-faq-a">Предоплата 50% при подтверждении заказа, остаток — перед отправкой. Оплата картой или переводом.</div>
        </div>
        <div class="co-faq">
            <div class="co-faq-q">Что если размер не подойдёт?</div>
            <div class="co-faq-a">Мы шьём точно по вашим размерам. Если возникла ошибка с нашей стороны — перешьём бесплатно. Рекомендуем измерить стол перед заказом и приложить фото.</div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
