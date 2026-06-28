# ТЗ — Личный кабинет (My Account) LoraLeya, MVP

**Контекст.** Кнопка «Личный кабинет» в шапке сейчас ведёт на главную (заглушка). Нужен рабочий кабинет на стандартном WooCommerce `/my-account/`, стилизованный под dark-luxury (как чекаут).

**Концепция (подтверждена заказчиком):**
- Покупка **гостем остаётся** — регистрация НЕ обязательна.
- Регистрация **добровольная**, доступна в двух местах: галочкой на чекауте И формой на странице кабинета.
- Кабинет нужен постоянным клиентам (кафе/рестораны) — история заказов, повтор заказа, адреса.
- **B2B (безнал, счета, опт) — НЕ в этом этапе**, отложено.

**Объём MVP:** Заказы (+ «Заказать снова»), Адреса, Детали аккаунта. Вход/регистрация для незалогиненных.

---

## 1. Страница и кнопка в шапке

- [ ] Убедиться, что страница **«Мой аккаунт»** существует и содержит шорткод `[woocommerce_my_account]`. Если нет — создать (Страницы → Добавить), указать её в WooCommerce → Настройки → Дополнительно → «Страница аккаунта».
- [ ] В шапке (`header.php` кастомной темы) ссылка кнопки **«Личный кабинет»** сейчас ведёт на главную. Заменить href на ссылку страницы аккаунта: использовать `wc_get_page_permalink( 'myaccount' )`, а не хардкод `/`.
  - Опционально: для залогиненного показывать «Личный кабинет», для гостя можно ту же ссылку (WooCommerce сам покажет форму входа). Не усложнять — одна ссылка на `myaccount`.

## 2. Настройки аккаунтов (WooCommerce → Настройки → Аккаунты и приватность)

- [ ] **«Разрешить покупателям создавать аккаунт при оформлении заказа»** → включить (галочка на чекауте).
- [ ] **«Разрешить покупателям создавать аккаунт на странице "Мой аккаунт"»** → включить (форма регистрации в кабинете).
- [ ] «Разрешить вход в систему при оформлении заказа» → включить (для постоянников, чтобы вошли и адрес подтянулся).
- [ ] Гостевой заказ оставить разрешённым (НЕ требовать регистрацию для покупки).

## 3. Согласие на обработку ПДн при регистрации

- [ ] На форме **регистрации** (и галочке регистрации на чекауте) должно быть **согласие на обработку ПДн** со ссылкой на Политику конфиденциальности — по аналогии с тем, как уже сделано на чекауте (там подключается блочное поле `loraleya/privacy-consent`). Использовать тот же механизм/текст согласия. Регистрация без согласия невозможна.
  - WooCommerce имеет хук `woocommerce_register_form` для вставки чекбокса согласия и `woocommerce_register_post` для валидации. Либо переиспользовать существующий компонент согласия темы.

## 4. Стилизация — `assets/css/account.css`

Создать отдельный CSS, подключить **условно** через `is_account_page()` (по образцу `checkout.css`):

```php
add_action( 'wp_enqueue_scripts', function () {
    if ( function_exists( 'is_account_page' ) && is_account_page() ) {
        $path = get_stylesheet_directory() . '/assets/css/account.css';
        $uri  = get_stylesheet_directory_uri() . '/assets/css/account.css';
        if ( file_exists( $path ) ) {
            wp_enqueue_style( 'loraleya-account', $uri, array( 'loraleya-style' ), filemtime( $path ) );
        }
    }
}, 20 );
```

CSS на переменных темы (`--bg #0e0e0c`, `--bg2 #1a1917`, `--bg3 #252420`, `--cream #e8e0d0`, `--gold #c5a55a`, `--gold-light #d4bc7c`, `--text #c8c0b4`, `--text-muted #8a847a`, `--serif`, `--sans`, `--radius 0`, `--max-width 1200px`, `--transition 0.3s ease`):

