{extends file='parent:frontend/home/index.tpl'}

{* provide compatibility with bonus products sliders on home page *}
{block name='frontend_home_index_bonus_slider_body'}
    {block name='frontend_home_index_bonus_slider_body_quick_view'}
        {if $additionalQuickViewMode === 2}
            {block name='frontend_home_index_bonus_slider_body_quick_view_all_products'}
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
