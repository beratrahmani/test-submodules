{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_grid/grid.tpl"}

{block name="b2b_grid_form"}
    <input type="hidden" name="offerId" value="{$offer->id}">

    {foreach $errors as $error}
        <div class="grid--errors error--list">
            {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
        </div>
    {/foreach}

    {if $discountMessage}
        <div class="grid--errors error--list">
            {include file="frontend/_includes/messages.tpl" type="error" content='Discount has been removed because the sum of the discount and the reference discounts is greater than the amount'|snippet:'DiscountGreaterThanAmountMessage':'frontend/plugins/b2b_debtor_plugin'}
        </div>
    {/if}

    {$smarty.block.parent}
{/block}

{block name="b2b_grid_col_sort"}
    <option value="reference_number::asc"{if $gridState.sortBy === 'reference_number::asc'} selected="selected"{/if}>{s name="ReferenceNumberAsc"}Reference Number Ascending{/s}</option>
    <option value="reference_number::desc"{if $gridState.sortBy === 'reference_number::desc'} selected="selected"{/if}>{s name="ReferenceNumberDesc"}Reference Number Descending{/s}</option>
{/block}

{block name="b2b_grid_table_head"}
    <th>{s name="Product"}Product{/s}</th>
    <th>{s name="Quantity"}Quantity{/s}</th>
    <th>{s name="ListPrice"}Listprice{/s}</th>
    <th>{s name="DiscountPrice"}Discountprice{/s}</th>
    <th>{s name="Actions"}Actions{/s}</th>
{/block}
{block name="b2b_grid_table_row"}
        <tr class="{b2b_acl controller=b2bofferlineitemreference action=update}"
            data-row-id="{$row->id}"
            data-class="row"
            {if $offer->isEditableByUser()}
                data-mode="edit"
            {/if}
        >
            <td class="line-item--column-referencenumber">
                {$row->name}<br/>
                <small>{$row->referenceNumber}</small>
            </td>
            <td class="line-item--column-quantity">
                <div data-display="edit-mode" class="is--hidden">
                    <input type="number" name="quantity" value="{$row->quantity}" data-b2b-form-input-holder="true" data-targetElement="quantityHidden-{$row->id}" min="{$row->minPurchase|default:1}" step="{$row->purchaseStep|default:1}" {if $lineItemReference->maxPurchase}max="{$lineItemReference->maxPurchase}"{/if}>
                </div>

                <div data-display="view-mode">
                    {$row->quantity}
                </div>
            </td>
            <td>
                {$row->amountNet|currency}
                <br>
                <small>{s name="withTax"}with MwSt{/s}: {$row->amount|currency}</small>
            </td>
            <td class="line-item--column-discountamount">
                <div data-display="edit-mode" class="is--hidden">
                    <input class="price-input" type="number" name="discountAmountNetHidden" value="{round($row->discountAmountNet, 2)}" data-b2b-form-input-holder="true" data-targetElement="discountAmountNetHidden-{$row->id}" step="0.01">
                </div>

            <span data-display="view-mode">
                {$row->discountAmountNet|currency}
                <br>
                <small>{s name="withTax"}with MwSt{/s}: {$row->discountAmount|currency}</small>
            </span>
        </td>
        <td class="col-actions actions--offer-details">
            {if $offer->isEditableByUser()}
                <button
                        title="{s name="EditItem"}Edit Item{/s}"
                        type="button"
                        data-mode="edit"
                        class="btn is--default is--small {b2b_acl controller=b2bofferlineitemreference action=update}"
                >
                    <i class="icon--pencil"></i>
                </button>
                <form action="{url action=remove}" method="post" class="form--inline" data-ajax-panel-trigger-reload="offer-grid">
                    <input type="hidden" name="offerId" value="{$offer->id}">
                    <input type="hidden" name="lineItemId" value="{$row->id}">
                    <input type="hidden" name="confirmName" value="{$row->referenceNumber}">

                    <button type="submit"
                            title="{s name="DeleteItem"}Delete Item{/s}"
                            class="btn is--default no--edit is--small component-action-delete {b2b_acl controller=b2bofferlineitemreference action=remove}"
                            data-confirm="true"
                            data-confirm-url="{url controller="b2bconfirm" action="remove"}">
                        <i class="icon--trash no--edit"></i>
                    </button>
                </form>
            {/if}
        </td>
    </tr>
    <tr class="is--hidden" data-display="edit-mode">
        <td colspan="5" class="line-item--column-comment">
            <form action="{url action=update}" method="post" data-ajax-panel-trigger-reload="offer-grid">

                <input type="hidden" name="quantity" class="quantityHidden-{$row->id}" value="{$row->quantity}">
                <input type="hidden" name="discountAmountNet" class="discountAmountNetHidden-{$row->id}" value="{$row->discountAmountNet}">
                <input type="hidden" name="id" value="{$row->id}">
                <input type="hidden" name="listId" value="{$offer->listId}">
                <input type="hidden" name="offerId" value="{$offer->id}">

                <div class="comment--container offer-lineitem--save">
                    <div class="comment--actions">
                        <a href="{url offerId=$offer->id}" class="btn is--secondary is--cancel">{s name="Cancel"}Cancel{/s}</a>
                        <button type="submit" class="btn btn--small is--primary is--save">{s name="Save"}Save{/s}</button>
                    </div>
                </div>
            </form>
        </td>
    </tr>
    <tr data-display="spacer-mode">
        {*Empty row to prevent wrong color for the next row due to the open comment row *}
    </tr>
{/block}