```css
/* ============================================================
   LoraLeya — Личный кабинет (My Account). Грузится через is_account_page().
   ============================================================ */

.woocommerce-account .woocommerce { max-width: var(--max-width); margin: 0 auto; }

/* ---- Раскладка: меню слева, контент справа ---- */
.woocommerce-account .woocommerce-MyAccount-navigation { float: left; width: 24%; }
.woocommerce-account .woocommerce-MyAccount-content    { float: right; width: 72%; }
@media (max-width: 768px) {
  .woocommerce-account .woocommerce-MyAccount-navigation,
  .woocommerce-account .woocommerce-MyAccount-content { float: none; width: 100%; }
}

/* ---- Навигация кабинета ---- */
.woocommerce-MyAccount-navigation ul { list-style: none; margin: 0; padding: 0; border: 1px solid rgba(197,165,90,.15); }
.woocommerce-MyAccount-navigation ul li { border-bottom: 1px solid rgba(197,165,90,.1); }
.woocommerce-MyAccount-navigation ul li:last-child { border-bottom: 0; }
.woocommerce-MyAccount-navigation ul li a {
  display: block; padding: .85rem 1.1rem;
  font-family: var(--sans); font-size: .82rem; letter-spacing: .08em; text-transform: uppercase;
  color: var(--text-muted); text-decoration: none; transition: all var(--transition);
}
.woocommerce-MyAccount-navigation ul li a:hover { color: var(--cream); background: var(--bg2); }
.woocommerce-MyAccount-navigation ul li.is-active a { color: var(--gold); border-left: 2px solid var(--gold); }

/* ---- Заголовки / текст ---- */
.woocommerce-account .woocommerce-MyAccount-content h2,
.woocommerce-account .woocommerce-MyAccount-content h3 { font-family: var(--serif); font-weight: 300; color: var(--cream); }
.woocommerce-account .woocommerce-MyAccount-content p { color: var(--text); }
.woocommerce-account .woocommerce-MyAccount-content a { color: var(--gold); }

/* ---- Таблица заказов ---- */
.woocommerce-account .woocommerce-orders-table,
.woocommerce-account table.shop_table { width: 100%; border-collapse: collapse; }
.woocommerce-account .shop_table th,
.woocommerce-account .shop_table td {
  padding: .7rem .6rem; border-bottom: 1px solid rgba(197,165,90,.1);
  text-align: left; font-size: .88rem; color: var(--text);
}
.woocommerce-account .shop_table thead th {
  color: var(--text-muted); text-transform: uppercase; letter-spacing: .08em; font-size: .7rem; font-weight: 400;
}

/* ---- Формы (вход / регистрация / адреса / детали) ---- */
.woocommerce-account .input-text,
.woocommerce-account input[type="text"],
.woocommerce-account input[type="email"],
.woocommerce-account input[type="password"],
.woocommerce-account input[type="tel"],
.woocommerce-account select,
.woocommerce-account textarea {
  width: 100%; background: var(--bg2);
  border: 1px solid rgba(197,165,90,.22); border-radius: var(--radius);
  color: var(--cream); font-family: var(--sans); font-weight: 300; font-size: .95rem;
  padding: .65rem .8rem; transition: border-color var(--transition);
}
.woocommerce-account .input-text:focus,
.woocommerce-account input:focus, .woocommerce-account select:focus, .woocommerce-account textarea:focus {
  outline: none; border-color: var(--gold);
}
.woocommerce-account .form-row label,
.woocommerce-account label {
  display: block; font-size: .72rem; letter-spacing: .13em; text-transform: uppercase;
  color: var(--text-muted); margin-bottom: .35rem;
}
.woocommerce-account .form-row { margin-bottom: 1rem; }
.woocommerce-account .required { color: var(--gold); border: 0; }

/* ---- Карточки логин/регистрация ---- */
.woocommerce-account .u-columns .col-1,
.woocommerce-account .u-columns .col-2,
.woocommerce-account .woocommerce-form-login,
.woocommerce-account .woocommerce-form-register {
  background: var(--bg2); border: 1px solid rgba(197,165,90,.15);
  border-radius: var(--radius); padding: 1.5rem;
}

/* ---- Кнопки (по образцу .single_add_to_cart_button) ---- */
.woocommerce-account .button,
.woocommerce-account button[type="submit"],
.woocommerce-account .woocommerce-Button {
  background: var(--gold); color: var(--bg); border: 0; border-radius: var(--radius);
  font-family: var(--sans); font-weight: 600; font-size: .8rem; letter-spacing: .12em; text-transform: uppercase;
  padding: .8rem 1.4rem; cursor: pointer; transition: background var(--transition);
}
.woocommerce-account .button:hover,
.woocommerce-account button[type="submit"]:hover { background: var(--gold-light); }

/* «Заказать снова» / второстепенные действия — золотой outline */
.woocommerce-account .order-again .button,
.woocommerce-account .woocommerce-button.view {
  background: transparent; border: 1px solid var(--gold); color: var(--gold);
}
.woocommerce-account .order-again .button:hover { background: var(--gold); color: var(--bg); }

/* ---- Адреса ---- */
.woocommerce-account .woocommerce-Address { background: var(--bg2); border: 1px solid rgba(197,165,90,.15); padding: 1.2rem; }
.woocommerce-account .woocommerce-Address address { color: var(--text); font-style: normal; line-height: 1.7; }

/* ---- Уведомления ---- */
.woocommerce-account .woocommerce-message,
.woocommerce-account .woocommerce-info { background: var(--bg2); border-top: 2px solid var(--gold); color: var(--text); border-radius: 0; }
.woocommerce-account .woocommerce-error { background: var(--bg2); border-top: 2px solid #a14a3a; color: var(--cream); border-radius: 0; }

/* clearfix для float-раскладки */
.woocommerce-account .woocommerce::after { content: ""; display: table; clear: both; }
```

