{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_grid/grid.tpl"}

{block name="b2b_grid_form"}
    {include file="frontend/b2bcompany/_filter.tpl"}
{/block}

{block name="b2b_grid_col_sort"}
    <option value="firstname::asc"{if $gridState.sortBy == 'firstname::asc'} selected="selected"{/if}>{s name="FirstNameAsc"}Firstname ascending{/s}</option>
    <option value="firstname::desc"{if $gridState.sortBy == 'firstname::desc'} selected="selected"{/if}>{s name="FirstNameDesc"}Firstname descending{/s}</option>
    <option value="lastname::asc"{if $gridState.sortBy == 'lastname::asc'} selected="selected"{/if}>{s name="LastNameAsc"}Lastname ascending{/s}</option>
    <option value="lastname::desc"{if $gridState.sortBy == 'lastname::desc'} selected="selected"{/if}>{s name="LastNameDesc"}Lastname descending{/s}</option>
    <option value="email::asc"{if $gridState.sortBy == 'email::asc'} selected="selected"{/if}>{s name="EmailAsc"}E-Mail ascending{/s}</option>
    <option value="email::desc"{if $gridState.sortBy == 'email::desc'} selected="selected"{/if}>{s name="EmailDesc"}E-Mail descending{/s}</option>
    <option value="active::asc"{if $gridState.sortBy == 'active::asc'} selected="selected"{/if}>{s name="StateAsc"}State ascending{/s}</option>
    <option value="active::desc"{if $gridState.sortBy == 'active::desc'} selected="selected"{/if}>{s name="StateDesc"}State descending{/s}</option>
{/block}

{block name="b2b_grid_table_head"}
    <tr>
        <th>{s name="FirstName"}Firstname{/s}</th>
        <th>{s name="SurName"}Surname{/s}</th>
        <th>{s name="Email"}E-Mail{/s}</th>
        <th class="is--align-center">{s name="State"}State{/s}</th>
        <th class="is--align-right">{s name="Actions"}Actions{/s}</th>
    </tr>
{/block}

{block name="b2b_grid_table_row"}
    <tr class="ajax-panel-link {b2b_acl controller=b2bcontact action=detail}" data-target="contact-detail" data-row-id="{$row->id}" data-href="{url action=detail id=$row->id}">
        <td data-label="{s name="FirstName"}Firstname{/s}">{$row->firstName}</td>
        <td data-label="{s name="SurName"}Surname{/s}">{$row->lastName}</td>
        <td data-label="{s name="Email"}E-Mail{/s}">{$row->email}</td>
        <td data-label="{s name="State"}State{/s}" class="col-status">
            {if $row->active}
                <i class="icon--record color--active" title="{s name="Active"}Active{/s}"></i>
            {else}
                <i class="icon--record color--inactive" title="{s name="Disabled"}Disabled{/s}"></i>
            {/if}
        </td>
        <td data-label="{s name="Actions"}Actions{/s}" class="col-actions">
            <button title="{s name="EditContact"}Edit contact{/s}" type="button" class="btn btn--edit is--small"><i class="icon--pencil"></i></button>

            <form action="{url action=remove}" method="post" class="form--inline">
                <input type="hidden" name="id" value="{$row->id}">
                <input type="hidden" name="confirmName" value="{$row->firstName} {$row->lastName}">

                <button title="{s name="DeleteContact"}Delete Contact{/s}" type="submit" class="btn is--small component-action-delete {b2b_acl controller=b2bcontact action=remove}"
                        data-confirm="true"
                        data-confirm-url="{url controller="b2bconfirm" action="remove"}">
                    <i class="icon--trash"></i>
                </button>
            </form>
        </td>
    </tr>
{/block}