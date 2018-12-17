{extends file='frontend/detail/content.tpl'}

{* add wrapper class for quick view *}
{block name='frontend_index_content_inner'}
    {$theme.ajaxVariantSwitch = true}
    {block name="emotion_advanced_quick_view"}
        <div class="view--content">
            {block name="emotion_advanced_quick_view_content"}
                {$smarty.block.parent}
            {/block}
        </div>
    {/block}
{/block}

{* make product name a link *}
{block name='frontend_detail_index_name'}
    {block name='swag_emotion_advanced_quick_view_product_name'}
        <div class="product--title" itemprop="name">
            <a class="product--link" href="{$sArticle.linkDetailsRewrited}">{$sArticle.articleName}</a>
        </div>
    {/block}
{/block}

{* add button to link to the detail page of the product *}
{block name='frontend_detail_buy_button'}
    {include file="widgets/swag_emotion_advanced/buy_button.tpl"}

    {block name='swag_emotion_advanced_quick_view_product_detail_button'}
        <a href="{$sArticle.linkDetailsRewrited}" class="quick-view--product-detail-button block btn is--icon-right is--center is--large">
            {s name="productDetailPageButtonText" namesapce="widgets/swag_emotion_advanced/index"}{/s}
            <i class="icon--arrow-right"></i>
        </a>
    {/block}
{/block}

{* add a teaser of the description under the buy box *}
{block name='frontend_detail_index_buy_container_base_info'}
    {$smarty.block.parent}

    {block name='emotion_advanced_quick_view_description'}
        <div class="product--short-description">
            {$sArticle.description}
        </div>
    {/block}
{/block}

{* don't show product details like description or rating *}
{block name='frontend_detail_index_detail'}
{/block}

{* don't show cross selling or similar products *}
{block name='frontend_detail_index_tabs_cross_selling'}
{/block}
