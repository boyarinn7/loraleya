# ТЗ Code-Клоду — Адаптив, компонента A: МОБИЛЬНОЕ МЕНЮ

*Бридж от Спринт-Клода по ТЗ `tz-adaptiv-mobile-tablet.md` (раздел 4.A, приоритет №1).*
*Итеративная задача: эта компонента — первая, остальные (B–F) только после приёмки A на всех 4 брейках.*

**Файлы:** `header.php`, `assets/js/main.js`, `style.css`
**Гейты (из общего ТЗ, едут с задачей):** контент не скрывать (`display:none` на смысле запрещён); тач ≥44×44px; единицы rem; новый `!important` не плодить; ноль CLS (бургер/панель — `position:fixed`, шапку не сдвигают); НЕ трогать URL/слаги/таксономии/корзину/вариации/цены/тексты.

## 0. Git-чекпойнт
```bash
git add -A && git commit -m "checkpoint before adaptive-A mobile menu" --allow-empty
git tag pre-adaptive-A
```

## 1. `header.php` — бургер + выезжающая панель

**Найти** (блок «Личный кабинет» и закрытие шапки):
```php
    <a href="<?php echo home_url('/my-account/'); ?>" class="header-account" aria-label="Личный кабинет">
        Личный кабинет
    </a>
</header>
```

**Заменить на:**
```php
    <a href="<?php echo home_url('/my-account/'); ?>" class="header-account" aria-label="Личный кабинет">
        Личный кабинет
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Открыть меню" aria-expanded="false" aria-controls="mobileNav">
        <span></span><span></span><span></span>
    </button>
</header>

<!-- Мобильная навигация (выезжает на ≤1024) -->
<div class="mobile-nav-overlay" id="mobileNavOverlay"></div>
<nav id="mobileNav" class="mobile-nav" aria-label="Мобильное меню" aria-hidden="true">
    <a href="<?php echo home_url('/#scenarios'); ?>" class="<?php echo $is_scenario ? 'current-menu-item' : ''; ?>">Сценарии</a>
    <a href="<?php echo home_url('/#palette'); ?>" class="<?php echo $is_palette ? 'current-menu-item' : ''; ?>">Палитра</a>
    <a href="<?php echo home_url('/blog/'); ?>" class="<?php echo $is_blog ? 'current-menu-item' : ''; ?>">Блог</a>
    <a href="<?php echo home_url('/individualnyy-zakaz/'); ?>" class="<?php echo $is_custom_order ? 'current-menu-item' : ''; ?>">Индивидуальный заказ</a>
    <a href="<?php echo home_url('/about/'); ?>" class="<?php echo $is_about ? 'current-menu-item' : ''; ?>">О бренде</a>
    <a href="<?php echo home_url('/shop/'); ?>">Каталог</a>
    <a href="<?php echo home_url('/my-account/'); ?>">Личный кабинет</a>
</nav>
```
> Переменные `$is_scenario … $is_about` уже вычислены выше в `header.php` — подсветка активного пункта переносится автоматически.

## 2. `style.css` — добавить В КОНЕЦ файла (новые селекторы, существующее не трогаем)

