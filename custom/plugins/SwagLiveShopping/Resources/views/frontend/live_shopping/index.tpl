{extends file="parent:frontend/listing/index.tpl"}

{block name="frontend_index_header_title"}
    {s name="emotionLiveshoppingHeader" namespace="frontend/live_shopping/main"}{/s}
{/block}

{block name="frontend_listing_index_banner"}
    {include file='frontend/live_shopping/banner.tpl'}
{/block}

{block name="frontend_listing_index_text"}
    {include file='frontend/live_shopping/text.tpl'}
{/block}

{block name="frontend_listing_index_topseller"}{/block}

{block name="frontend_listing_index_layout_variables"}
    {$smarty.block.parent}

    {$showListing = true}
{/block}

{block name="frontend_index_header_meta_tags"}
    {$smarty.block.parent}
    <meta name="title" content="{$listingMetaTitle}"/>
{/block}

{block name='frontend_index_header_meta_keywords'}
    {$smarty.block.parent}

    {if $listingMetaKeywords}{$listingMetaKeywords}{/if}
{/block}

{block name='frontend_index_header_meta_description'}
    {$smarty.block.parent}

    {if $listingMetaDescription}{$listingMetaDescription}{/if}
{/block}

{block name="frontend_listing_box_article_rating"}
    {if {config namespace="SwagLiveShopping" name="displayRating"}}
        {$smarty.block.parent}
    {/if}
{/block}
