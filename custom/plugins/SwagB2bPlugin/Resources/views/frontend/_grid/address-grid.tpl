{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_grid/grid.tpl"}

{block name="b2b_grid_form"}
    {include file="frontend/b2bcompany/_filter.tpl" hide=true}
{/block}

{block name="b2b_grid_col_sort"}
    <option value="company::asc"{if $gridState.sortBy == 'company::asc'} selected="selected"{/if}>{s name="CompanyAsc"}Company ascending{/s}</option>
    <option value="company::desc"{if $gridState.sortBy == 'company::desc'} selected="selected"{/if}>{s name="CompanyDesc"}Company descending{/s}</option>
    <option value="street::asc"{if $gridState.sortBy == 'street::asc'} selected="selected"{/if}>{s name="StreetAsc"}Street ascending{/s}</option>
    <option value="street::desc"{if $gridState.sortBy == 'street::desc'} selected="selected"{/if}>{s name="StreetDesc"}Street descending{/s}</option>
    <option value="zipcode::asc"{if $gridState.sortBy == 'zipcode::asc'} selected="selected"{/if}>{s name="PostcodeAsc"}Postcode ascending{/s}</option>
    <option value="zipcode::desc"{if $gridState.sortBy == 'zipcode::desc'} selected="selected"{/if}>{s name="PostcodeDesc"}Postcode descending{/s}</option>
    <option value="city::asc"{if $gridState.sortBy == 'city::asc'} selected="selected"{/if}>{s name="CityAsc"}City ascending{/s}</option>
    <option value="city::desc"{if $gridState.sortBy == 'city::desc'} selected="selected"{/if}>{s name="CityDesc"}City descending{/s}</option>
{/block}

{block name="b2b_grid_table_head"}
    <tr>
        <th>{s name="Company"}Company{/s}</th>
        <th>{s name="Street"}Street{/s}</th>
        <th>{s name="Postcode"}Postcode{/s}</th>
        <th>{s name="City"}City{/s}</th>
        <th>{s name="Actions"}Actions{/s}</th>
    </tr>
{/block}

{block name="b2b_grid_table_row"}
    <tr data-row-id="{$row->id}" data-href="{url action=detail id=$row->id}" data-target="address-detail" class="ajax-panel-link {b2b_acl controller=b2baddress action=detail}">
        <td data-label="{s name="Company"}Company{/s}">{$row->company}</td>
        <td data-label="{s name="Street"}Street{/s}">{$row->street}</td>
        <td data-label="{s name="Postcode"}Postcode{/s}">{$row->zipcode}</td>
        <td data-label="{s name="City"}City{/s}">{$row->city}</td>
        <td data-label="{s name="Actions"}Actions{/s}" class="col-actions">
            <button title="{s name="EditAddress"}Edit Address{/s}" type="button" class="btn btn--edit is--small"><i class="icon--pencil"></i></button>

            <form action="{url action=remove type=$type}" method="post" class="form--inline">
                <input type="hidden" name="id" value="{$row->id}">
                {if !$row->is_used}
                    <button title="{s name="DeleteAddress"}Delete Address{/s}" type="submit" class="btn is--small component-action-delete {b2b_acl controller=b2baddress action=remove}"
                            data-confirm="true"
                            data-confirm-url="{url controller="b2bconfirm" action="remove"}">
                        <i class="icon--trash"></i>
                    </button>
                {/if}
            </form>
        </td>
    </tr>
{/block}
