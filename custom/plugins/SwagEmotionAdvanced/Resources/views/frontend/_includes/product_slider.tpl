{extends file='parent:frontend/_includes/product_slider.tpl'}

{block name='frontend_common_product_slider_container'}
    {block name='frontend_common_product_slider_container_quick_view_slider'}
        {if $additionalQuickViewMode === 2 && !$sEmotions}
            {block name='frontend_common_product_slider_container_quick_view_slider_all_products'}
                <div class="quick-view--slider-wrapper"
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
