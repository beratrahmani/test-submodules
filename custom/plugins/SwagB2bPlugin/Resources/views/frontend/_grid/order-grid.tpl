{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_grid/grid.tpl"}

{block name="b2b_grid_message_grid_empty"}
    {include file="frontend/_includes/messages.tpl" type="info" content="{s name="NoResults"}There are no results yet.{/s}"}
{/block}

{block name="b2b_grid_col_sort"}
    <option value="ordernumber::asc"{if $gridState.sortBy == 'ordernumber::asc'} selected="selected"{/if}>{s name="OrdernumberAsc"}Ordernumber ascending{/s}</option>
    <option value="ordernumber::desc"{if $gridState.sortBy == 'ordernumber::desc'} selected="selected"{/if}>{s name="OrdernumberDesc"}Ordernumber descending{/s}</option>
    <option value="status::asc"{if $gridState.sortBy == 'status::asc'} selected="selected"{/if}>{s name="StateAsc"}State ascending{/s}</option>
    <option value="status::desc"{if $gridState.sortBy == 'status::desc'} selected="selected"{/if}>{s name="StateDesc"}State descending{/s}</option>
    <option value="created_at::asc"{if $gridState.sortBy == 'created_at::asc'} selected="selected"{/if}>{s name="DateAsc"}Date ascending{/s}</option>
    <option value="created_at::desc"{if $gridState.sortBy == 'created_at::desc'} selected="selected"{/if}>{s name="DateDesc"}Date descending{/s}</option>
{/block}

{block name="b2b_grid_table_head"}
    <tr>
        <th>{s name="Ordernumber"}Ordernumber{/s}</th>
        <th>{s name="OrderReferenceNumberShort"}Reference{/s}</th>
        <th>{s name="State"}State{/s}</th>
        <th>{s name="Date"}Date{/s}</th>
        <th>{s name="OrderAmount"}Order Amount{/s}</th>
        <th>{s name="Actions"}Actions{/s}</th>
    </tr>
{/block}

{block name="b2b_grid_table_row"}
    <tr data-row-id="{$row->id}" class="ajax-panel-link {b2b_acl controller=b2border action=detail}" data-target="order-detail" data-href="{url action=detail orderContextId=$row->id}">
        <td data-label="{s name="Ordernumber"}Ordernumber{/s}" class="is--align-center">
            {$row->orderNumber|default:'-'}
        </td>
        <td data-label="{s name="OrderReferenceNumberShort"}Reference{/s}" class="is--align-center">
            {$row->orderReference}
        </td>
        <td data-label="{s name="State"}State{/s}" class="is--align-center">
            {$row->status|snippet:{$row->status}:"backend/static/order_status"}
        </td>
        <td data-label="{s name="Date"}Date{/s}" class="is--align-center">
            {$row->createdAt|date_format:'d.m.Y H:i'}
        </td>
        <td data-label="{s name="OrderAmount"}Order Amount{/s}" class="is--align-right">
            {$row->list->amountNet|currency}
            <br>
            <small>{s name="withTax"}with Tax{/s}: {$row->list->amount|currency}</small>
        </td>
        <td data-label="{s name="Actions"}Actions{/s}" class="col-actions">
            <button type="button" title="{s name="CreateOrderListFromOrder"}Create orderlist{/s}" data-target="orderlist-detail" data-href="{url controller=b2borderorderlist action=createNewOrderList id=$row->id}" class="btn component-action-create ajax-panel-link {b2b_acl controller=b2borderorderlist action=createNewOrderList}">
                <i class="icon--add-to-list"></i>
            </button>
            <button title="{s name="OrderDetails"}Order details{/s}" type="button" class="btn btn--edit is--small"><i class="icon--pencil"></i></button>
        </td>
    </tr>
{/block}
