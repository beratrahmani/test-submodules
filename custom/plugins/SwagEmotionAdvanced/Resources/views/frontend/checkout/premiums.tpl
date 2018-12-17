{extends file='parent:frontend/checkout/premiums.tpl'}

{block name='frontend_checkout_premium_slider'}
    {block name='frontend_checkout_premium_slider_quick_view'}
        {if $additionalQuickViewMode === 2}
            {block name='frontend_checkout_premium_slider_quick_view_all_products'}
                <div class="quick-view--premium-products-slider-wrapper"
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
