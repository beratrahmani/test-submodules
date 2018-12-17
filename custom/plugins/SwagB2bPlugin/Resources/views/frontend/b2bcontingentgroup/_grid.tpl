{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_grid/grid.tpl"}

{block name="b2b_grid_form"}
    {include file="frontend/b2bcompany/_filter.tpl"}
{/block}

{block name="b2b_grid_col_sort"}
    <option value="name::asc"{if $gridState.sortBy == 'name::asc'} selected="selected"{/if}>
        {s name="NameAsc"}Name ascending{/s}
    </option>
    <option value="name::desc"{if $gridState.sortBy == 'name::desc'} selected="selected"{/if}>
        {s name="NameDesc"}Name descending{/s}
    </option>
    <option value="description::asc"{if $gridState.sortBy == 'description::asc'} selected="selected"{/if}>
        {s name="DescriptionAsc"}Description ascending{/s}
    </option>
    <option value="description::desc"{if $gridState.sortBy == 'description::desc'} selected="selected"{/if}>
        {s name="DescriptionDesc"}Description descending{/s}
    </option>
{/block}

{block name="b2b_grid_table_head"}
    <tr>
        <th>{s name="ContingentGroupName"}Name{/s}</th>
        <th>{s name="ContingentGroupDescription"}Description{/s}</th>
        <th>{s name="Actions"}Actions{/s}</th>
    </tr>
{/block}

{block name="b2b_grid_table_row"}
    <tr data-row-id="{$row->id}" class="ajax-panel-link {b2b_acl controller=b2bcontingentgroup action=detail grantContext=$grantContext}" data-target="contingent-group-detail" data-href="{url action=detail id=$row->id}">
        <td data-label="{s name="ContingentGroupName"}Name{/s}">{$row->name}</td>
        <td data-label="{s name="ContingentGroupDescription"}Description{/s}">{$row->description}</td>
        <td data-label="{s name="Actions"}Actions{/s}" class="col-actions">
            <button title="{s name="EditContingentGroup"}Edit contingent group{/s}" type="button" class="btn btn--edit is--small"><i class="icon--pencil"></i></button>

            <form action="{url action=remove}" method="post" class="form--inline">
                <input type="hidden" name="id" value="{$row->id}">
                <input type="hidden" name="confirmName" value="{$row->name}">
                <button title="{s name="DeleteContingentGroup"}Delete ContingentGroup{/s}" type="submit" class="btn is--small component-action-delete {b2b_acl controller=b2bcontingentgroup action=remove}"
                        data-confirm="true"
                        data-confirm-url="{url controller="b2bconfirm" action="remove"}">
                    <i class="icon--trash"></i>
                </button>
            </form>
        </td>
    </tr>
{/block}
