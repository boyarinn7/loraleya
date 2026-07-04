# ТЗ — Микроправка: подсказка профиля торчит за край поля

**Контекст.** Подсказка под «Отображаемое имя» теперь под полем (хорошо), но она **шире поля** — «хвост» текста вылезает за правый край поля «Отображаемое имя». Нужно: уменьшить шрифт и ограничить подсказку шириной поля, чтобы текст переносился внутри границ поля и не торчал.

**Только эта правка.** Остальное не трогать.

---

## Что сделать

В `account.css`, в блоке подсказок (`/* ---- Подсказки под полями (description) ... */`, тот, что с `display:block !important`), изменить два значения:

1. **Шрифт мельче:** `font-size: .72rem` → `font-size: .66rem`.
2. **Ширину подсказки ограничить шириной поля** (поле = `max-width: 440px`): заменить `max-width: 100% !important;` на `max-width: 440px !important;`.

Итоговый блок должен выглядеть так (меняются только две строки, остальное как есть):

```css
.woocommerce-account .woocommerce-EditAccountForm p.form-row .description,
.woocommerce-account .woocommerce-EditAccountForm p.form-row em,
.woocommerce-account .woocommerce-EditAccountForm p.form-row > span:not(.woocommerce-input-wrapper),
.woocommerce-account form .form-row .description,
.woocommerce-account span.description,
.woocommerce-account em.description {
  display: block !important;
  clear: both !important;
  float: none !important;
  width: 100% !important;
  max-width: 440px !important;   /* ← было 100% — теперь по ширине поля, хвост не торчит */
  margin: .35rem 0 0 !important;
  font-family: var(--sans);
  font-size: .66rem;             /* ← было .72rem — мельче */
  font-style: normal;
  line-height: 1.4;
  letter-spacing: .02em;
  color: var(--text-muted);
}
```

> Результат: подсказка не шире поля (440px), текст переносится внутри ширины поля, «хвост» за правым краем поля пропадает; шрифт мельче.

## Проверка
1. Подсказка «Так ваше имя…» не выходит за правый край поля «Отображаемое имя» — переносится в пределах ширины поля.
2. Шрифт подсказки стал мельче.
3. Остальное не изменилось.
