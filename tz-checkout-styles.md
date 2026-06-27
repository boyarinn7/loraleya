# ТЗ — Стилизация классического чекаута LoraLeya (dark-luxury)

**Контекст.** Чекаут переведён с блочного режима на классический шорткод `[woocommerce_checkout]` (на блочном падал `wc is not defined` в `fivepost/blocks-checkout.js`, ломалась карта 5Post). Шорткод отдаёт неоформленную форму. Тема никогда не стилизовала чекаут — готовых стилей нет, пишем с нуля под переменные темы. Эталон вида — карточка товара (золотая кнопка «В КОРЗИНУ», тёмные поля, серифные заголовки) и страница «О бренде».

**Раскладка:** две колонки — реквизиты слева, панель «Ваш заказ» справа, панель `sticky`. Акцент — на правой панели (итог золотом, золотая кнопка оплаты). Левая колонка приглушённая. Кнопка «Выбрать на карте» — золотой outline (слабее кнопки оплаты), кнопка «Подтвердить заказ» — золотая плашка (финал).

---

## 1. Файл и подключение

Создать `assets/css/checkout.css` (содержимое — в разделе 3).

В `functions.php` добавить условное подключение (грузить только на чекауте, зависит от `loraleya-style`, версия по `filemtime`):

```php
add_action( 'wp_enqueue_scripts', function () {
    if ( function_exists( 'is_checkout' ) && is_checkout() ) {
        $path = get_stylesheet_directory() . '/assets/css/checkout.css';
        $uri  = get_stylesheet_directory_uri() . '/assets/css/checkout.css';
        if ( file_exists( $path ) ) {
            wp_enqueue_style(
                'loraleya-checkout',
                $uri,
                array( 'loraleya-style' ),
                filemtime( $path )
            );
        }
    }
}, 20 );
```

---

## 2. Брендовый текст ошибки доставки

Сейчас при нераспознанном городе WooCommerce пишет казённое «Нет доступных способов доставки…». Заменить на брендовый текст. В `functions.php`:

```php
add_filter( 'woocommerce_no_shipping_available_html', 'loraleya_no_shipping_text' );
add_filter( 'woocommerce_cart_no_shipping_available_html', 'loraleya_no_shipping_text' );
function loraleya_no_shipping_text( $html ) {
    return '<p>В этом населённом пункте пока нет пунктов выдачи 5Post. Попробуйте ближайший крупный город или напишите нам на loraleya-tex@yandex.ru — подберём доставку.</p>';
}
```

---

## 3. CSS — `assets/css/checkout.css`

Опирается на переменные темы из `style.css` (`:root`): `--bg #0e0e0c`, `--bg2 #1a1917`, `--bg3 #252420`, `--cream #e8e0d0`, `--gold #c5a55a`, `--gold-light #d4bc7c`, `--text #c8c0b4`, `--text-muted #8a847a`, `--serif` (Cormorant), `--sans` (Raleway), `--radius 0`, `--max-width 1200px`, `--transition`.

