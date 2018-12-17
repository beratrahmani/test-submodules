{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="{b2b_acl controller=b2borderlist action=grid}">
    <div class="select-field">
        <select name="orderlist" class="b2b--orderlist-dropdown" data-action-create="{url controller=b2borderlist action=createAjax}" data-success="{s name="RemoteListFastOrderSuccess"}The products have been successfully added to list{/s}" data-new-placeholder="{s name="OrderlistCreatePlaceholder"}Name for the new order list...{/s}" data-new-error="{s name="OrderlistCreateError"}An error occurred while saving the order list.{/s}">
            <option disabled selected>{s name="AddItemToListHeadline"}Add item to order list…{/s}</option>#
            <optgroup label="{s name="OrderLists"}Order lists{/s}">
                {foreach $orderLists as $list}
                    <option value="{$list->id}" {if $list->id === $orderListId}selected="selected"{/if}>{$list->name}</option>
                {foreachelse}
                    <option value="">{s name="NoOrderListsAvailable"}No lists available{/s}</option>
                {/foreach}
            </optgroup>
            <optgroup label="{s name="Actions"}Actions{/s}" class="{b2b_acl controller=b2borderlist action=createAjax}">
                <option value="_new_">{s name="CreateOrderList"}Create order list{/s}</option>
            </optgroup>
        </select>
    </div>
</div>