{extends file="parent:frontend/listing/actions/action-filter-panel.tpl"}

{block name="frontend_listing_actions_filter_form_page"}
    {$smarty.block.parent}
    {if $b2bSuite}
        <input type="hidden" id="b2bListingView" name="b2bListingView" value="{$b2bListingView}">
    {/if}
{/block}