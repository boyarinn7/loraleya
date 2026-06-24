<?php
/**
 * Template Name: О бренде
 * Шаблон страницы /about/
 */

get_header();
?>

<style>
/* ============================================================
   PAGE-ABOUT — локальные стили
   ============================================================ */

/* HERO */
.ab-hero {
    padding: 8rem 4rem 5rem;
    display: flex;
    align-items: center;
    gap: 5rem;
    max-width: 1100px;
    margin: 0 auto;
}
.ab-hero-left { flex: 1; }
.ab-hero-ey {
    font-size: .58rem;
    letter-spacing: .35em;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: 1rem;
    font-weight: 500;
    font-family: var(--sans);
}
.ab-hero-h1 {
    font-family: var(--serif);
    font-size: clamp(2rem, 4.5vw, 3.2rem);
    font-weight: 300;
    color: var(--cream);
    line-height: 1.15;
    margin-bottom: .8rem;
}
.ab-hero-sub {
    font-family: var(--serif);
    font-size: clamp(1.2rem, 2.5vw, 1.7rem);
    font-weight: 300;
    color: var(--text-muted);
    line-height: 1.3;
    margin-bottom: 1.2rem;
}
.ab-hero-quote {
    font-family: var(--serif);
    font-size: 1.1rem;
    font-style: italic;
    color: var(--text);
    line-height: 1.7;
    border-left: 2px solid var(--gold);
    padding-left: 1.5rem;
    margin-bottom: 1.5rem;
}
.ab-hero-text {
    font-size: .85rem;
    color: var(--text-muted);
    font-weight: 300;
    line-height: 1.9;
}
.ab-hero-right {
    flex: 0 0 280px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}
.ab-photo-slot {
    width: 200px;
    aspect-ratio: 3/4;
    border: 1px solid rgba(197,165,90,.12);
    border-radius: 4px;
    overflow: hidden;
}
.ab-photo-slot img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center 20%;
    display: block;
}
.ab-seal-since {
    font-size: .6rem;
    color: var(--text-muted);
    letter-spacing: .15em;
}

/* SECTION shared */
.ab-sec {
    padding: 4.5rem 4rem;
    max-width: 1100px;
    margin: 0 auto;
}
.ab-sec--alt {
    background: var(--bg2);
    border-top: 1px solid rgba(197,165,90,.06);
    border-bottom: 1px solid rgba(197,165,90,.06);
    max-width: none;
}
.ab-sec--alt > .ab-sec-inner {
    max-width: 1100px;
    margin: 0 auto;
    padding: 4.5rem 4rem;
}
.ab-ey {
    font-size: .56rem;
    letter-spacing: .35em;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: .6rem;
    font-weight: 500;
    font-family: var(--sans);
}
.ab-h2 {
    font-family: var(--serif);
    font-size: clamp(1.4rem, 2.8vw, 2rem);
    font-weight: 300;
    color: var(--cream);
    margin-bottom: .5rem;
    line-height: 1.2;
}
.ab-desc {
    color: var(--text-muted);
    font-weight: 300;
    max-width: 520px;
    margin-bottom: 2rem;
    font-size: .82rem;
    line-height: 1.8;
}
.ab-body {
    font-size: .85rem;
    color: var(--text);
    font-weight: 300;
    line-height: 1.9;
    max-width: 680px;
}
.ab-body p { margin-bottom: 1rem; }
.ab-body p:last-child { margin-bottom: 0; }
.ab-body a { color: var(--gold); border-bottom: 1px solid rgba(197,165,90,.25); transition: border-color .2s; }
.ab-body a:hover { border-color: var(--gold); }
.ab-body strong { color: var(--cream); font-weight: 500; }

/* BLOCK 1 — facts table */
.ab-facts {
    border: 1px solid rgba(197,165,90,.08);
    width: 100%;
    max-width: 700px;
    border-collapse: collapse;
    font-size: .8rem;
    margin-top: 1.5rem;
}
.ab-facts td {
    padding: .7rem 1rem;
    border-bottom: 1px solid rgba(197,165,90,.05);
    line-height: 1.5;
}
.ab-facts tr:last-child td { border-bottom: none; }
.ab-facts td:first-child {
    color: var(--text-muted);
    font-weight: 500;
    white-space: nowrap;
    width: 200px;
    font-family: var(--sans);
    font-size: .7rem;
    letter-spacing: .05em;
}
.ab-facts td:last-child { color: var(--cream); }

