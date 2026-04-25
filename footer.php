</main><!-- #main -->

<footer class="site-footer">
    <div class="footer-grid">
        <div>
            <h4>LoraLeya</h4>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>">Каталог</a>
            <a href="<?php echo home_url('/#scenarios'); ?>">Сценарии</a>
            <a href="<?php echo home_url('/#palette'); ?>">Палитра</a>
            <a href="<?php echo home_url('/custom-order/'); ?>">Индивидуальный заказ</a>
        </div>
        <div>
            <h4>Покупателю</h4>
            <a href="<?php echo home_url('/delivery/'); ?>">Оплата и доставка</a>
            <a href="<?php echo home_url('/returns/'); ?>">Возврат и обмен</a>
            <a href="<?php echo home_url('/care/'); ?>">Уход за текстилем</a>
            <a href="<?php echo home_url('/reviews/'); ?>">Отзывы</a>
        </div>
        <div>
            <h4>Контакты</h4>
            <a href="tel:+7XXXXXXXXXX">+7 (xxx) xxx-xx-xx</a>
            <a href="mailto:info@loraleya.ru">info@loraleya.ru</a>
            <a href="https://t.me/loraleya" target="_blank">Telegram</a>
            <a href="#">VK</a>
        </div>
        <div class="footer-bottom">
            <span>&copy; <?php echo date('Y'); ?> LoraLeya &middot; Сделано в России с любовью</span>
            <span>Москва</span>
        </div>
    </div>
</footer>

<?php get_template_part('cart-widget'); ?>

<?php wp_footer(); ?>
</body>
</html>
