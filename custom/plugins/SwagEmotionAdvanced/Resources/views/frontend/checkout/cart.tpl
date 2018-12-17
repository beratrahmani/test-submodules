{extends file="parent:frontend/checkout/cart.tpl"}

{* provide compatibility with bonus products sliders on checkout cart page *}
{block name='frontend_checkout_cart_bonus_slider_body'}
    {block name='frontend_checkout_cart_bonus_slider_body_quick_view'}
        {if $additionalQuickViewMode === 2}
            {block name='frontend_checkout_cart_bonus_slider_body_quick_view_all_products'}
                <div class="quick-view--bonus-slider-wrapper"
                     data-quickview="true"
                     data-ajaxUrl="{url module='Widgets' controller='SwagEmotionAdvanced' action='index'}"
                     data-productLinkSelector=".product-slider--container .product--box a">
                    {$smarty.block.parent}
                </div>
            {/block}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}

{* provide compatibility with free goods promotion products sliders on checkout cart page *}
{block name='frontend_checkout_promotion_free_goods_slider'}
    {block name='frontend_checkout_promotion_free_goods_slider_quick_view'}
        {if $additionalQuickViewMode === 2}
            {block name='frontend_checkout_promotion_free_goods_slider_quick_view_all_products'}
                <div class="quick-view--free-goods-slider-wrapper"
                     data-quickview="true"
                     data-ajaxUrl="{url module='Widgets' controller='SwagEmotionAdvanced' action='index'}"
                     data-productLinkSelector=".product-slider--container .product--inner a">
                    {$smarty.block.parent}
                </div>
            {/block}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}