/* BLOCK 5 — reasons */
.ab-reasons { display: flex; flex-direction: column; gap: 1.5rem; margin-top: 1.5rem; }
.ab-reason {
    padding: 1.5rem 2rem;
    border: 1px solid rgba(197,165,90,.06);
}
.ab-reason-title {
    font-family: var(--serif);
    font-size: 1rem;
    color: var(--cream);
    font-weight: 400;
    margin-bottom: .4rem;
}
.ab-reason-text {
    font-size: .75rem;
    color: var(--text-muted);
    font-weight: 300;
    line-height: 1.8;
}
.ab-reason-text a { color: var(--gold); }

/* BLOCK 4 — body full-width */
.ab-sec .ab-body { max-width: 760px; }

/* BLOCK 6 — manifesto blockquote */
.ab-manifesto {
    max-width: 720px;
    margin-top: 1.5rem;
    border-left: 2px solid rgba(197,165,90,.3);
    padding-left: 2rem;
}
.ab-manifesto__body {
    font-family: var(--serif);
    font-size: 1rem;
    font-style: italic;
    color: var(--text);
    line-height: 1.9;
}
.ab-manifesto__body p { margin-bottom: .9rem; }
.ab-manifesto__body p:last-child { margin-bottom: 0; }
.ab-manifesto__sig {
    font-size: .7rem;
    color: var(--text-muted);
    font-weight: 300;
    margin-top: 1.2rem;
    letter-spacing: .05em;
}
.ab-manifesto__sig strong { color: var(--cream); font-weight: 500; }

/* BLOCK 7 — contacts */
.ab-contacts-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-top: 1.5rem;
}
.ab-ct {
    padding: 1.5rem;
    border: 1px solid rgba(197,165,90,.06);
    text-align: center;
    transition: border-color .3s;
}
.ab-ct:hover { border-color: rgba(197,165,90,.25); }
.ab-ct-label {
    font-size: .55rem;
    letter-spacing: .2em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: .3rem;
    font-weight: 500;
    font-family: var(--sans);
}
.ab-ct-val { font-size: .85rem; color: var(--cream); font-weight: 400; }
.ab-ct-hint { font-size: .58rem; color: var(--text-muted); font-weight: 300; margin-top: .2rem; }
.ab-ct-note {
    font-size: .72rem;
    color: var(--text-muted);
    font-weight: 300;
    line-height: 1.8;
    margin-top: 1.5rem;
    max-width: 560px;
}

/* BLOCK 8 — CTA */
.ab-cta {
    text-align: center;
    padding: 4.5rem 4rem;
    border-top: 1px solid rgba(197,165,90,.06);
}
.ab-cta__h2 {
    font-family: var(--serif);
    font-size: clamp(1.4rem, 2.5vw, 2rem);
    font-weight: 300;
    color: var(--cream);
    margin-bottom: .6rem;
}
.ab-cta__desc {
    color: var(--text-muted);
    font-weight: 300;
    margin-bottom: 2rem;
    font-size: .82rem;
}
.ab-cta-links {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
}
.ab-cta-btn {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .9rem 2.2rem;
    background: var(--gold);
    color: var(--bg);
    font-size: .65rem;
    letter-spacing: .16em;
    text-transform: uppercase;
    font-weight: 600;
    text-decoration: none;
    transition: background .3s;
    font-family: var(--sans);
}
.ab-cta-btn:hover { background: var(--gold-light); color: var(--bg); }
.ab-cta-btn--outline {
    background: transparent;
    color: var(--gold);
    border: 1px solid var(--gold);
    font-weight: 500;
}
.ab-cta-btn--outline:hover { background: var(--gold); color: var(--bg); }

/* NUMBERS */
.ab-numbers {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
    margin-top: 2.5rem;
    padding-top: 2.5rem;
    border-top: 1px solid rgba(197,165,90,.06);
}
.ab-num { text-align: center; }
.ab-num-val {
    font-family: var(--serif);
    font-size: clamp(1.8rem, 3.5vw, 2.8rem);
    color: var(--cream);
    font-weight: 300;
    line-height: 1;
}
.ab-num-label {
    font-size: .6rem;
    color: var(--text-muted);
    font-weight: 300;
    margin-top: .4rem;
    letter-spacing: .05em;
    font-family: var(--sans);
}

