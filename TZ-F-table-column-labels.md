# ТЗ Code-Клоду — Адаптив F: подписи колонок в свёрнутой таблице статьи (мобайл)

*Последняя мелочь компоненты F. Проблема: в статьях таблица на ≤640 сворачивается в столбик, и заголовки колонок (ХЛОПОК/ЖАККАРД) прячутся вместе с `thead` → остаются значения без подписи. Добавляем подпись колонки к каждому значению на мобиле. Только мобайл; десктоп без изменений. Работает для всех таблиц в статьях автоматически, разметку статей не трогаем.*

**Файлы:** `assets/js/main.js`, `style.css`
**Гейты:** ноль CLS; не трогать разметку статей/контент.

## 0. Git-чекпойнт
```bash
git add -A && git commit -m "checkpoint before F table column labels" --allow-empty
git tag pre-F-tablelabels
```

## 1. `assets/js/main.js` — проставить колонку каждому значению. Добавить В КОНЕЦ
```js
/* ===== Адаптив F: подписи колонок в свёрнутых таблицах статьи (мобайл) ===== */
(function () {
  var tables = document.querySelectorAll('.article-body table');
  if (!tables.length) return;
  tables.forEach(function (table) {
    var heads = [];
    table.querySelectorAll('thead th').forEach(function (th) {
      heads.push(th.textContent.trim());
    });
    if (heads.length < 2) return; // нет шапки — нечего подписывать
    table.querySelectorAll('tbody tr').forEach(function (tr) {
      tr.querySelectorAll('td').forEach(function (td, i) {
        if (i === 0) {
          td.classList.add('article-td-head'); // первая ячейка — имя параметра
        } else if (heads[i]) {
          td.setAttribute('data-col', heads[i]);
        }
      });
    });
  });
})();
```
> Атрибут `data-col` ставится всегда, но показывается только на ≤640 (см. CSS). На десктопе таблица не меняется.

## 2. `style.css` — показать подпись только на мобиле. Добавить В КОНЕЦ
```css
/* Адаптив F: подписи колонок в свёрнутой таблице статьи (только мобайл) */
@media (max-width: 640px) {
    .article-body td[data-col]::before {
        content: attr(data-col) ": ";
        color: var(--gold-dim, #8a7a4a);
        font-weight: 600;
    }
    .article-body td.article-td-head {
        color: var(--cream, #e8e0d0);
        font-weight: 600;
        padding-top: 0.6rem;
    }
}
```

## 3. Что НЕ делаем
- Разметку статей и содержимое таблиц — не трогаем (подписи берутся из существующего `thead`).
- Десктопную таблицу — не трогаем.

## 4. Приёмка
1. Статья с таблицей (например, `/blog/kak-vybrat-skatert/`), на **360 / телефоне**: у каждого значения видна его колонка — «Хлопок: после стирки до 5%», «Жаккард: нет»; имя параметра («Усадка») выделено.
2. **Десктоп** (>640): таблица как была — обычная шапка ХЛОПОК/ЖАККАРД, без задвоенных подписей.
3. Проверить, что подписи верные (Хлопок слева, Жаккард справа — порядок колонок).

## 5. Откат
```bash
git reset --hard pre-F-tablelabels
```

---
*После этого компонента **F закрыта**, и закрыт весь адаптивный спринт (A–F). Финал: повторный **PageSpeed mobile** (адаптив мог тронуть CLS/LCP), затем снятие общего **noindex**. Отдельный бэклог вне адаптива: гейтовые слова «полиэстер» в `single-scenario.php` (контент-ТЗ) — желательно до снятия noindex; обложка статьи «Как красиво сложить салфетки»; режим работы у Натальи (About/Schema).*
