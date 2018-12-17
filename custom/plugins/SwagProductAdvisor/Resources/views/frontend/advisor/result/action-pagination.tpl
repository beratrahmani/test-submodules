{extends file="parent:frontend/listing/actions/action-pagination.tpl"}

{* Pagination - First page *}
{block name="frontend_listing_actions_paging_first"}
    {if $sPage > 1}
        <a href="{$baseUrl}&p=1" title="{"{s name='ListingLinkFirst'}{/s}"|escape}"
           class="paging--link paging--prev" data-action-link="true">
            <i class="icon--arrow-left"></i>
            <i class="icon--arrow-left"></i>
        </a>
    {/if}
{/block}

{* Pagination - Previous page *}
{block name='frontend_listing_actions_paging_previous'}
    {if $sPage > 1}
        <a href="{$baseUrl}&p={$sPage-1}" title="{"{s name='ListingLinkPrevious'}{/s}"|escape}" class="paging--link paging--prev" data-action-link="true">
            <i class="icon--arrow-left"></i>
        </a>
    {/if}
{/block}

{* Pagination - Next page *}
{block name='frontend_listing_actions_paging_next'}
    {if $sPage < $pages}
        <a href="{$baseUrl}&p={$sPage+1}" title="{"{s name='ListingLinkNext'}{/s}"|escape}" class="paging--link paging--next" data-action-link="true">
            <i class="icon--arrow-right"></i>
        </a>
    {/if}
{/block}

{* Pagination - Last page *}
{block name="frontend_listing_actions_paging_last"}
    {if $sPage < $pages}
        <a href="{$baseUrl}&p={$pages}" title="{"{s name='ListingLinkLast'}{/s}"|escape}" class="paging--link paging--next" data-action-link="true">
            <i class="icon--arrow-right"></i>
            <i class="icon--arrow-right"></i>
        </a>
    {/if}
{/block}