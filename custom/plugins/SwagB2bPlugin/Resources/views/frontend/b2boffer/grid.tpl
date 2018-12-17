{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_grid/grid.tpl"}

{block name="b2b_grid"}
    {if $sendToAdminMessage}
        {include file="frontend/_includes/messages.tpl" type="success" content='Offer request send to admin'|snippet:'SendToAdminMessage':'frontend/plugins/b2b_debtor_plugin'}
    {/if}
    {$smarty.block.parent}
{/block}

{block name="b2b_grid_message_no_search_result"}
    {include file="frontend/_includes/messages.tpl" type="info" content="{s name="EmptySearchList"}There are no search results found for:{/s} \"{$gridState.searchTerm}\""}
{/block}
{block name="b2b_grid_message_grid_empty"}
    {include file="frontend/_includes/messages.tpl" type="info" content="{s name="EmptyResult"}There are no results yet.{/s}"}
{/block}

{block name="b2b_grid_col_sort"}
    <option value="created_at::asc"{if $gridState.sortBy === 'created_at::asc'} selected="selected"{/if}>{s name="CreationDateAsc"}Creation Date Ascending{/s}</option>
    <option value="created_at::desc"{if $gridState.sortBy === 'created_at::desc'} selected="selected"{/if}>{s name="CreationDateDesc"}Creation Date Descending{/s}</option>
    <option value="expired_at::asc"{if $gridState.sortBy === 'expired_at::asc'} selected="selected"{/if}>{s name="ExpiredDateAsc"}Expired Date Ascending{/s}</option>
    <option value="expired_at::desc"{if $gridState.sortBy === 'expired_at::desc'} selected="selected"{/if}>{s name="ExpiredDateDesc"}Expired Date Descending{/s}</option>
    <option value="discount_amount::asc"{if $gridState.sortBy === 'discount_amount::asc'} selected="selected"{/if}>{s name="DiscountAsc"}Discount Ascending{/s}</option>
    <option value="discount_amount::desc"{if $gridState.sortBy === 'discount_amount::desc'} selected="selected"{/if}>{s name="DiscountDesc"}Discount Descending{/s}</option>
{/block}

{block name="b2b_grid_table_head"}
    <tr>
        <th>{s name="Customer"}Customer{/s}</th>
        <th>{s name="Status"}Status{/s}</th>
        <th>{s name="CreatedDate"}Creation date{/s}</th>
        <th>{s name="ExpiredDate"}Expiration date{/s}</th>
        <th>{s name="DiscountAmountNet"}Discount amount without tax{/s}</th>
        <th>{s name="Actions"}Actions{/s}</th>
    </tr>
{/block}

{block name="b2b_grid_table_row"}
    <tr class="ajax-panel-link {b2b_acl controller=b2boffer action=detail}" data-target="offer-detail" data-row-id="{$row->id}" data-href="{url action=detail offerId=$row->id}">
        <td data-label="{s name="Name"}Name{/s}">{$row->email}</td>

        <td data-label="{s name="Status"}Status{/s}">{$row->status|snippet:$row->status:"frontend/plugins/b2b_debtor_plugin"}</td>
        <td data-label="{s name="Creation"}Creation{/s}">{$row->createdAt|date:'DATE_LONG'}</td>
        <td data-label="{s name="Expired"}Expired{/s}">{$row->expiredAt|date:'DATE_LONG'}</td>
        <td data-label="{s name="DiscountPrice"}Discount Price{/s}">{$row->discountAmountNet|currency}</td>
        <td data-label="{s name="Actions"}Actions{/s}" class="col-actions">
            <button title="{s name="EditOffer"}Edit offer{/s}" type="button" class="btn btn--edit is--small"><i class="icon--pencil"></i></button>

            <form action="{url action=remove}" method="post" class="form--inline">
                <input type="hidden" name="id" value="{$row->id}">

                <button title="{s name="DeleteOffer"}Delete offer{/s}" type="submit" class="btn is--small component-action-delete {b2b_acl controller=b2bofferlineitemreference action=remove}"
                        data-confirm="true"
                        data-confirm-url="{url controller="b2bconfirm" action="remove"}">
                    <i class="icon--trash"></i>
                </button>
            </form>
        </td>
    </tr>
{/block}