/* RESPONSIVE */
@media (max-width: 1024px) {
    .ab-hero { flex-direction: column; padding: 7rem 1.5rem 3rem; gap: 2rem; }
    .ab-hero-right { flex: none; }
    .ab-photo-slot { width: 100%; max-width: 260px; }
    .ab-sec { padding: 3rem 1.5rem; }
    .ab-sec--alt > .ab-sec-inner { padding: 3rem 1.5rem; }
    .ab-contacts-grid { grid-template-columns: 1fr; }
    .ab-numbers { grid-template-columns: repeat(2, 1fr); }
    .ab-cta { padding: 3rem 1.5rem; }
}
@media (max-width: 640px) {
    .ab-cta-btn { width: 100%; justify-content: center; }
    .ab-numbers { grid-template-columns: repeat(2, 1fr); gap: 1.5rem; }
    .ab-facts td:first-child { width: 130px; }
}
</style>

<!-- HERO -->
<section class="ab-hero">
    <div class="ab-hero-left">
        <div class="ab-hero-ey">О бренде</div>
        <h1 class="ab-hero-h1"><?php the_title(); ?></h1>
        <div class="ab-hero-sub">Жаккардовый столовый текстиль из Раменского района Подмосковья</div>
        <div class="ab-hero-quote">«Красиво накрытый стол — это не роскошь, а ежедневный ритуал, который делает жизнь теплее»</div>
        <p class="ab-hero-text">LoraLeya — российский бренд жаккардового столового текстиля, основан в 2022 году Натальей Куренковой. Мастерская в Подмосковье шьёт скатерти, дорожки, салфетки и куверты в 17 цветах.</p>
    </div>
    <div class="ab-hero-right">
        <div class="brand-seal">
            <span class="brand-seal__top">Сделано в России</span>
            <span class="brand-seal__logo">LoraLeya</span>
            <span class="brand-seal__bottom">С любовью</span>
        </div>
        <div class="ab-seal-since">Раменское · с 2022 года</div>
        <div class="ab-photo-slot">
            <img src="https://loraleya.ru/wp-content/uploads/2026/06/natalia-kurenkova-loraleya.webp"
                 alt="Наталья Куренкова, основательница бренда LoraLeya"
                 width="1600" height="2133"
                 fetchpriority="high">
        </div>
    </div>
</section>

<!-- BLOCK 1 — Карточка бренда -->
<section class="ab-sec--alt">
    <div class="ab-sec-inner">
        <div class="ab-ey">Факты о бренде</div>
        <div class="ab-h2">Карточка LoraLeya</div>
        <table class="ab-facts">
            <tr><td>Бренд</td><td>LoraLeya</td></tr>
            <tr><td>Год основания</td><td>2022 (январь)</td></tr>
            <tr><td>Основательница</td><td>Наталья Куренкова</td></tr>
            <tr><td>Производство</td><td>Раменский район, Подмосковье</td></tr>
            <tr><td>Материал</td><td>Жаккардовая ткань</td></tr>
            <tr><td>Ассортимент</td><td>17 цветов · готовые наборы на 2, 4 и 6 персон · индивидуальный пошив</td></tr>
            <tr><td>Изделия</td><td>Скатерти · дорожки · салфетки · куверты (конверты для столовых приборов)</td></tr>
            <tr><td>Сертификация</td><td>Декларация о соответствии</td></tr>
            <tr><td>Контакты</td><td>loraleya-tex@yandex.ru · +7 926 495 02 10</td></tr>
        </table>
    </div>
</section>

<!-- BLOCK 2 — Что мы делаем -->
<section class="ab-sec">
    <div class="ab-ey">Что мы делаем</div>
    <div class="ab-h2">Текстиль, который собирает стол</div>
    <div class="ab-body">
        <p>LoraLeya делает жаккардовый столовый текстиль для дома.</p>
        <p>В коллекции 17 однотонных оттенков, у каждого — мраморный перелив. Цвет меняется под углом света.</p>
        <p>Шьём всё: скатерти, дорожки, салфетки, куверты (конверты для столовых приборов). В каждом из 17 цветов. Готовые наборы — на 2, 4 и 6 персон. Под нестандартный стол — пошив по точным меркам.</p>
        <p>Производство в Раменском районе Подмосковья.</p>
    </div>
    <div class="ab-numbers">
        <div class="ab-num">
            <div class="ab-num-val">17</div>
            <div class="ab-num-label">цветов в палитре</div>
        </div>
        <div class="ab-num">
            <div class="ab-num-val">4</div>
            <div class="ab-num-label">типа изделий</div>
        </div>
        <div class="ab-num">
            <div class="ab-num-val">200+</div>
            <div class="ab-num-label">наименований товаров</div>
        </div>
        <div class="ab-num">
            <div class="ab-num-val">7–14</div>
            <div class="ab-num-label">дней на инд. заказ</div>
        </div>
    </div>
