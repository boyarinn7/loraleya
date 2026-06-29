# ТЗ — Багфикс визуала личного кабинета LoraLeya (после дизайн-ревью)

**Контекст.** Кабинет причёсан (старт на заказах, чистка меню, приветствие, «В каталог»). Логика работает, но визуальное ревью выявило баги и грязь. Чиним по приоритетам. Всё — в `account.css` + точечно `functions.php`. Токены: `--bg #0e0e0c`, `--bg2 #1a1917`, `--cream #e8e0d0`, `--gold #c5a55a`, `--gold-light #d4bc7c`, `--text #c8c0b4`, `--text-muted #8a847a`, `--serif`, `--sans`.

Эталон качества — раздел **«Адреса»** (он сверстан правильно). Подтянуть остальные экраны к нему.

---

## 🔴 ПРИОРИТЕТ 1 — Баги

### 1.1. Золотая плашка поверх «Заказов ещё не создано»
На пустом разделе «Заказы» кнопка пустого состояния (WooCommerce `.woocommerce-Button` / «Browse products») потеряла подпись и налезла золотым прямоугольником на текст «Заказов ещё не создано».

Исправить:
- Кнопка не должна перекрывать текст — дать ей `display:inline-block`, отступ сверху, и убедиться, что у неё есть **текст** («Перейти в каталог»). Если текст пустой — задать через CSS-контент нельзя надёжно; проверить разметку, при необходимости перерисовать пустое состояние своим хуком.
- Стандартное сообщение `.woocommerce-info` (где «Заказов ещё не создано») и кнопка — разнести вертикально.

```css
.woocommerce-account .woocommerce-MyAccount-content .woocommerce-info {
  display: block; margin-bottom: 1.2rem; position: static;
}
.woocommerce-account .woocommerce-MyAccount-content .woocommerce-info .button,
.woocommerce-account .woocommerce-MyAccount-content .woocommerce-Button {
  position: static; float: none; display: inline-block; margin-top: .4rem;
}
```
Если кнопка остаётся без подписи — заменить пустое состояние «Заказы» своим хуком:
```php
add_action( 'woocommerce_account_orders_endpoint', function () {
    // только если заказов нет — даём чистое пустое состояние
}, 5 );
```
(Агенту: проще всего отстилить дефолт; кастом пустого состояния — только если текст реально теряется.)

### 1.2. Приветствие раздуто до второго H1 — ужать
Сейчас «Здравствуйте, kurenkova» выводится крупным Cormorant и конкурирует с заголовком раздела («Заказы»/«Адреса»). Должна быть **скромная строка**, а не второй заголовок.

```css
.ll-account-greeting { margin: 0 0 1.4rem; padding: 0 0 1rem; border-bottom: 1px solid rgba(197,165,90,.12); }
.ll-account-greeting__eyebrow { font-size: .62rem; letter-spacing: .28em; text-transform: uppercase; color: var(--text-muted); margin: 0 0 .3rem; }
.ll-account-greeting__title {
  font-family: var(--sans);          /* НЕ serif — чтобы не спорил с заголовком раздела */
  font-weight: 400; font-size: 1rem; color: var(--text); margin: 0; letter-spacing: .02em;
}
```
> Цель: приветствие = тихая строка-приставка. Крупный serif остаётся только у заголовка раздела (H1 «Заказы»).

### 1.3. Заголовок «Анкета» → «Профиль» (не заменился)
Пункт меню переименован, но заголовок страницы edit-account остался «Анкета». Привести к «Профиль» (единообразно с меню).

```php
add_filter( 'the_title', function ( $title, $id ) {
    if ( ! is_admin() && function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'edit-account' ) ) {
        // заголовок страницы кабинета на вкладке профиля
    }
    return $title;
}, 10, 2 );
```
> Надёжнее — через фильтр заголовка эндпоинта/`woocommerce_endpoint_edit-account_title` если доступен, либо JS-независимо переопределить выводимый заголовок раздела. Агенту выбрать рабочий способ для этой версии WooCommerce. Результат: на вкладке профиля заголовок — «Профиль».

---

## 🟠 ПРИОРИТЕТ 2 — Грязь в формах

### 2.1. Поля смены пароля наезжают друг на друга
Блок `fieldset` «Смена пароля»: лейблы наезжают на поля, звёздочки `*` отдельными строками, рамка обрезает текст. Дать внутренние отступы и нормальный поток.

