{extends file="parent:frontend/listing/listing.tpl"}

{block name="frontend_listing_listing_content"}
    {if $b2bListingView == 'table'}
        <div class="listing b2b-listing">
            {include file="frontend/listing/listing_inner_container.tpl"}
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}


{block name="frontend_listing_listing_container"}
{if $b2bListingView == 'table'}
    <div class="module-fastorder fast-order-inputs">
        {$smarty.block.parent}

        {action module=widgets controller=b2bremotebox action=listing}
    </div>
{else}
    {$smarty.block.parent}
{/if}

{/block}