</section>

<!-- BLOCK 3 — История -->
<section class="ab-sec--alt">
    <div class="ab-sec-inner">
        <div class="ab-ey">История</div>
        <div class="ab-h2">С 2022 года</div>
        <div class="ab-body">
            <p>LoraLeya появился в январе 2022 года. Идею запустила Наталья Куренкова — экономист по образованию, до этого десять лет работавшая в компаниях, которые помогала строить с нуля. В свободное время — вязание, вышивка, ростовые цветы из изолона. В какой-то момент захотелось соединить экономический опыт и ручной труд в собственное дело.</p>
            <p>Первые две идеи провалились. Накопления были потрачены впустую, близкие не верили в проект.</p>
            <p>Третья идея сработала: столовый текстиль из нестандартной для российского рынка ткани — жаккарда. Так появился LoraLeya.</p>
        </div>
    </div>
</section>

<!-- BLOCK 4 — Как мы шьём -->
<section class="ab-sec">
    <div class="ab-ey">Как мы шьём</div>
    <div class="ab-h2">Мастерская в Подмосковье</div>
    <div class="ab-body">
        <p>Мастерская LoraLeya — в Раменском районе Подмосковья.</p>
        <p>Небольшое профессиональное ателье. Швеи, закройщицы, отпарщицы, упаковщицы — у каждого этапа свои руки.</p>
        <p>Жаккард везём от проверенных международных поставщиков. Раскрой и пошив — здесь, в России. На продукцию есть Декларация о соответствии.</p>
        <p>Каждый набор проходит через всю цепочку: от раскроя до упаковки. Качество смотрим на каждом шаге, не только в финале.</p>
        <p>Под нестандартный стол — <a href="<?php echo home_url('/individualnyy-zakaz/'); ?>">индивидуальный пошив</a> по точным меркам. Срок — 7–14 дней.</p>
    </div>
</section>

<!-- BLOCK 5 — За что нас выбирают -->
<section class="ab-sec--alt">
    <div class="ab-sec-inner">
        <div class="ab-ey">За что нас выбирают</div>
        <div class="ab-h2">Пять причин остаться</div>
        <div class="ab-reasons">
            <div class="ab-reason">
                <div class="ab-reason-title">17 оттенков в одной коллекции</div>
                <div class="ab-reason-text">Большинство брендов столового текстиля обходятся 3–5 цветами и парой авторских принтов. У нас — широкая однотонная палитра. От тёплого <a href="<?php echo home_url('/color/bezhevyj/'); ?>">бежевого</a> до парадного графита и классического <a href="<?php echo home_url('/color/belyj/'); ?>">белого</a>.</div>
            </div>
            <div class="ab-reason">
                <div class="ab-reason-title">Готовые наборы вместо отдельных позиций</div>
                <div class="ab-reason-text">Конкуренты продают скатерти и салфетки врозь. Покупателю приходится самому подбирать, что с чем сочетается. Мы делаем иначе: <a href="<?php echo home_url('/product/gotovyj-nabor/'); ?>">готовые комплекты</a> на 2, 4 и 6 персон. Дорожка, салфетки, куверты — всё в одном цвете.</div>
            </div>
            <div class="ab-reason">
                <div class="ab-reason-title">Жаккардовая ткань с переливом</div>
                <div class="ab-reason-text">В нише столового текстиля почти все играют в лён и хлопок. Жаккард — редкость. Его рисунок ткётся самими нитями, не печатается сверху. Поэтому цвет не выгорает и не вымывается при стирке. И тот самый перелив, который меняется под углом света.</div>
            </div>
            <div class="ab-reason">
                <div class="ab-reason-title">Индивидуальный пошив под форму стола</div>
                <div class="ab-reason-text">Стандартные размеры подходят не всем. Шьём под точные размеры круглых, овальных и прямоугольных столов. <a href="<?php echo home_url('/individualnyy-zakaz/'); ?>">Индивидуальный заказ</a> — 7–14 дней.</div>
            </div>
            <div class="ab-reason">
                <div class="ab-reason-title">Прозрачные размеры наборов</div>
                <div class="ab-reason-text">Под стол на 4 персоны — дорожки 140 или 175 см. На 6 персон — 240 или 300 см. Подходящий размер виден сразу, считать ничего не нужно.</div>
            </div>
        </div>
    </div>