```css
/* ============================================================
   LoraLeya — Checkout (классический [woocommerce_checkout])
   Грузится только на чекауте через is_checkout().
   ============================================================ */

/* ---- Раскладка: две колонки, sticky-панель заказа ---- */
.woocommerce-checkout form.checkout.woocommerce-checkout {
  display: grid;
  grid-template-columns: 1.3fr 1fr;
  column-gap: 3rem;
  align-items: start;
  max-width: var(--max-width);
  margin: 0 auto;
}
.woocommerce-checkout form.checkout #customer_details { grid-column: 1; grid-row: 1 / span 2; }
.woocommerce-checkout form.checkout #order_review_heading { grid-column: 2; grid-row: 1; margin-top: 0; }
.woocommerce-checkout form.checkout #order_review { grid-column: 2; grid-row: 2; position: sticky; top: 6rem; }
.woocommerce-checkout .woocommerce-form-coupon-toggle,
.woocommerce-checkout form.checkout_coupon { grid-column: 1 / -1; }

/* дефолтная внутренняя 2-колонка #customer_details — складываем в одну */
.woocommerce-checkout #customer_details .col2-set,
.woocommerce-checkout #customer_details .col-1,
.woocommerce-checkout #customer_details .col-2 { width: 100%; float: none; }

/* ---- Заголовки секций ---- */
.woocommerce-checkout #customer_details h3,
.woocommerce-checkout #order_review_heading {
  font-family: var(--serif);
  font-weight: 300;
  color: var(--cream);
  font-size: 1.5rem;
  margin: 0 0 1.2rem;
  padding-bottom: 0.6rem;
  border-bottom: 1px solid rgba(197, 165, 90, 0.15);
}

/* ---- Лейблы ---- */
.woocommerce-checkout .form-row label {
  display: block;
  font-size: 0.72rem;
  letter-spacing: 0.13em;
  text-transform: uppercase;
  color: var(--text-muted);
  margin-bottom: 0.35rem;
}
.woocommerce-checkout .required { color: var(--gold); border: 0; text-decoration: none; }
.woocommerce-checkout .optional { color: var(--text-muted); }

/* ---- Поля ---- */
.woocommerce-checkout .input-text,
.woocommerce-checkout select,
.woocommerce-checkout textarea,
.woocommerce-checkout .select2-container .select2-selection {
  width: 100%;
  background: var(--bg2);
  border: 1px solid rgba(197, 165, 90, 0.22);
  border-radius: var(--radius);
  color: var(--cream);
  font-family: var(--sans);
  font-weight: 300;
  font-size: 0.95rem;
  padding: 0.65rem 0.8rem;
  transition: border-color var(--transition);
}
.woocommerce-checkout .input-text:focus,
.woocommerce-checkout select:focus,
.woocommerce-checkout textarea:focus,
.woocommerce-checkout .select2-container--focus .select2-selection {
  outline: none;
  border-color: var(--gold);
}
.woocommerce-checkout .input-text::placeholder { color: var(--text-muted); }
.woocommerce-checkout .form-row { margin-bottom: 1rem; }

/* select2 (страна) — тёмная тема */
.woocommerce-checkout .select2-container .select2-selection__rendered { color: var(--cream); line-height: 1.7; }
.select2-dropdown { background: var(--bg2); border: 1px solid rgba(197, 165, 90, 0.22); color: var(--cream); }
.select2-results__option--highlighted { background: var(--bg3) !important; color: var(--gold) !important; }

/* «Доставка по другому адресу» + примечание */
.woocommerce-checkout #ship-to-different-address label {
  text-transform: none; letter-spacing: 0; font-size: 1.05rem;
  color: var(--cream); font-family: var(--serif);
}

/* ---- Панель «Ваш заказ» ---- */
.woocommerce-checkout #order_review {
  background: var(--bg2);
  border: 1px solid rgba(197, 165, 90, 0.22);
  border-radius: var(--radius);
  padding: 1.4rem;
}
.woocommerce-checkout #order_review .shop_table { width: 100%; border-collapse: collapse; }
.woocommerce-checkout #order_review th,
.woocommerce-checkout #order_review td {
  padding: 0.6rem 0;
  border-bottom: 1px solid rgba(197, 165, 90, 0.1);
  font-size: 0.9rem;
  text-align: left;
  color: var(--text);
}
.woocommerce-checkout #order_review thead th {
  color: var(--text-muted); font-weight: 400;
  text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.7rem;
}
.woocommerce-checkout #order_review td.product-total,
.woocommerce-checkout #order_review th.product-total { text-align: right; white-space: nowrap; }
.woocommerce-checkout #order_review .order-total th,
.woocommerce-checkout #order_review .order-total td { border-bottom: 0; padding-top: 0.9rem; }
.woocommerce-checkout #order_review .order-total .amount {
  font-family: var(--serif); color: var(--gold); font-size: 1.4rem;
}

/* ---- Кнопка «Выбрать на карте» (5Post) ----
   ВНИМАНИЕ: подтвердить точный класс кнопки в live DOM (плагин fivepost).
   Если у неё свой класс — добавить его явно. Селектор ниже ловит любую
   .button внутри #order_review, КРОМЕ #place_order. */
.woocommerce-checkout #order_review .button:not(#place_order):not(.single_add_to_cart_button) {
  display: inline-block;
  background: transparent;
  border: 1px solid var(--gold);
  border-radius: var(--radius);
  color: var(--gold);
  font-family: var(--sans);
  font-weight: 400;
  font-size: 0.7rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  padding: 0.45rem 0.85rem;
  cursor: pointer;
  transition: all var(--transition);
}
.woocommerce-checkout #order_review .button:not(#place_order):hover { background: var(--gold); color: var(--bg); }

/* ---- Оплата ---- */
.woocommerce-checkout #payment { background: transparent; padding: 0; margin-top: 1rem; }
.woocommerce-checkout #payment ul.payment_methods {
  list-style: none; margin: 0; padding: 0;
  border: 1px solid rgba(197, 165, 90, 0.15);
}
.woocommerce-checkout #payment ul.payment_methods li { padding: 0.8rem; }
.woocommerce-checkout #payment label { color: var(--cream); text-transform: none; letter-spacing: 0; font-size: 0.95rem; }
.woocommerce-checkout #payment .payment_box {
  background: var(--bg3); color: var(--text-muted); font-size: 0.85rem; border-radius: var(--radius);
}
.woocommerce-checkout #payment .payment_box::before { display: none; }

/* ---- Кнопка «Подтвердить заказ» (по образцу .single_add_to_cart_button) ---- */
.woocommerce-checkout #place_order {
  width: 100%;
  background: var(--gold);
  color: var(--bg);
  border: 0;
  border-radius: var(--radius);
  font-family: var(--sans);
  font-weight: 600;
  font-size: 0.8rem;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  padding: 1rem;
  margin-top: 0.5rem;
  cursor: pointer;
  transition: background var(--transition);
}
.woocommerce-checkout #place_order:hover { background: var(--gold-light); }

/* ---- Купон ---- */
.woocommerce-checkout .woocommerce-form-coupon-toggle .woocommerce-info {
  background: transparent; border: 0; color: var(--text-muted); padding: 0 0 0.5rem; font-size: 0.85rem;
}
.woocommerce-checkout form.checkout_coupon {
  background: var(--bg2); border: 1px solid rgba(197, 165, 90, 0.15);
  border-radius: var(--radius); padding: 1.2rem; margin-bottom: 1.5rem;
}
.woocommerce-checkout form.checkout_coupon .button {
  background: transparent; border: 1px solid var(--gold); color: var(--gold);
  border-radius: var(--radius); text-transform: uppercase; letter-spacing: 0.1em;
  font-size: 0.72rem; padding: 0.6rem 1.1rem; cursor: pointer; transition: all var(--transition);
}
.woocommerce-checkout form.checkout_coupon .button:hover { background: var(--gold); color: var(--bg); }

/* ---- Уведомления ---- */
.woocommerce-checkout .woocommerce-info,
.woocommerce-checkout .woocommerce-message {
  background: var(--bg2); border-top: 2px solid var(--gold); color: var(--text); border-radius: 0;
}
.woocommerce-checkout .woocommerce-error {
  background: var(--bg2); border-top: 2px solid #a14a3a; color: var(--cream); border-radius: 0;
}

/* ---- Адаптив ---- */
@media (max-width: 900px) {
  .woocommerce-checkout form.checkout.woocommerce-checkout { grid-template-columns: 1fr; }
  .woocommerce-checkout form.checkout #customer_details,
  .woocommerce-checkout form.checkout #order_review_heading,
  .woocommerce-checkout form.checkout #order_review { grid-column: 1; grid-row: auto; }
  .woocommerce-checkout form.checkout #order_review { position: static; margin-top: 1.5rem; }
}
```

