{namespace name=frontend/plugins/b2b_debtor_plugin}
{extends file="frontend/index/index.tpl"}

{block name='frontend_index_logo_trusted_shops'}
    <form action="{url action="backToCheckout"}" method="post" class="ignore--b2b-ajax-panel form--offercheckout-back is--align-right">
        <input type="hidden" name="offerId" value="{$offerId}">
        <button class="btn is--default is--icon-left">
            {s name="BackToCart"}Back to the Cart{/s}<i class="icon--arrow-left"></i>
        </button>
    </form>
{/block}

{block name='frontend_index_content_left'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_index_breadcrumb'}{/block}

{block name='frontend_index_shop_navigation'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_index_navigation_categories_top'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_index_top_bar_container'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_index_footer"}
    {if !$theme.checkoutFooter}
        {$smarty.block.parent}
    {else}
        {block name='frontend_index_checkout_confirm_footer'}
            {include file="frontend/index/footer_minimal.tpl"}
        {/block}
    {/if}
{/block}

{block name='frontend_index_content'}
    <div class="b2b--ajax-panel" data-id="offer-grid" data-url="{url action=grid controller=b2bofferthroughcheckout offerId=$offerId}" data-plugins="b2bGridComponent"></div>

    <div class="b2b--ajax-panel b2b-modal-panel" data-id="offer-detail-product" data-plugins="b2bAjaxProductSearch"></div>
{/block}