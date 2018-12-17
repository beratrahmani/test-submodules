{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.actions = true}
    {$modalSettings.content.padding = false}
    {$modalSettings.bottom = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="SelectAddress"}Select address{/s}
{/block}

{block name="b2b_modal_base_content_inner_scrollable_inner_actions_inner"}
    <form method="get" class="form--inline" action="{url}">
        <input type="hidden" name="type" value="{$addressType}" />
        <input type="hidden" name="selectedId" value="{$selectedAddressId}" />

        <div class="action--sort">
            <div class="select-field">
                <select name="sort-by" class="is--auto-submit">
                    <option value="" selected="selected">
                        {s name="SortBy"}Sort by{/s}
                    </option>
                    <option value="company::asc"{if $gridState.sortBy == 'company::asc'} selected="selected"{/if}>{s name="CompanyAsc"}Company ascending{/s}</option>
                    <option value="company::desc"{if $gridState.sortBy == 'company::desc'} selected="selected"{/if}>{s name="CompanyDesc"}Company descending{/s}</option>
                    <option value="street::asc"{if $gridState.sortBy == 'street::asc'} selected="selected"{/if}>{s name="StreetAsc"}Street ascending{/s}</option>
                    <option value="street::desc"{if $gridState.sortBy == 'street::desc'} selected="selected"{/if}>{s name="StreetDesc"}Street descending{/s}</option>
                    <option value="zipcode::asc"{if $gridState.sortBy == 'zipcode::asc'} selected="selected"{/if}>{s name="PostcodeAsc"}Postcode ascending{/s}</option>
                    <option value="zipcode::desc"{if $gridState.sortBy == 'zipcode::desc'} selected="selected"{/if}>{s name="PostcodeDesc"}Postcode descending{/s}</option>
                    <option value="city::asc"{if $gridState.sortBy == 'city::asc'} selected="selected"{/if}>{s name="CityAsc"}City ascending{/s}</option>
                    <option value="city::desc"{if $gridState.sortBy == 'city::desc'} selected="selected"{/if}>{s name="CityDesc"}City descending{/s}</option>
                </select>
            </div>
        </div>
        <div class="action--search">
            <div class="search--area">
                <input type="hidden" name="filters[all][field-name]" value="_all_">
                <input type="hidden" name="filters[all][type]" value="like">
                <input type="text" name="filters[all][value]"
                       value="{$gridState.searchTerm}" placeholder="{s name="Search"}Search{/s}...">

                <button title="{s name="Search"}Search{/s}" type="submit" value="submit" class="button--submit btn is--primary is--center">
                    <i class="icon--arrow-right"></i>
                </button>
            </div>
        </div>
    </form>
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    {if !$gridState.data|count && $gridState.searchTerm|strlen}
        {include file="frontend/_includes/messages.tpl" type="info" content="{s name="EmptySearchList"}There are no search results found for:{/s} \"{$gridState.searchTerm}\""}
    {elseif !$gridState.data|count}
        {include file="frontend/_includes/messages.tpl" type="info" content="{s name="EmptyAddressListNoCreate"}There are no further addresses.{/s}"}
    {/if}

    {if $gridState.data|count}
        <table class="table--contacts b2b--component-grid" data-row-count="{$gridState.data|count}">
            <thead>
            <tr class="pointer--default">
                <th>{s name="Company"}Company{/s}</th>
                <th>{s name="Street"}Street{/s}</th>
                <th>{s name="Postcode"}Postcode{/s}</th>
                <th>{s name="City"}City{/s}</th>
            </tr>
            </thead>
            <tbody>
            {foreach $gridState.data as $row}
                <form class="b2b--address-select-{$row->id} b2b--close-modal" method="post" action="{url action=select type=$addressType addressId=$row->id}" data-ajax-panel-trigger-reload="_WINDOW_"></form>
                <tr data-linked-form="b2b--address-select-{$row->id}" class="is--auto-submit" data-row-id="{$row->id}">
                    <td>{$row->company}</td>
                    <td>{$row->street}</td>
                    <td>{$row->zipcode}</td>
                    <td>{$row->city}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    {/if}
{/block}

{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <form method="get" class="form--inline" action="{url}">
        <input type="hidden" name="type" value="{$addressType}" />
        <input type="hidden" name="selectedId" value="{$selectedAddressId}" />

        {if $gridState.data|count}
            <div class="bottom--page">
                {s name="Page"}Page{/s} {$gridState.currentPage} / {$gridState.maxPage}
            </div>
            <div class="bottom--pagination">
                <div class="select-field">
                    <select name="page" class="is--auto-submit">
                        {for $i = 1 to $gridState.maxPage}
                            <option value="{$i}"{if $i == $gridState.currentPage } selected="selected"{/if}>{$i}</option>
                        {/for}
                    </select>
                </div>
            </div>
        {/if}
    </form>
{/block}