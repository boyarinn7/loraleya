# ТЗ-6: Доточка карточки товара

**Версия:** 1.0
**Дата:** 05.06.2026
**Тип:** правка `functions.php` + CSS-блок в `style.css`. **Без переписывания шаблонов.**
**Файлы:** `functions.php`, `style.css`
**Исполнитель:** Claude Code
**Автор ТЗ:** Sprint-Клд

> Доводка карточки товара по замечаниям Куренковой/Бориса. Всё через CSS + один хук — карточку (ТЗ-3) не трогаем структурно.

---

## Замечания и решения

1. Скрыть бейдж «Распродажа!».
2. Отключить зум фото при наведении (лайтбокс/листание оставить).
3. Поднять текст (сводку) к верхнему краю фото.
4. Текст табов (Детали и пр.) заходит под фото — убрать «липкость» галереи из ТЗ-3.
5. Стилизовать блок отзывов под тёмный luxury.

---

## ⚠️ ПЕРЕД СТАРТОМ
```
git add -A && git commit -m "checkpoint: перед доточкой карточки (ТЗ-6)"
git tag pre-pdp-polish
```

---

## ФАЗА 1 — `functions.php`: отключить зум галереи

Добавить в конец. Снимает только зум при наведении; лайтбокс и слайдер остаются.

```php
// === Доточка карточки товара (ТЗ-6) ===
add_action('after_setup_theme', function () {
    remove_theme_support('wc-product-gallery-zoom');
}, 20);
```

> Если зум не пропал — значит тема включает его в своём `after_setup_theme` позже; тогда поднять приоритет (число `20` увеличить до `99`). Проверяется на живой карточке.

---

## ФАЗА 2 — `style.css`: визуальные правки

Добавить в конец файла. Эти правила идут ПОСЛЕ блока ТЗ-3, поэтому переопределяют его по каскаду (в т.ч. снимают `sticky`).

```css

/* ===== Доточка карточки товара (ТЗ-6) ===== */

/* 1. Скрыть бейдж «Распродажа!» */
.woocommerce span.onsale,
.single-product span.onsale { display:none !important; }

/* 3. Текст сводки — к верхнему краю фото */
.single-product div.product { align-items:start !important; }
.single-product div.product .summary { margin-top:0 !important; padding-top:0 !important; }
.single-product div.product .summary > :first-child { margin-top:0 !important; }

/* 4. Убрать «липкость» галереи — фото больше не наезжает на табы */
.single-product div.product .woocommerce-product-gallery { position:static !important; top:auto !important; }

/* 5. Отзывы под luxury */
.single-product #reviews .commentlist{list-style:none; margin:0 0 2rem; padding:0}
.single-product #reviews .commentlist li{border:1px solid rgba(197,165,90,.12); background:var(--bg2,#1a1917); padding:1.2rem 1.4rem; margin-bottom:1rem}
.single-product #reviews .comment-author,.single-product #reviews .meta{color:var(--gold-dim,#8a7a4a); font-size:.8rem}
.single-product #reviews .description{color:var(--cream,#e8e0d0); font-weight:300}
.single-product #reviews .star-rating span,
.single-product .star-rating span{color:var(--gold,#c5a55a)}

.single-product #review_form_wrapper{border-top:1px solid rgba(197,165,90,.12); margin-top:1.5rem; padding-top:1.5rem}
.single-product #reviews #reply-title{font-family:var(--serif); font-size:1.5rem; color:var(--cream,#e8e0d0); font-weight:500}
.single-product #reviews .comment-form label{color:var(--gold-dim,#8a7a4a); font-size:.78rem; letter-spacing:.05em; text-transform:uppercase; display:block; margin-bottom:.4rem}
.single-product #reviews .comment-form input[type="text"],
.single-product #reviews .comment-form input[type="email"],
.single-product #reviews .comment-form textarea{
    width:100%; background:var(--bg3,#252420); color:var(--cream,#e8e0d0);
    border:1px solid rgba(197,165,90,.3); border-radius:0; padding:.7rem .9rem; font-family:var(--sans);
}
.single-product #reviews .comment-form textarea:focus,
.single-product #reviews .comment-form input:focus{outline:none; border-color:var(--gold,#c5a55a)}
.single-product #reviews .comment-form-rating .stars a{color:var(--gold-dim,#8a7a4a)}
.single-product #reviews .comment-form-rating .stars a:hover,
.single-product #reviews .comment-form-rating .stars a.active{color:var(--gold,#c5a55a)}
.single-product #reviews .form-submit input{
    background:var(--gold,#c5a55a)!important; color:var(--bg,#0e0e0c)!important; border:none; border-radius:0;
    font-family:var(--sans); font-weight:600; letter-spacing:.18em; text-transform:uppercase; font-size:.78rem;
    padding:.9rem 2rem; cursor:pointer; transition:.3s;
}
.single-product #reviews .form-submit input:hover{background:var(--gold-light,#d4bc7c)!important}
```

---

## Чек-лист

- [ ] `php -l functions.php` чист.
- [ ] Бейдж «Распродажа!» исчез на карточке и в каталоге.
- [ ] Наведение на фото больше не увеличивает (зума нет); клик по фото — лайтбокс работает, листание миниатюр работает.
- [ ] Заголовок/цена/свотчи начинаются на одном уровне с верхом фото.
- [ ] Прокрутка к табам: фото НЕ наезжает на «Детали/Описание/Отзывы», текст табов в нормальной ширине, ничего не уходит под фото.
- [ ] Таб «Отзывы»: поля формы тёмные с золотой рамкой, звёзды-рейтинг золотые, кнопка отправки золотая — в стиле сайта.
- [ ] Мобильный: одна колонка, ничего не разъехалось.
- [ ] Регресс: свотчи, выбор вариации, «в корзину», цена — работают как раньше.

> Если после правок текст сводки всё ещё ниже фото — пришли скрин, посмотрю реальную вложенность (возможно тема оборачивает `.summary` нестандартно, тогда поправлю селектор точечно).

---

## Что НЕ делаем
- НЕ трогаем свотчи, логику вариаций, корзину (ТЗ-3 работает).
- НЕ трогаем ссылки в статьях и баг сценариев — это отдельная волна (ждёт реальных URL).
- НЕ трогаем производительность и описание наборов — следующие шаги.

---

## Финал
```
git add -A
git commit -m "polish(woo): карточка товара — бейдж, зум, выравнивание, табы, отзывы (ТЗ-6)"
git push
```

## Откат
```
git reset --hard pre-pdp-polish
```