```css
/* ===== Мобильное меню (компонента адаптива A) ===== */
.nav-toggle {
  display: none;
  flex-direction: column;
  justify-content: center;
  gap: 0.3rem;
  width: 2.75rem;   /* 44px тач */
  height: 2.75rem;
  padding: 0.6rem;
  background: none;
  border: none;
  cursor: pointer;
  z-index: 110;
}
.nav-toggle span {
  display: block;
  width: 1.5rem;
  height: 2px;
  background: var(--cream);
  transition: transform 0.3s, opacity 0.3s;
}
.mobile-nav {
  display: none;
  position: fixed;
  top: 0; right: 0;
  width: min(80vw, 20rem);
  height: 100dvh;
  z-index: 109;
  flex-direction: column;
  padding: 5rem 2rem 2rem;
  background: rgba(14, 14, 12, 0.98);
  backdrop-filter: blur(20px);
  border-left: 1px solid rgba(197, 165, 90, 0.12);
  transform: translateX(100%);
  transition: transform 0.35s ease;
  overflow-y: auto;
}
.mobile-nav a {
  display: flex;
  align-items: center;
  min-height: 2.75rem; /* 44px тач */
  font-family: var(--sans);
  font-size: 0.9rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--cream);
  border-bottom: 1px solid rgba(197, 165, 90, 0.08);
}
.mobile-nav a.current-menu-item { color: var(--gold); }
.mobile-nav-overlay {
  display: none;
  position: fixed;
  inset: 0;
  z-index: 108;
  background: rgba(0, 0, 0, 0.5);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.35s ease;
}
@media (max-width: 1024px) {
  .nav-toggle { display: flex; }
  .mobile-nav { display: flex; }
  .mobile-nav-overlay { display: block; }
}
/* Открытое состояние */
body.nav-open { overflow: hidden; }
body.nav-open .mobile-nav { transform: translateX(0); }
body.nav-open .mobile-nav-overlay { opacity: 1; pointer-events: auto; }
body.nav-open .nav-toggle span:nth-child(1) { transform: translateY(0.5rem) rotate(45deg); }
body.nav-open .nav-toggle span:nth-child(2) { opacity: 0; }
body.nav-open .nav-toggle span:nth-child(3) { transform: translateY(-0.5rem) rotate(-45deg); }
```
> `.main-nav { display:none }` на ≤1024 НЕ трогаем — десктоп-меню так и остаётся скрытым на мобиле, бургер его заменяет.

## 3. `assets/js/main.js` — добавить В КОНЕЦ файла

```js
/* ===== Мобильное меню (компонента адаптива A) ===== */
(function () {
  var toggle  = document.getElementById('navToggle');
  var nav     = document.getElementById('mobileNav');
  var overlay = document.getElementById('mobileNavOverlay');
  if (!toggle || !nav) return;

  function openMenu() {
    document.body.classList.add('nav-open');
    toggle.setAttribute('aria-expanded', 'true');
    nav.setAttribute('aria-hidden', 'false');
  }
  function closeMenu() {
    document.body.classList.remove('nav-open');
    toggle.setAttribute('aria-expanded', 'false');
    nav.setAttribute('aria-hidden', 'true');
  }

  toggle.addEventListener('click', function () {
    document.body.classList.contains('nav-open') ? closeMenu() : openMenu();
  });
  if (overlay) overlay.addEventListener('click', closeMenu);
  nav.querySelectorAll('a').forEach(function (a) {
    a.addEventListener('click', closeMenu);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' || e.key === 'Esc') closeMenu();
  });
})();
```

## 4. Приёмка (на 1024 / 768 / 414 / 360 — скрин «после» на каждом)
- Бургер виден ≤1024, скрыт >1024; десктоп-меню наоборот.
- Меню открывается/закрывается: по бургеру, по клику на оверлей, по клику на ссылку, по `Esc`.
- Все 7 ссылок (5 + Каталог + Личный кабинет) доступны и жмутся пальцем (≥44px).
- Активный пункт подсвечен золотым.
- При открытом меню скролл `body` заблокирован.
- Нет горизонтального скролла; шапка не «прыгает» (CLS=0); контент/H1 в закрытом состоянии не перекрыты.
- Бургер анимируется в ×.

## 5. После приёмки
- Чистка кэша («Удалить весь кэш»).
- Скрины «после» (4 брейка) — как verified-source.
- Только потом — компонента B (конфликт `total-sticky` × `cart-fab`).

## 6. Откат
```bash
git reset --hard pre-adaptive-A
```

---
*Общий бэклог адаптива (едет дальше, не теряем): стандартизация лестницы брейкпоинтов — отложено; гейтовые слова в `single-scenario.php` — отдельным контент-ТЗ; режим работы у Натальи — для About/Schema, адаптив не блокирует.*
