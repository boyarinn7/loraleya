# ТЗ — Финальная полировка кабинета LoraLeya (регулируемая высота + 2 фикса)

**Контекст.** После багфикса осталось: (1) дать заказчику ручку регулировки высоты правой колонки — он сам доведёт до мм; (2) починить кнопку пустого состояния «Заказы»; (3) сделать поля «Профиля» компактнее (сейчас слишком крупные, не премиум). Вход — эталон, НЕ трогать. Адреса — НЕ трогать, кроме общего сдвига.

Токены: `--bg #0e0e0c`, `--bg2 #1a1917`, `--cream #e8e0d0`, `--gold #c5a55a`, `--gold-light #d4bc7c`, `--text #c8c0b4`, `--text-muted #8a847a`, `--serif`, `--sans`.

---

## 1. РУЧКА РЕГУЛИРОВКИ ВЫСОТЫ — ТОЛЬКО РАЗДЕЛ «ЗАКАЗЫ»

ВАЖНО: на этом этапе двигаем **только раздел «Заказы»**. Адреса и Профиль НЕ трогаем (их высоту оставляем как есть). Нужна отдельная переменная-ручка, действующая исключительно на странице заказов, чтобы заказчик подогнал сам.

Раздел заказов определяется телом-классом WooCommerce `.woocommerce-orders` (страница `/my-account/orders/`). Применяем сдвиг только при этом классе.

В `account.css`, в самом верху файла:

```css
/* ============================================================
   РЕГУЛИРОВКА ВЫСОТЫ ПРАВОЙ КОЛОНКИ — ТОЛЬКО РАЗДЕЛ «ЗАКАЗЫ»
   Меняй только это число:
     меньше (например -3rem, -4rem) = контент ВЫШЕ
     больше (например -1rem, 0)     = контент НИЖЕ
   1rem ≈ 16px ≈ 0.4 см. Шаг 0.25rem ≈ 4px для точной подгонки.
   Адреса и Профиль этим НЕ двигаются.
   ============================================================ */
:root {
  --ll-orders-content-shift: -2rem;   /* ← КРУТИ ЭТО (только заказы) */
}

/* Сдвиг применяется ТОЛЬКО на странице заказов */
.woocommerce-account.woocommerce-orders .woocommerce-MyAccount-content {
  margin-top: var(--ll-orders-content-shift);
}
```

Требования:
- Сдвиг действует **только** на разделе «Заказы» (тело-класс `.woocommerce-orders`). На адресах/профиле margin-top НЕ меняется.
- Если у этой версии WooCommerce body-класс иной — агент определяет фактический класс страницы заказов (через `body_class` или `is_wc_endpoint_url('orders')`) и привязывает сдвиг к нему. Цель: двигается только контент заказов.
- Комментарий на русском, как выше.
- На мобиле (≤768px) обнулить, чтобы не наезжало:
```css
@media (max-width: 768px) {
  .woocommerce-account.woocommerce-orders .woocommerce-MyAccount-content { margin-top: 0; }
}
```

> Стартовое -2rem — ориентир, заказчик подгонит. Адреса/Профиль — отдельным этапом позже, своими переменными.

## 2. ФИКС КНОПКИ «ЗАКАЗЫ» (пустое состояние)

Сейчас на пустом разделе «Заказы» золотая кнопка «Перейти в каталог» (WooCommerce `.woocommerce-info .button`) **наезжает** на текст «Заказов ещё не создано» И **надпись не читается** (золотой текст на золотом фоне).

Починить два момента:

**2a. Разнести текст и кнопку вертикально** (кнопка — на отдельную строку под текстом):
```css
.woocommerce-account .woocommerce-MyAccount-content .woocommerce-info {
  display: flex; flex-direction: column; align-items: flex-start; gap: 1rem;
  background: var(--bg2); border-top: 2px solid var(--gold); border-radius: 0;
  padding: 1.2rem 1.4rem; color: var(--text); position: static;
}
.woocommerce-account .woocommerce-MyAccount-content .woocommerce-info .button {
  position: static; float: none; margin: 0;
}
```

