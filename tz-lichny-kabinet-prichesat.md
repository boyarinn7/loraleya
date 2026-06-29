# ТЗ — Личный кабинет LoraLeya, причёсывание (премиум-гигиена)

**Контекст.** Кабинет на `/my-account/` работает и стилизован (`account.css`). Сейчас приводим его в порядок под премиум-бренд и убираем дублирование/рудименты WooCommerce. Решения приняты заказчиком — без отсебятины, делаем ровно перечисленное.

**Принцип:** ничего «ради наполнения». Кастомную консоль НЕ строим (она дублировала меню). Вся ценность — в чистке и удобстве повторного заказа.

---

## Объём (что делаем)

1. **Старт кабинета = «Заказы», «Консоль» убрать из меню.**
2. **Чистка меню:** убрать «Загрузки» и «Способы оплаты».
3. **Переименовать** пункты на человеческие русские названия.
4. **Приветствие-шапка** по имени над контентом каждого раздела.
5. **Ссылка «В каталог»** рядом с «Заказать снова» на странице заказа.

Всё — в `functions.php` (хуки/фильтры) + дополнения в `account.css`. Без новых страниц и плагинов.

---

## 1. Старт на «Заказах», «Консоль» вон из меню

**1a. Убрать пункт «Панель/Консоль» (dashboard) из меню кабинета:**

```php
add_filter( 'woocommerce_account_menu_items', function ( $items ) {
    unset( $items['dashboard'] );        // Консоль
    unset( $items['downloads'] );        // Загрузки (п.2)
    unset( $items['payment-methods'] );  // Способы оплаты (п.2)
    return $items;
}, 20 );
```

**1b. При заходе на корень `/my-account/` показывать заказы.** Способ Б — подмена дефолта через редирект на эндпоинт orders, чтобы корень не показывал пустую консоль:

```php
add_action( 'template_redirect', function () {
    if ( ! function_exists( 'is_account_page' ) ) return;
    // только корень кабинета (без эндпоинтов), только для залогиненного
    if ( is_account_page() && is_user_logged_in() && empty( WC()->query->get_current_endpoint() ) ) {
        wp_safe_redirect( wc_get_account_endpoint_url( 'orders' ) );
        exit;
    }
} );
```

> Примечание агенту: `get_current_endpoint()` пуст на «дашборде». Если в этой версии WooCommerce метод иной — использовать проверку `is_wc_endpoint_url()` по известным эндпоинтам, цель: «голый» `/my-account/` → `/my-account/orders/`. Гость (незалогиненный) на `/my-account/` должен по-прежнему видеть форму входа/регистрации — редирект только для залогиненных.

## 2. Чистка меню

Уже сделано в фильтре выше (`downloads`, `payment-methods` удалены). Проверить, что в меню осталось: **Заказы, Адреса, Профиль, Выход**.

## 3. Переименование пунктов (по-русски, понятно)

```php
add_filter( 'woocommerce_account_menu_items', function ( $items ) {
    if ( isset( $items['orders'] ) )       $items['orders']       = 'Мои заказы';
    if ( isset( $items['edit-address'] ) ) $items['edit-address'] = 'Адреса';
    if ( isset( $items['edit-account'] ) ) $items['edit-account'] = 'Профиль';   // было «Анкета»
    if ( isset( $items['customer-logout'] ) ) $items['customer-logout'] = 'Выйти';
    return $items;
}, 30 );
```

## 4. Приветствие-шапка над контентом

Тёплое обращение по имени над содержимым кабинета (заменяет казённое «Добро пожаловать, kurenkova (не kurenkova? Выйти)»). Вывести через хук перед контентом аккаунта, по имени из профиля, с запасным вариантом:

```php
add_action( 'woocommerce_account_content', function () {
    if ( ! is_user_logged_in() ) return;
    $user = wp_get_current_user();
    $name = $user->first_name ? $user->first_name : $user->display_name;
    echo '<div class="ll-account-greeting">';
    echo '<p class="ll-account-greeting__eyebrow">Личный кабинет</p>';
    echo '<h2 class="ll-account-greeting__title">Здравствуйте, ' . esc_html( $name ) . '</h2>';
    echo '</div>';
}, 5 );
```

> `woocommerce_account_content` срабатывает на всех вкладках кабинета — приветствие будет над заказами, адресами, профилем. Приоритет 5 — выше контента.

Также убрать стандартный текст дашборда (он больше не нужен, т.к. дашборд не показывается; но если где-то всплывёт — не критично).

## 5. Ссылка «В каталог» рядом с «Заказать снова»

