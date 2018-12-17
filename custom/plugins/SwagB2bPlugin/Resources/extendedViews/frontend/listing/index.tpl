{extends file="parent:frontend/listing/index.tpl"}

{block name="frontend_listing_index_text"}
    {if $b2bListingView != 'table'}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_listing_index_topseller"}
    {if $b2bListingView != 'table'}
        {$smarty.block.parent}
    {/if}
{/block}