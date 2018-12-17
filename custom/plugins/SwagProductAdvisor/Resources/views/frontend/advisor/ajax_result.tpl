{$othersTitleFound = false}
{foreach $advisor['result'] as $product}
    {block name="frontend_advisor_listing_ajax_others_title"}
        {include file="frontend/advisor/result/others.tpl" scope='parent'}
    {/block}

    {* Single product box *}
    {block name="frontend_advisor_listing_ajax_article"}
        {include file="frontend/listing/box_article.tpl" sArticle=$product productBoxLayout=$advisor['listingLayout']}
    {/block}
{/foreach}

{if $advisor['othersTitle']['showLastTitle']}
    {block name="frontend_advisor_listing_ajax_others_last_title"}
        {include file="frontend/advisor/result/others_title.tpl" scope='parent'}
    {/block}
{/if}