**2b. Сделать подпись кнопки читаемой** — тёмный текст на золоте (как кнопка «ВОЙТИ»):
```css
.woocommerce-account .woocommerce-info .button,
.woocommerce-account .woocommerce-MyAccount-content .button {
  background: var(--gold); color: var(--bg) !important;   /* тёмный текст на золоте */
  font-family: var(--sans); font-weight: 600; font-size: .78rem;
  letter-spacing: .12em; text-transform: uppercase;
  padding: .75rem 1.6rem; border: 0; border-radius: 0;
  transition: background var(--transition);
}
.woocommerce-account .woocommerce-info .button:hover { background: var(--gold-light); }
```
> Если у кнопки реально пустой текст (не только цвет) — проверить разметку пустого состояния; стандартно WooCommerce пишет «Browse products» / «Перейти в магазин». Подпись должна быть видна. Цель: читаемая кнопка под текстом, ничего не наезжает.

## 3. ПОЛЯ «ПРОФИЛЯ» — КОМПАКТНЕЕ (премиум)

Сейчас поля Имя/Фамилия/Email слишком крупные и высокие — громоздко. Сделать изящнее: меньше высота, тоньше, больше воздуха вокруг, без «раздутости».

```css
/* Компактные изящные поля профиля */
.woocommerce-account .woocommerce-EditAccountForm .input-text,
.woocommerce-account .woocommerce-EditAccountForm input[type="text"],
.woocommerce-account .woocommerce-EditAccountForm input[type="email"],
.woocommerce-account .woocommerce-EditAccountForm input[type="password"] {
  padding: .55rem .75rem;       /* было выше — уменьшаем высоту */
  font-size: .9rem; font-weight: 300;
  background: var(--bg2); border: 1px solid rgba(197,165,90,.25); color: var(--cream);
  max-width: 440px;             /* не на всю ширину — изящнее */
}
.woocommerce-account .woocommerce-EditAccountForm .form-row { margin-bottom: .9rem; }
.woocommerce-account .woocommerce-EditAccountForm label {
  font-size: .68rem; letter-spacing: .12em; text-transform: uppercase;
  color: var(--text-muted); margin-bottom: .3rem;
}
/* блок смены пароля — те же компактные поля, поля на всю ширину блока, но не громоздкие */
.woocommerce-account fieldset .input-text { padding: .55rem .75rem; font-size: .9rem; max-width: 440px; }
```
> Цель: поля стали ниже и уже (max-width 440px), форма дышит, выглядит дорого, а не как раздутая админка. Кнопку «Сохранить изменения» оставить как есть (она норм).

---

## Проверка

1. **Высота:** в `account.css` вверху есть переменная `--ll-orders-content-shift` с русским комментарием; меняя её, заказчик двигает правую колонку **только раздела «Заказы»** вверх/вниз. Адреса и Профиль НЕ сдвигаются.
2. **Заказы (пусто):** текст «Заказов ещё не создано» и золотая кнопка — на разных строках, не наезжают; **подпись кнопки читаема** (тёмная на золоте).
3. **Профиль:** поля компактнее и уже (не на всю ширину), форма изящная, не громоздкая.
4. **Вход:** не тронут (эталон).
5. **Адреса и Профиль:** высота НЕ тронута (двигаем их отдельным этапом позже). Содержимое адресов не тронуто.
6. Мобила: сдвиг обнулён, ничего не наезжает.

## Ограничения
- НЕ трогать форму входа (идеальна) и содержимое «Адресов».
- Переменная высоты — ровно одна, с комментарием, легко найти.
- Не регрессировать логику (старт на заказах, гостевой checkout).

## Бэклог
- Боевой прогон у Натальи — ждём отчёт.
- Возврат #867 — ждёт баланс ЮKassa.
- Сверка тарифа 5Post с договором.
- Баг-репорт ipol: блочный чекаут `wc is not defined`.
- Снять `noindex` при запуске (кабинет/корзина/чекаут — закрыты).
