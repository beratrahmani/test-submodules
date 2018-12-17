{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.actions = false}

    {if $orderContext->isEditable()}
        {$modalSettings.actions = true}
    {/if}

    {$modalSettings.content.padding = false}
    {$modalSettings.bottom = false}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="Positions"}Positions{/s}
{/block}

{* Modal Content: Grid Component: Actions *}
{block name="b2b_modal_base_content_inner_scrollable_inner_actions_inner"}
    {if $orderContext->isEditable()}
        <button data-target="contingent-tab-content"
                data-href="{url action="new" orderContextId=$orderContext->id}"
                title="{s name="AddLineItem"}Add line item{/s}"
                class="btn component-action-create ajax-panel-link {b2b_acl controller=b2borderlineitemreference action=new}">
            {s name="AddLineItem"}Add line item{/s}
        </button>
    {/if}
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    {include file="frontend/b2borderlineitemreference/_head.tpl"}

    {foreach $errors as $error}
        <div class="modal--errors error--list">
            {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
        </div>
    {/foreach}

    {if $itemGrid.data|count}
        <table class="table--contacts table--responsive b2b--component-grid" data-row-count="{$itemGrid.data|count}">
            <thead>
                <tr class="pointer--default">
                    <th>{s name="Product"}Product{/s}</th>
                    <th>{s name="Quantity"}Quantity{/s}</th>
                    <th>{s name="UnitPrice"}Unit price{/s}</th>
                    <th>{s name="Comment"}Comment{/s}</th>
                    <th>{s name="Actions"}Actions{/s}</th>
                </tr>
            </thead>
            <tbody>

            {$orderEditable = $orderContext->isEditable()}

            {foreach $itemGrid.data as $row}
                {$itemEditable = $row->isEditable()}
                <tr class="{b2b_acl controller=b2borderlineitemreference action=updateLineItem}{if !$orderEditable} pointer--default{/if}{if !$itemEditable} is--disabled{/if}"
                    data-row-id="{$row->id}"
                    data-class="row"
                    {if $itemEditable && $orderEditable}data-mode="edit"{/if}
                >
                    <td class="modal-col-product" data-label="{s name="Product"}Product{/s}" class="{b2b_acl controller=b2borderlineitemreference action=updateLineItem}">
                        {$row->name}
                        <br>
                        <small>{$row->referenceNumber}</small>

                        {if $row->orderList}
                            <br/>
                            <span class="info-text">{s name="OrderList"}Order List{/s}: {$row->orderList}</span>
                        {/if}
                    </td>
                    <td class="modal--col-quantity" data-label="{s name="Quantity"}Quantity{/s}" class="line-item--column-quantity">
                        {if $itemEditable && $orderEditable}
                            <div data-display="edit-mode" class="is--hidden">
                                <input type="number" name="quantity" value="{$row->quantity}" data-b2b-form-input-holder="true" data-targetElement="quantityHidden-{$row->id}" min="{$row->minPurchase|default:1}" step="{$row->purchaseStep|default:1}" max="{$row->maxPurchase}">
                            </div>
                        {/if}

                        <div data-display="view-mode">
                            {$row->quantity}
                        </div>
                    </td>
                    <td data-label="{s name="UnitPrice"}Unit price{/s}">
                        {$row->amountNet|currency}
                        <br>
                        <small>{s name="withTax"}inkl. MwSt{/s}: {$row->amount|currency}</small>
                    </td>
                    <td class="modal--col-comment" data-label="{s name="Comment"}Comment{/s}" title="{$row->comment}">
                        {$row->comment|truncate:80}
                    </td>
                    <td class="modal--col-actions" data-label="{s name="Actions"}Actions{/s}">
                        {if $itemEditable && $orderEditable}
                            <button title="{s name="EditItem"}Edit Item{/s}" type="button" data-mode="edit" class="btn is--default {b2b_acl controller=b2borderlineitemreference action=updateLineItem}"><i class="icon--pencil"></i></button>
                            <form action="{url action=removeLineItem}" method="post" class="form--inline" data-ajax-panel-trigger-reload="order-grid,order-clearance-grid">
                                <input type="hidden" name="orderContextId" value="{$orderContext->id}">
                                <input type="hidden" name="lineItemId" value="{$row->id}">
                                <input type="hidden" name="confirmName" value="{$row->referenceNumber}">

                                <button title="{s name="DeleteItem"}Delete Item{/s}"
                                        type="submit"
                                        class="btn is--primary is--default no--edit component-action-delete {b2b_acl controller=b2borderlineitemreference action=removeLineItem}"
                                        data-confirm="true"
                                        data-confirm-url="{url controller="b2bconfirm" action="remove"}">
                                    <i class="icon--trash no--edit"></i>
                                </button>
                            </form>
                        {/if}
                    </td>
                </tr>
                {if $itemEditable && $orderEditable}
                    <tr class="is--hidden" data-display="edit-mode">
                        <td data-label="{s name="Comment"}Comment{/s}" colspan="6" class="line-item--column-comment">
                            <form action="{url action=updateLineItem}" method="post" data-ajax-panel-trigger-reload="order-grid,order-clearance-grid,statistic-grid">

                                <input type="hidden" name="quantity" class="quantityHidden-{$row->id}" value="{$row->quantity}">
                                <input type="hidden" name="id" value="{$row->id}">
                                <input type="hidden" name="orderContextId" value="{$orderContext->id}">
                                <input type="hidden" name="mode" value="{$row->mode}">
                                <input type="hidden" name="stateId" value="{$stateId}">

                                <div class="comment--container">
                                    <input type="text" name="comment" placeholder="{s name='Comment'}Comment{/s}" value="{$row->comment}" autocomplete="off">
                                    <div class="comment--actions">
                                        <a href="{url orderContextId=$orderContext->id}" class="btn is--secondary is--cancel">
                                            {s name="Cancel"}Cancel{/s}
                                        </a>
                                        <button type="submit" class="btn is--primary is--save">
                                            {s name="Save"}Save{/s}

                                            <span class="hidden-"></span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </td>
                    </tr>
                {/if}
                <tr data-display="spacer-mode">
                    {*Empty row to prevent wrong color for the next row due to the open comment row *}
                </tr>
            {/foreach}
            </tbody>
        </table>
    {/if}
{/block}