---

## 4. Проверка после внедрения (инкогнито, `/checkout/`)

1. Раскладка: две колонки, реквизиты слева, «Ваш заказ» справа; при скролле панель заказа залипает.
2. Поля: тёмный фон `--bg2`, тонкая золотая обводка, фокус — золотая рамка; плейсхолдеры приглушены.
3. Лейблы: мелкий капс вразрядку, звёздочка обязательных — золотом.
4. Селект «Страна» (select2): тёмный, выпадашка тёмная, ховер пунктов золотой.
5. Кнопка «Подтвердить заказ» — золотая плашка во всю ширину, ховер `--gold-light`.
6. Кнопка «Выбрать на карте» — золотой outline. **Убедиться, что селектор не зацепил `#place_order` и не перекрасил его.** Если кнопка карты не подхватилась — найти её класс в DOM и дописать.
7. Мобила (≤900px): одна колонка, панель заказа снизу, не sticky.
8. Ошибка доставки для нераспознанного города — брендовый текст (раздел 2), не дефолтный WooCommerce.
9. Карта 5Post открывается и точка выбирается (регресс-проверка, стили не должны были это задеть).

---

## 5. Ограничения / на что обратить внимание

- **Карта 5Post открывается в отдельном окне браузера** (поведение плагина fivepost), не инлайн-виджетом. Саму карту стилизовать нельзя — она на стороне 5Post/Яндекса. Стилизуем только кнопку «Выбрать на карте» и блок выбранного ПВЗ в форме.
- Точный класс кнопки «Выбрать на карте» — **подтвердить по DOM**. Селектор в CSS универсальный (`.button` внутри `#order_review`), но если плагин даёт кнопке спец-класс — добавить его явно для надёжности.
- В теме базовый reset мог убрать float у `.col2-set` — в CSS это учтено (принудительный `float:none; width:100%`).
- Коммиты атомарные: (1) `checkout.css` + enqueue, (2) брендовый текст ошибки доставки — отдельно.

