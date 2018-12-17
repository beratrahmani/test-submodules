{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_grid/grid.tpl"}

{block name="b2b_grid_col_sort"}
    <option value="name::asc"{if $gridState.sortBy == 'name::asc'} selected="selected"{/if}>{s name="NameAsc"}Name ascending{/s}</option>
    <option value="name::desc"{if $gridState.sortBy == 'name::desc'} selected="selected"{/if}>{s name="NameDesc"}Name descending{/s}</option>
{/block}

{block name="b2b_grid_table_head"}
    <tr>
        <th>{s name="Name"}Name{/s}</th>
        <th>{s name="Items"}Items{/s}</th>
        <th>{s name="Amount"}Amount{/s}</th>
        <th>{s name="Actions"}Actions{/s}</th>
    </tr>
{/block}

{block name="b2b_grid_table_row"}
    <tr data-row-id="{$row->id}" class="ajax-panel-link {b2b_acl controller=b2borderlist action=detail}" data-target="order-list-detail" data-href="{url action=detail orderlist=$row->id orderContextId=$row->orderContextId}">
        <td data-label="{s name="Name"}Name{/s}" class="is--align-left">{$row->name}</td>
        <td data-label="{s name="Items"}Items{/s}" class="is--align-center">{$row->lineItemList->references|count}</td>
        <td data-label="{s name="Amount"}Amount{/s}" class="is--align-right">
            {$row->lineItemList->amountNet|currency}
            <br>
            <small>{s name="withTax"}with taxes{/s}: {$row->lineItemList->amount|currency}</small>
        </td>
        <td data-label="{s name="Actions"}Actions{/s}" class="col-actions">
            <button title="{s name="EditOrderList"}Edit order list{/s}" type="button" class="btn btn--edit is--small {b2b_acl controller=b2borderlist action=detail}">
                <i class="icon--pencil"></i>
            </button>

            <form action="{url action=produceCart}" method="post" class="form--inline order-list--add-to-cart ignore--b2b-ajax-panel">
                <input type="hidden" name="orderlist" value="{$row->id}">

                <button title="{s name="AddOrderListToCart"}Add order list to cart{/s}" type="submit"
                        class="btn is--primary is--small component-action-create {b2b_acl controller=b2borderlist action=produceCart}{if $row->lineItemList->references|count === 0} is--hidden{/if}"
                >
                    <i class="icon--basket"></i>
                </button>
            </form>

            <form action="{url action=duplicate}" method="post" class="form--inline">
                <input type="hidden" name="id" value="{$row->id}">
                <button title="{s name="DuplicateList"}Duplicate list{/s}" type="submit" class="btn is--small component-action-duplicate {b2b_acl controller=b2borderlist action=duplicate}">
                    <i class="icon--docs"></i>
                </button>
            </form>

            <form action="{url action=remove}" method="post" class="form--inline">
                <input type="hidden" name="id" value="{$row->id}">
                <input type="hidden" name="confirmName" value="{$row->name}">

                <button type="submit"
                        title="{s name="DeleteOrderList"}Delete order list{/s}"
                        class="btn is--small component-action-delete {b2b_acl controller=b2borderlist action=remove}"
                        data-confirm="true"
                        data-confirm-url="{url controller="b2bconfirm" action="remove"}">
                    <i class="icon--trash"></i>
                </button>
            </form>
        </td>
    </tr>
{/block}