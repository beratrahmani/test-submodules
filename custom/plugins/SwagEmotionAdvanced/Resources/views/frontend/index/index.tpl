{extends file='parent:frontend/index/index.tpl'}

{block name='frontend_index_left_last_articles'}
    {block name='frontend_index_left_last_articles_quick_view_last_seen_products'}
        {if $additionalQuickViewMode === 2 && $sLastArticlesShow && !$isEmotionLandingPage}
            {block name='frontend_index_left_last_articles_quick_view_last_seen_products_all_products'}
                <div class="quick-view--last-seen-products-wrapper"
                     data-quickview="true"
                     data-ajaxUrl="{url module='Widgets' controller='SwagEmotionAdvanced' action='index'}"
                     data-productLinkSelector=".last-seen-products--container .product--box a">
                    {$smarty.block.parent}
                </div>
            {/block}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}

{* provide compatibility with product advisor listing *}
{block name='frontend_advisor_listing_container_outer'}
    {block name='frontend_advisor_listing_container_outer_quick_view'}
        {if $additionalQuickViewMode === 2}
            {block name='frontend_advisor_listing_container_outer_quick_view_all_products'}
                <div class="quick-view--product-advisor-listing-wrapper"
                     data-quickview="true"
                     data-ajaxUrl="{url module='Widgets' controller='SwagEmotionAdvanced' action='index'}"
                     data-productLinkSelector="#advisor-listing--container .product--box a">
                    {$smarty.block.parent}
                </div>
            {/block}
        {elseif $additionalQuickViewMode === 3}
            {block name='frontend_advisor_listing_container_outer_quick_view_product_details'}
                <div class="quick-view--product-advisor-listing-wrapper"
                     data-quickview="true"
                     data-ajaxUrl="{url module='Widgets' controller='SwagEmotionAdvanced' action='index'}"
                     data-productLinkSelector="#advisor-listing--container .product--detail-btn .buybox--button"
                     data-detailBtnSelector=".product--detail-btn">
                    {$smarty.block.parent}
                </div>
            {/block}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}

{* provide compatibility with SwagAdvancedCart wish lists *}
{block name='frontend_wishlist_index_container_info'}
    {block name='frontend_wishlist_index_container_info_quick_view'}
        {if $additionalQuickViewMode === 2}
            {block name='frontend_wishlist_index_container_info_quick_view_all_products'}
                <div class="quick-view--note-wrapper"
                     data-quickview="true"
                     data-ajaxUrl="{url module='Widgets' controller='SwagEmotionAdvanced' action='index'}"
                     data-productLinkSelector=".note--item a">
                    {$smarty.block.parent}
                </div>
            {/block}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}

{block name='frontend_wishlist_public_content'}
    {block name='frontend_wishlist_public_content_quick_view'}
        {if $additionalQuickViewMode === 2}
            {block name='frontend_wishlist_public_content_quick_view_all_products'}
                <div class="quick-view--note-wrapper"
                     data-quickview="true"
                     data-ajaxUrl="{url module='Widgets' controller='SwagEmotionAdvanced' action='index'}"
                     data-productLinkSelector=".note--item a">
                    {$smarty.block.parent}
                </div>
            {/block}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}