## 5. «Заказать снова»

- [ ] Кнопка **«Заказать снова» / Order again** работает в WooCommerce из коробки на странице **выполненного** заказа. Проверить, что статус «Выполнен» входит в `woocommerce_valid_order_statuses_for_order_again` (по умолчанию — да). Если кафе должны повторять и другие статусы — расширить фильтром, но для MVP оставить дефолт (только выполненные).

---

## 6. Проверка после внедрения

1. Кнопка «Личный кабинет» в шапке → открывает `/my-account/`, НЕ главную.
2. Гость на `/my-account/` → видит формы **входа** и **регистрации**, обе стилизованы (тёмные, золото).
3. Регистрация без галочки согласия на ПДн → не проходит (валидация).
4. После регистрации/входа → кабинет: меню слева (Панель, Заказы, Адреса, Детали, Выход), контент справа.
5. Раздел **Заказы** → список, внутри заказа трек-номер 5Post и кнопка «Заказать снова» (на выполненном).
6. **Адреса** и **Детали аккаунта** — формы стилизованы, сохраняются.
7. Чекаут: появилась галочка «Создать аккаунт» (с согласием на ПДн), но покупка **гостем по-прежнему работает** без неё.
8. Мобила (≤768px): меню и контент в одну колонку.
9. Регресс: оформление заказа гостем не сломалось.

## 7. Ограничения / на заметку

- Коммиты атомарные: (1) страница + ссылка в шапке + настройки аккаунтов; (2) `account.css` + enqueue; (3) согласие на ПДн на регистрации.
- B2B (безнал/Сбербанк Бизнес Онлайн, счета, опт, документы) — **вне этого ТЗ**, отдельный этап по запросу от кафе/ресторанов.
- Регистрация = хранение ПДн (логин/пароль/история). Согласие на ПДн на форме обязательно (п.3) — бьётся с поданными документами РКН.

## 8. Бэклог проекта (карта открытых хвостов)

- Боевой прогон у Натальи (заказ→доставка→маркировка) — в процессе, ждём отчёт.
- Возврат тестового #867 (190 ₽) — ждёт зачисления на баланс ЮKassa.
- Сверка тарифа 5Post (база 3 / шаг 1) с договором.
- Баг-репорт ipol: блочный чекаут `wc is not defined` (для возврата блочной вёрстки).
- Снять `noindex` при публичном запуске.
- B2B-надстройка кабинета — по появлению спроса от ресторанов.