</section>

<!-- BLOCK 6 — Философия (манифест Натальи) -->
<section class="ab-sec">
    <div class="ab-h2">Магия сервировки</div>
    <div class="ab-manifesto">
        <div class="ab-manifesto__body">
            <p>Насколько важна сервировка? Зачем делать культ из простого приёма пищи?</p>
            <p>Конечно, можно поесть прямо с плиты, не подогревая, на ходу, просто насытить желудок. Но что в итоге — сплошное кусочничество, никакого удовольствия от еды, переедание и гастрит.</p>
            <p>Другое дело, когда садишься за красиво сервированный стол. Даже самое простое блюдо можно подать со вкусом, в красивой посуде, украсить зеленью. Ты вдыхаешь аромат специй и с наслаждением вкушаешь каждый кусочек. В такие моменты ты находишься в гармонии с собой, никуда не спешишь и просто наслаждаешься процессом.</p>
            <p>Сигнал о насыщении приходит только через 20 минут — торопиться точно не нужно.</p>
            <p>Еда — это основная энергия нашего организма. И от нас зависит, какой именно энергией мы его наполним, положительной или отрицательной.</p>
            <p>Но как сервировать так, чтобы это было красиво и элегантно? Главная основа любой сервировки — это столовый текстиль. Это может быть скатерть или дорожка на стол, обязательно тканевые салфетки. Даже такой, на первый взгляд, незначительный аксессуар, как конверт для столовых приборов, придаст акцент всей сервировке.</p>
            <p>И какой же это культ? Нет, это не культ. Это магия сервировки и культура правильного питания.</p>
        </div>
        <div class="ab-manifesto__sig">— <strong>Наталья Куренкова, основательница LoraLeya</strong></div>
    </div>
</section>

<!-- BLOCK 7 — Контакты -->
<section class="ab-sec--alt">
    <div class="ab-sec-inner">
        <div class="ab-ey">Контакты и ателье</div>
        <div class="ab-h2">Свяжитесь с нами</div>
        <div class="ab-desc">Мастерская LoraLeya работает в Раменском районе Подмосковья. Заказы принимаем через сайт, по почте и телефону.</div>
        <div class="ab-contacts-grid">
            <div class="ab-ct">
                <div class="ab-ct-label">Электронная почта</div>
                <div class="ab-ct-val"><a href="mailto:loraleya-tex@yandex.ru">loraleya-tex@yandex.ru</a></div>
                <div class="ab-ct-hint">Для индивидуальных заказов и вопросов</div>
            </div>
            <div class="ab-ct">
                <div class="ab-ct-label">Телефон</div>
                <div class="ab-ct-val"><a href="tel:+79264950210">+7 926 495 02 10</a></div>
            </div>
            <div class="ab-ct">
                <div class="ab-ct-label">Индивидуальный пошив</div>
                <div class="ab-ct-val"><a href="<?php echo home_url('/individualnyy-zakaz/'); ?>">Оставить заявку</a></div>
                <div class="ab-ct-hint">Под точные размеры стола · 7–14 дней</div>
            </div>
        </div>
        <p class="ab-ct-note">Если стол нестандартного размера или нужен оттенок, которого нет в готовых наборах, — напишите на почту с размерами и пожеланиями. В течение дня свяжемся и рассчитаем стоимость.</p>
    </div>
</section>

<!-- BLOCK 8 — CTA -->
<section class="ab-cta">
    <div class="ab-cta__h2">Готовы выбрать текстиль для своего стола?</div>
    <div class="ab-cta__desc">Начните с того, что ближе</div>
    <div class="ab-cta-links">
        <a href="<?php echo home_url('/#palette'); ?>" class="ab-cta-btn">Палитра 17 цветов</a>
        <a href="<?php echo home_url('/#scenarios'); ?>" class="ab-cta-btn ab-cta-btn--outline">Сценарии сервировки</a>
        <a href="<?php echo home_url('/product/gotovyj-nabor/'); ?>" class="ab-cta-btn ab-cta-btn--outline">Готовые наборы</a>
        <a href="<?php echo home_url('/individualnyy-zakaz/'); ?>" class="ab-cta-btn ab-cta-btn--outline">Индивидуальный пошив</a>
    </div>
</section>

<?php get_footer(); ?>
