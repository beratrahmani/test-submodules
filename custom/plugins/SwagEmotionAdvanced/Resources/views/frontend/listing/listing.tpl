{extends file='parent:frontend/listing/listing.tpl'}

{block name='frontend_listing_listing_container'}
    {block name='frontend_listing_listing_container_quick_view'}
        {if $additionalQuickViewMode === 2}
            {block name='frontend_listing_listing_container_quick_view_all_products'}
                <div class="quick-view--listing-wrapper"
                     data-quickview="true"
                     data-ajaxUrl="{url module='Widgets' controller='SwagEmotionAdvanced' action='index'}"
                     data-productLinkSelector=".listing--container .product--box a:not('[data-open-wishlist-modal]')"
                     data-productSelector="div*[data-ordernumber]">
                    {$smarty.block.parent}
                </div>
            {/block}
        {elseif $additionalQuickViewMode === 3}
            {block name='frontend_listing_listing_container_quick_view_only_details'}
                <div class="quick-view--listing-wrapper"
                     data-quickview="true"
                     data-ajaxUrl="{url module='Widgets' controller='SwagEmotionAdvanced' action='index'}"
                     data-productLinkSelector=".product--detail-btn .buybox--button"
                     data-detailBtnSelector=".product--detail-btn">
                    {$smarty.block.parent}
                </div>
            {/block}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}
