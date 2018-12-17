{extends file='parent:frontend/detail/data.tpl'}

{block name="frontend_detail_data"}
    {if $liveShopping}
        {include file="frontend/swag_live_shopping/detail/liveshopping-detail.tpl"}
    {/if}

    {$smarty.block.parent}
{/block}
