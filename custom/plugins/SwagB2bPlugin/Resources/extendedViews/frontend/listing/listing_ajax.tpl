{extends file="parent:frontend/listing/listing_ajax.tpl"}

{block name="frontend_listing_list_inline_ajax"}
    {if $b2bListingView == 'table'}
        <form method="post" class="b2b-ajax-listing-submit" data-target-element-id="b2b-order-list--message"
              action="{url controller=b2bfastorder action=processItemsFromListing}">
            {include file="frontend/listing/listing_inner_container.tpl"}
        </form>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}