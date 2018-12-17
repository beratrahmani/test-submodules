{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.navigation = true}

    {$modalSettings.actions = true}
    {$modalSettings.content.padding = false}
    {$modalSettings.bottom = false}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="Positions"}Positions{/s}
{/block}

{* Modal Content: Grid Component: Actions *}
{block name="b2b_modal_base_content_inner_scrollable_inner_actions_inner"}
    <button data-target="contingent-tab-content"
            data-href="{url action="new" orderlist=$orderList->id}"
            class="btn component-action-create ajax-panel-link {b2b_acl controller=b2borderlistlineitemreference action=new}">
        {s name="AddLineItem"}Add line item{/s}
    </button>
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    {foreach $errors as $error}
        <div class="modal--errors error--list">
            {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
        </div>
    {/foreach}

    <div class="panel has--border b2b-order-overview">
        <div class="block-group">
            <span class="item-count">
                <strong>{s name="OrderItemQuantityPlural"}Order item quantity{/s}:</strong><br>
                {$itemCount}
            </span>
            <span class="value">
                <strong>{s name="OrderAmount"}Order amount{/s}:</strong><br>
                {$amountNet|currency}
            </span>
            <span class="value">
                <strong>{s name="withTax"}with Tax{/s}:</strong><br>
                {$amount|currency}
            </span>
        </div>
    </div>

    {foreach $errors as $error}
        <div class="modal--errors error--list">
            {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
        </div>
    {/foreach}

    {if $gridState.data|count}
        <table class="table--contacts table--responsive b2b--component-grid" data-row-count="{$gridState.data|count}">
            <thead>
            <tr>
                <th>{s name="Product"}Product{/s}</th>
                <th>{s name="Quantity"}Quantity{/s}</th>
                <th>{s name="UnitPrice"}Unit price{/s}</th>
                <th>{s name="Comment"}Comment{/s}</th>
                <th class="modal--col-actions">{s name="Actions"}Actions{/s}</th>
            </tr>
            </thead>

            <tbody>
            {foreach $gridState.data as $row}
                <tr class="{b2b_acl controller=b2borderlistlineitemreference action=update}"
                    data-row-id="{$row->id}"
                    data-class="row"
                    data-mode="edit"
                >
                    <td data-label="{s name="Product"}Product{/s}">
                        {$row->name}
                        <br>
                        <small>{$row->referenceNumber}</small>
                    </td>
                    <td data-label="{s name="Quantity"}Quantity{/s}" class="line-item--column-quantity">
                        <div data-display="edit-mode" class="is--hidden">
                            <input type="number" min="{$row->minPurchase|default:1}" step="{$row->purchaseStep|default:1}" max="{$row->maxPurchase}" name="quantity" value="{$row->quantity}" data-b2b-form-input-holder="true" data-targetElement="quantityHidden-{$row->id}">
                        </div>

                        <div class="is--align-center" data-display="view-mode">
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
                        <button
                                title="{s name="EditItem"}Edit Item{/s}"
                                type="button"
                                data-mode="edit"
                                class="btn is--small {b2b_acl controller=b2borderlistlineitemreference action=update}"
                        >
                            <i class="icon--pencil"></i>
                        </button>
                        <form action="{url action=remove}" method="post" class="form--inline" data-ajax-panel-trigger-reload="order-list-grid,orderlist-tab-content">
                            <input type="hidden" name="orderlist" value="{$orderList->id}">
                            <input type="hidden" name="lineItemId" value="{$row->id}">
                            <input type="hidden" name="confirmName" value="{$row->referenceNumber}">

                            <button type="submit"
                                    title="{s name="DeleteItem"}Delete Item{/s}"
                                    class="btn no--edit is--small component-action-delete {b2b_acl controller=b2borderlistlineitemreference action=remove}"
                                    data-confirm="true"
                                    data-confirm-url="{url controller="b2bconfirm" action="remove"}">
                                <i class="icon--trash no--edit"></i>
                            </button>
                        </form>

                        <div class="button--group-split">

                            <form action="{url action=sort}" method="post" class="form--inline">
                                <input type="hidden" name="itemIdOne" value="{$row->id}">
                                <input type="hidden" name="itemIdTwo" value="{$row->previousItem}">
                                <input type="hidden" name="listId" value="{$listId}">
                                <input type="hidden" name="orderlist" value="{$orderList->id}">
                                <input type="hidden" name="direction" value="up">

                                <button type="submit" class="btn is--top"{if $row@first} disabled="disabled"{/if}>
                                    <i class="icon--arrow-up"></i>
                                </button>
                            </form>

                            <form action="{url action=sort}" method="post" class="form--inline">
                                <input type="hidden" name="itemIdOne" value="{$row->id}">
                                <input type="hidden" name="itemIdTwo" value="{$row->nextItem}">
                                <input type="hidden" name="listId" value="{$listId}">
                                <input type="hidden" name="orderlist" value="{$orderList->id}">
                                <input type="hidden" name="direction" value="down">

                                <button type="submit" class="btn is--bottom"{if $row@last} disabled="disabled"{/if}>
                                    <i class="icon--arrow-down"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="is--hidden" data-display="edit-mode">
                    <td colspan="6" class="line-item--column-comment" data-label="{s name="Comment"}Comment{/s}">
                        <form action="{url action=update}" method="post" data-ajax-panel-trigger-reload="order-list-grid">

                            <input type="hidden" name="quantity" class="quantityHidden-{$row->id}" value="{$row->quantity}">
                            <input type="hidden" name="id" value="{$row->id}">
                            <input type="hidden" name="listId" value="{$listId}">
                            <input type="hidden" name="orderlist" value="{$orderList->id}">

                            <div class="comment--container">
                                <input type="text" name="comment" placeholder="{s name="Comment"}Comment{/s}" value="{$row->comment}" autocomplete="off">
                                <div class="comment--actions">
                                    <a href="{url orderlist=$orderList->id}" class="btn is--secondary is--cancel">{s name="Cancel"}Cancel{/s}</a>
                                    <button type="submit" class="btn is--primary is--save">{s name="Save"}Save{/s}</button>
                                </div>
                            </div>
                        </form>
                    </td>
                </tr>
                <tr data-display="spacer-mode">
                    {*Empty row to prevent wrong color for the next row due to the open comment row *}
                </tr>
            {/foreach}
            </tbody>
        </table>
    {else}
        {block name="b2b_grid_message_grid_empty"}
            {include file="frontend/_includes/messages.tpl" type="info" content="{s name="EmptyOrderList"}There are no items yet. You can add one by clicking the add item button.{/s}"}
        {/block}
    {/if}

{/block}
