{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_grid/order-grid.tpl"}

{block name="b2b_grid_col_sort"}
    <option value="ordernumber::asc"{if $gridState.sortBy == 'ordernumber::asc'} selected="selected"{/if}>{s name="OrdernumberAsc"}Ordernumber ascending{/s}</option>
    <option value="ordernumber::desc"{if $gridState.sortBy == 'ordernumber::desc'} selected="selected"{/if}>{s name="OrdernumberDesc"}Ordernumber descending{/s}</option>
    <option value="created_at::asc"{if $gridState.sortBy == 'created_at::asc'} selected="selected"{/if}>{s name="DateAsc"}Date ascending{/s}</option>
    <option value="created_at::desc"{if $gridState.sortBy == 'created_at::desc'} selected="selected"{/if}>{s name="DateDesc"}Date descending{/s}</option>
{/block}

{block name="b2b_grid_table_head"}
    <tr>
        <th>{s name="Date"}Date{/s}</th>
        <th>{s name="Contact"}Contact{/s}</th>
        <th>{s name="OrderAmount"}Order Amount{/s}</th>
        <th>{s name="OrderReferenceNumberShort"}Reference{/s}</th>
        <th>{s name="State"}State{/s}</th>
        <th width="15%">{s name="Actions"}Actions{/s}</th>
    </tr>
{/block}

{block name="b2b_grid_table_row"}
    <tr data-row-id="{$row->id}" class="ajax-panel-link {b2b_acl controller=b2borderclearance action=detail}" data-target="order-detail" data-href="{url action=detail orderContextId=$row->id}">
        <td class="is--align-center">{$row->createdAt|date_format:'d.m.Y H:i'}</td>
        <td class="is--align-center">
            {if $row->userPostalSettings}
                {$row->userPostalSettings->email}
            {else}
                {s name="ContactNotFound"}No contact found{/s}
            {/if}
        </td>
        <td class="is--align-right">
            {$row->list->amountNet|currency}
            <br>
            <small>{s name="withTax"}with Tax{/s}: {$row->list->amount|currency}</small>
        </td>
        <td class="is--align-center">{$row->orderReference}</td>
        <td class="is--align-center">
            {$row->status|snippet:{$row->status}:"backend/static/order_status"}
        </td>
        <td class="col-actions wide">
            <button title="{s name="EditOrderClearance"}Edit order clearance{/s}" type="button" class="btn btn--edit is--small"><i class="icon--pencil"></i></button>

            <form action="{url action=accept orderContextId=$row->id}" method="post" class="form--inline ignore--b2b-ajax-panel {b2b_acl controller=b2borderclearance action=accept}">
                <button type="submit"
                        class="btn is--primary is--small"
                        title="{s name="OrderAccept"}Accept Order{/s}"
                        {if !$row->isClearable}disabled{/if}
                >
                    <i class="icon--check"></i>
                </button>
            </form>

            <button class="btn is--small ajax-panel-link {b2b_acl controller=b2borderclearance action=decline}"
                    title="{s name="OrderDecline"}Decline Order{/s}"
                    data-target="order-detail"
                    data-href="{url action=decline orderContextId=$row->id}"
            >
                <i class="icon--cross"></i>
            </button>


            <form action="{url action=remove}" method="post" class="form--inline {b2b_acl controller=b2borderclearance action=remove}" data-ajax-panel-trigger-reload="order-grid">
                <input type="hidden" name="orderNumber" value="{$row->orderNumber}">
                <input type="hidden" name="orderContextId" value="{$row->id}">
                <input type="hidden" name="confirmName" value="{$row->orderNumber}">
                <button type="submit" class="btn is--small component-action-delete {b2b_acl controller=b2borderclearance action=remove}" title="{s name="OrderDelete"}Delete Order{/s}"
                        data-confirm="true"
                        data-confirm-url="{url controller="b2bconfirm" action="remove"}">
                    <i class="icon--trash"></i>
                </button>
            </form>
        </td>
    </tr>
{/block}