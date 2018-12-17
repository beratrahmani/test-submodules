{extends file="parent:frontend/listing/listing_actions.tpl"}

{block name="frontend_listing_actions_sort"}{/block}
{block name="frontend_listing_actions_filter"}
    <div class="is--hidden" data-filter-form="true" data-load-facets="false" data-instant-filter-result="false" data-is-in-sidebar="false">

    </div>
{/block}
{block name="frontend_listing_actions_filter_options"}{/block}
{block name="frontend_listing_actions_paging"}
    {include file="frontend/advisor/result/action-pagination.tpl"}
{/block}