{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.actions = false}
    {$modalSettings.content.padding = true}
    {$modalSettings.bottom = true}

    {if $offer->isEditableByUser()}
        {$modalSettings.actions = true}
    {/if}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="Positions"}Positions{/s}
{/block}

{* Modal Content: Grid Component: Actions *}
{block name="b2b_modal_base_content_inner_scrollable_inner_actions_inner"}
    {if $offer->isEditableByUser()}
        <button data-target="offer-detail-product"
                data-href="{url action="new" offerId=$offer->id}"
                class="btn is--primary component-action-create ajax-panel-link {b2b_acl controller=b2bofferlineitemreference action=new}">
            {s name="AddLineItem"}Add line item{/s}
        </button>
    {/if}
{/block}


{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}

    {include file="frontend/b2bofferlineitemreference/_head.tpl"}
    {include file="frontend/b2bofferlineitemreference/_grid.tpl"}

{/block}

{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <div>
        <form action="{url action=updateDiscount}" method="post" data-ajax-panel-trigger-reload="offer-grid">

            <div class="offer-input-group">
                <span class="group-addon is--left">{s name="Discount"}Discount{/s}:</span>
                <input type="number"
                       name="discount"
                       min="0"
                       step="any"
                       placeholder="{s name="AddDiscount"}Add discount{/s}"
                       {if $offer->discountValueNet}value="{round($offer->discountValueNet, 2)}"{/if}
                       {if !$offer->isEditableByUser()} disabled="disabled"{/if}
                >
                <span class="group-addon is--right is--wide">{b2b_currency_symbol}</span>
                <button type="submit"
                        class="btn is--secondary"
                        {if !$offer->isEditableByUser()}disabled="disabled"{/if}
                >
                    {s name="Save"}Save{/s}
                </button>
            </div>

            <input type="hidden" name="offerId" value="{$offer->id}">
        </form>

    </div>
{/block}