</main><!-- #main -->

<footer class="site-footer">
    <div class="footer-grid">
        <div>
            <h4>LoraLeya</h4>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>">Каталог</a>
            <a href="<?php echo home_url('/#scenarios'); ?>">Сценарии</a>
            <a href="<?php echo home_url('/#palette'); ?>">Палитра</a>
            <a href="<?php echo home_url('/individualnyy-zakaz/'); ?>">Индивидуальный заказ</a>
        </div>
        <div>
            <h4>Покупателю</h4>
            <a href="<?php echo home_url('/delivery/'); ?>">Оплата и доставка</a>
            <a href="<?php echo home_url('/returns/'); ?>">Возврат и обмен</a>
            <a href="<?php echo home_url('/oferta/'); ?>">Публичная оферта</a>
            <a href="<?php echo home_url('/privacy-policy/'); ?>">Политика конфиденциальности</a>
        </div>
        <div>
            <h4>Контакты</h4>
            <a href="tel:+79264950210">+7 926 495 02 10</a>
            <a href="mailto:loraleya-tex@yandex.ru">loraleya-tex@yandex.ru</a>
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
