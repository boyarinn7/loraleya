<?php
/**
 * LoraLeya — Cart Widget
 * Глобальная иконка корзины + модалка/bottom-sheet
 * Подключается из footer.php через get_template_part('cart-widget')
 */
?>

<!-- Floating cart button -->
<button
    type="button"
    class="ll-cart-fab"
    id="llCartFab"
    aria-label="Открыть корзину"
    data-count="0"
>
    <svg class="ll-cart-fab__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
        <line x1="3" y1="6" x2="21" y2="6"/>
        <path d="M16 10a4 4 0 01-8 0"/>
    </svg>
    <span class="ll-cart-fab__badge" id="llCartFabBadge" hidden>0</span>
</button>

<!-- Modal/Bottom-sheet overlay -->
<div class="ll-cart-modal" id="llCartModal" aria-hidden="true">
    <div class="ll-cart-modal__overlay" data-close="1"></div>
    <div class="ll-cart-modal__panel" role="dialog" aria-modal="true" aria-labelledby="llCartModalTitle">
        <div class="ll-cart-modal__header">
            <h3 class="ll-cart-modal__title" id="llCartModalTitle">Корзина</h3>
            <button type="button"
                    id="llCartClear"
                    class="ll-cart-modal__clear"
                    aria-label="Очистить корзину"
                    title="Очистить корзину"
                    hidden>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                    <path d="M10 11v6"></path>
                    <path d="M14 11v6"></path>
                    <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"></path>
                </svg>
            </button>
            <button type="button" class="ll-cart-modal__close" data-close="1" aria-label="Закрыть">×</button>
        </div>
        <div class="ll-cart-modal__body" id="llCartModalBody">
            <!-- Заполняется через JS из loraleya_get_cart -->
            <div class="ll-cart-modal__empty">Загрузка…</div>
        </div>
        <div class="ll-cart-modal__footer" id="llCartModalFooter" hidden>
            <div class="ll-cart-modal__total">
                <span class="ll-cart-modal__total-lbl">Итого</span>
                <span class="ll-cart-modal__total-sum" id="llCartModalTotal">0 ₽</span>
            </div>
            <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="ll-cart-modal__checkout">Оформить заказ →</a>
            <button type="button" class="ll-cart-modal__add-more" id="llCartAddMore" hidden>+ Добавить в другом цвете</button>
        </div>
    </div>
</div>