Сценарий: клиент повторяет заказ («Заказать снова» кладёт прошлый заказ в корзину), затем хочет **дозабрать** недостающее. Чтобы не блуждал — рядом с кнопкой повтора дать ссылку в каталог.

Кнопка «Заказать снова» выводится на странице просмотра заказа (`woocommerce_order_again_button`) и в действиях. Добавить рядом ссылку «В каталог» (на страницу магазина/палитры). Вариант — через хук после деталей заказа:

```php
add_action( 'woocommerce_order_details_after_order_table', function ( $order ) {
    if ( ! $order ) return;
    $shop = get_permalink( wc_get_page_id( 'shop' ) ); // или конкретная страница «Палитра»/«Сценарии»
    if ( $shop ) {
        echo '<p class="ll-order-add-more"><a href="' . esc_url( $shop ) . '" class="button ll-btn-outline">В каталог — добавить ещё</a></p>';
    }
}, 20 );
```

> Агенту: уточнить, куда логичнее вести — на «Магазин», «Палитру» или «Сценарии». По умолчанию — страница магазина. Боковая навигация подскажет.

---

## 6. Дополнения в `assets/css/account.css` (премиум-токены)

Токены темы: `--bg #0e0e0c`, `--bg2 #1a1917`, `--cream #e8e0d0`, `--gold #c5a55a`, `--gold-light #d4bc7c`, `--text #c8c0b4`, `--text-muted #8a847a`, `--serif`, `--sans`.

```css
/* Приветствие-шапка */
.ll-account-greeting { margin-bottom: 1.8rem; padding-bottom: 1.4rem; border-bottom: 1px solid rgba(197,165,90,.12); }
.ll-account-greeting__eyebrow {
  font-family: var(--sans); font-size: .68rem; letter-spacing: .3em; text-transform: uppercase;
  color: var(--text-muted); margin: 0 0 .5rem;
}
.ll-account-greeting__title {
  font-family: var(--serif); font-weight: 300; font-size: 2rem; color: var(--cream); margin: 0;
}

/* Ссылка «В каталог» у повтора заказа — золотой outline (как .btn--outline темы) */
.ll-order-add-more { margin-top: 1rem; }
.woocommerce-account .ll-btn-outline,
.woocommerce-view-order .ll-btn-outline {
  display: inline-block; background: transparent; border: 1px solid var(--gold); color: var(--gold);
  font-family: var(--sans); font-weight: 600; font-size: .7rem; letter-spacing: .14em; text-transform: uppercase;
  padding: .75rem 1.6rem; border-radius: 0; transition: all var(--transition);
}
.woocommerce-account .ll-btn-outline:hover,
.woocommerce-view-order .ll-btn-outline:hover { background: var(--gold); color: var(--bg); }
```

---

## 7. Проверка после внедрения

1. Залогиненный заходит на `/my-account/` → **сразу открываются «Мои заказы»** (не пустая консоль).
2. В меню кабинета: **Мои заказы · Адреса · Профиль · Выйти**. Нет «Консоль», «Загрузки», «Способы оплаты».
3. Над контентом каждого раздела — **«Здравствуйте, [Имя]»** (тёплая шапка), казённого «не kurenkova? Выйти» в теле нет.
4. На странице заказа есть **«Заказать снова»** и рядом **«В каталог — добавить ещё»**.
5. «Заказать снова» кладёт прошлый заказ в корзину; из каталога можно дозабрать; оформляется одним заказом.
6. Гость на `/my-account/` → по-прежнему форма входа/регистрации (редирект его не трогает).
7. Мобила: приветствие и меню корректны.
8. Регресс: оформление заказа гостем не сломано.

## 8. Ограничения

- Коммиты атомарные: (1) меню — чистка + переименование + старт на заказах; (2) приветствие-шапка + CSS; (3) ссылка «В каталог».
- Имя берём из профиля (`first_name`), запасной — `display_name`. Если у клиента имя не заполнено — показать `display_name`, не ломаться.
- B2B-надстройка (безнал, счета, опт) — вне этого ТЗ.

## 9. Бэклог проекта

- Боевой прогон у Натальи (заказ→доставка→маркировка) — ждём отчёт.
- Возврат #867 — ждёт зачисления на баланс ЮKassa (1–3 дня).
- Сверка тарифа 5Post с договором.
- Баг-репорт ipol: блочный чекаут `wc is not defined`.
- Снять `noindex` при запуске; проверить, что кабинет/корзина/чекаут остаются закрыты от индексации, а товары/блог/главная — открыты.
