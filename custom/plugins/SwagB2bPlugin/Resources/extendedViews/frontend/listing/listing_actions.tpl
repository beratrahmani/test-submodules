{extends file="parent:frontend/listing/listing_actions.tpl"}

{* Listing pagination *}
{block name="frontend_listing_actions_sort"}
    {$smarty.block.parent}
    {if $b2bSuite}
        <div class="{b2b_acl controller=b2bfastorderremote action=remoteListFastOrder}">
            <form class="action--sort action--content block b2b--listing-form" method="get">
                <input type="hidden" name="b2bListingView" value="{if $b2bListingView !== "table"}table{else}listing{/if}">
                {if $sRequests.sSearchOrginal}
                    <input type="hidden" name="sSearch" value="{$sRequests.sSearchOrginal}">
                {/if}
                {if $sPage}
                    <input type="hidden" name="p" value="{$sPage}">
                {/if}
                <button type="submit" title="{s name="ChangeView" namespace="frontend/plugins/b2b_debtor_plugin"}Change view{/s}" class="btn is--icon-left btn--view-switch">
                    {if $b2bListingView !== "table"}
                        <i class="icon--list"></i>
                        <span>
                            {s name="ListingViewTable" namespace="frontend/plugins/b2b_debtor_plugin"}Table view{/s}
                        </span>
                    {else}
                        <i class="icon--layout"></i>
                        <span>
                            {s name="ListingViewDefault" namespace="frontend/plugins/b2b_debtor_plugin"}Default view{/s}
                        </span>
                    {/if}
                </button>
            </form>
        </div>
    {/if}
{/block}