---

## 6. Бэклог (Отложено — нести дальше между сессиями/агентами)

1. **Точки 5Post загружены частично — 7 из 26 страниц.** Падает по таймауту Reg.ru на длинной операции. Поднять `max_execution_time` (≥300) и `memory_limit` (≥256M) в панели Reg.ru, прогнать «Обновление точек выдачи» до 26/26. До этого доставка работает только по части регионов РФ (Раменское/Москва — есть).
2. **Веса товаров не залиты** — стоит дефолтная заглушка 1 кг. Нужен импорт по таблице габаритов (PHP-утилита, паттерн `?k=ll2026`, превью → `&apply=YES`, бэкап). Без этого тариф 5Post считается от 1 кг для всех SKU.
3. **Сверить тариф 5Post** — базовый тариф `3` / шаг перевеса `1`: подтвердить, что это договорные значения, а не временные.
4. **Маркировка куверта** — в заказе #867 стояло «Не требуется», хотя столовый текстиль попадает под Честный знак. Настроить маркировку в ЮKassa + флаги маркируемости у товаров.
5. **Баг-репорт в ipol** (support@ipol.ru): на блочном чекауте `fivepost/assets/js/blocks-checkout.js` падает с `Uncaught ReferenceError: wc is not defined`, карта ПВЗ не инициализируется. Когда починят — можно вернуть блочный чекаут.
6. **Письмо в 5Post про поля соответствия платёжных систем** — ответ закроет вопрос окончательно. По факту предоплата уже работает (в заказе «Способ оплаты = Предоплата», доставка входит в онлайн-оплату).
7. **Возврат за тестовый заказ #867** (190 ₽, боевой платёж ЮKassa) — оформить, когда снимется «отложено».