```css
.woocommerce-account fieldset {
  border: 1px solid rgba(197,165,90,.15); padding: 1.2rem 1.4rem; margin-top: 1.2rem;
}
.woocommerce-account fieldset legend {
  font-family: var(--sans); font-size: .72rem; letter-spacing: .14em; text-transform: uppercase;
  color: var(--gold); padding: 0 .5rem;
}
.woocommerce-account fieldset .woocommerce-form-row,
.woocommerce-account fieldset .form-row { margin-bottom: 1rem; display: block; }
.woocommerce-account fieldset label { display: block; margin-bottom: .35rem; }
.woocommerce-account .required { color: var(--gold); border: 0; text-decoration: none; }
```

### 2.2. Единый стиль полей ввода (тёмные + золотая рамка) — везде
На «Профиле» поля почти невидимы (сливаются с фоном). Усилить рамку, чтобы поле читалось.

```css
.woocommerce-account .input-text,
.woocommerce-account input[type="text"],
.woocommerce-account input[type="email"],
.woocommerce-account input[type="password"],
.woocommerce-account input[type="tel"] {
  background: var(--bg2);
  border: 1px solid rgba(197,165,90,.3);   /* заметная рамка */
  color: var(--cream); padding: .7rem .85rem; width: 100%;
  border-radius: 0; transition: border-color var(--transition);
}
.woocommerce-account .input-text:focus,
.woocommerce-account input:focus { outline: none; border-color: var(--gold); }
```

---

## 🟠 ПРИОРИТЕТ 3 — Форма входа (жёлтые поля)

На `/my-account/` (гость) поля логина кремово-жёлтые — инородно для тёмной темы. Причины: (а) автозаполнение браузера красит фон; (б) форма входа не покрыта стилями account.css.

**Починить оба:**

```css
/* Форма входа/регистрации — те же тёмные поля */
.woocommerce-account .woocommerce-form-login .input-text,
.woocommerce-account .woocommerce-form-register .input-text {
  background: var(--bg2); border: 1px solid rgba(197,165,90,.3); color: var(--cream);
}
/* Гасим жёлтый фон автозаполнения Chrome/WebKit */
.woocommerce-account input:-webkit-autofill,
.woocommerce-account input:-webkit-autofill:hover,
.woocommerce-account input:-webkit-autofill:focus {
  -webkit-text-fill-color: var(--cream);
  -webkit-box-shadow: 0 0 0 1000px var(--bg2) inset;
  box-shadow: 0 0 0 1000px var(--bg2) inset;
  border: 1px solid rgba(197,165,90,.3);
  transition: background-color 9999s ease-in-out 0s; /* не даём вернуть жёлтый */
  caret-color: var(--cream);
}
.woocommerce-account .woocommerce-form-login,
.woocommerce-account .woocommerce-form-register {
  background: var(--bg2); border: 1px solid rgba(197,165,90,.15); padding: 1.5rem; max-width: 520px;
}
```

---

## Проверка после правок

1. **Заказы (пусто):** текст «Заказов ещё не создано» читается, золотая кнопка под ним с подписью «Перейти в каталог», ничего не налезает.
2. **Приветствие:** тихая строка над контентом, НЕ конкурирует с заголовком раздела. Крупный serif — только у заголовка раздела.
3. **Профиль:** заголовок страницы «Профиль» (не «Анкета»). Поля Имя/Фамилия/Email видны (рамка). Блок «Смена пароля» — поля не наезжают, лейблы над полями, звёздочки на месте.
4. **Вход (гость):** поля тёмные с золотой рамкой, **жёлтой заливки нет** даже при автозаполнении. Карточка входа аккуратная.
5. **Адреса:** остались как были (эталон) — не сломаны.
6. Мобила: формы и приветствие корректны, ничего не наезжает.

## Ограничения
- Коммиты: (1) баги П1 (пустые заказы + приветствие + заголовок профиля); (2) формы П2; (3) форма входа П3.
- Не трогать раздел «Адреса» — он сверстан правильно.
- Гостевой checkout и логика старта на заказах — не регрессировать.

## Бэклог проекта
- Боевой прогон у Натальи — ждём отчёт.
- Возврат #867 — ждёт баланс ЮKassa.
- Сверка тарифа 5Post с договором.
- Баг-репорт ipol: блочный чекаут `wc is not defined`.
- Снять `noindex` при запуске (кабинет/корзина/чекаут — оставить закрытыми).
