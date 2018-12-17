{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_grid/grid.tpl"}

{block name="b2b_grid_form"}
    {include file="frontend/b2bcompany/_filter.tpl" hide=true}
{/block}

{block name="b2b_grid_col_sort"}
    <option value="identifier::asc"{if $gridState.sortBy == 'identifier::asc'} selected="selected"{/if}>{s name="IdentifierAsc"}Identifier ascending{/s}</option>
    <option value="identifier::desc"{if $gridState.sortBy == 'identifier::desc'} selected="selected"{/if}>{s name="IdentifierDesc"}Identifier descending{/s}</option>
    <option value="name::asc"{if $gridState.sortBy == 'name::asc'} selected="selected"{/if}>{s name="NameAsc"}Name ascending{/s}</option>
    <option value="name::desc"{if $gridState.sortBy == 'name::desc'} selected="selected"{/if}>{s name="NameDesc"}Name descending{/s}</option>
{/block}

{block name="b2b_grid_table_head"}
    <tr>
        <th class="col-small">{s name="Identifier"}Identifier{/s}</th>
        <th>{s name="Name"}Name{/s}</th>
        <th>{s name="BudgetAmountUsed"}Used{/s}</th>
        <th>{s name="BudgetAmountAvailable"}Available{/s}</th>
        <th>{s name="BudgetCapacity"}Capacity{/s}</th>
        <th>{s name="Status"}Status{/s}</th>
        <th>{s name="Actions"}Actions{/s}</th>
    </tr>
{/block}

{block name="b2b_grid_table_row"}
    <tr class="ajax-panel-link {b2b_acl controller=b2bbudget action=detail}" data-target="budget-detail" data-row-id="{$row->id}" data-href="{url action=detail id=$row->id}">
        <td data-label="{s name="Identifier"}Identifier{/s}">{$row->identifier}</td>
        <td data-label="{s name="Name"}Name{/s}">{$row->name}</td>
        <td data-label="{s name="BudgetAmountUsed"}Used{/s}">{$row->currentStatus->usedBudget|currency}</td>
        <td data-label="{s name="BudgetAmountAvailable"}Available{/s}">{$row->currentStatus->availableBudget|currency}</td>
        <td data-label="{s name="BudgetCapacity"}Capacity{/s}">
            <div class="b2b-progress-bar">
                <span class="b2b-progress-value-text">{$row->currentStatus->percentage}%</span>
                <div class="b2b-progress-value" style="width: {$row->currentStatus->percentage}%;"></div>
            </div>
        </td>
        <td data-label="{s name="Status"}Status{/s}" class="col-status">
            {if $row->active}
                <i class="icon--record color--active" title="{s name="Active"}Active{/s}"></i>
            {else}
                <i class="icon--record color--inactive" title="{s name="Disabled"}Disabled{/s}"></i>
            {/if}
        </td>
        <td data-label="{s name="Actions"}Actions{/s}" class="col-actions">
            <button title="{s name="EditBudget"}Edit budget{/s}" type="button" class="btn btn--edit is--small"><i class="icon--pencil"></i></button>

            <form action="{url action=remove}" method="post" class="form--inline">
                <input type="hidden" name="id" value="{$row->id}">
                <input type="hidden" name="confirmName" value="{$row->name}">

                <button title="{s name="DeleteBudget"}Delete budget{/s}" type="submit" class="btn is--small component-action-delete {b2b_acl controller=b2bbudget action=remove}"
                        data-confirm="true"
                        data-confirm-url="{url controller="b2bconfirm" action="remove"}">
                    <i class="icon--trash"></i>
                </button>
            </form>
        </td>
    </tr>
{/block}