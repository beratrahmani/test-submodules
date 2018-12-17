{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.navigation = true}

    {$modalSettings.actions = true}
    {$modalSettings.bottom = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {if $type === 'billing'}
        {s name="BillingAddresses"}Billing addresses{/s}
    {else}
        {s name="ShippingAddresses"}Shipping addresses{/s}
    {/if}
{/block}

{* Modal Content: Grid Component: Actions *}
{block name="b2b_modal_base_content_inner_scrollable_inner_actions_inner"}
    <form method="get" class="form--inline" action="{url}">
        <input type="hidden" name="contactId" value="{$contact->id}" />
        <input type="hidden" name="type" value="{$type}" />

        <div class="action--sort">
            <div class="select-field">
                <select name="sort-by" class="is--auto-submit">
                    <option value="" selected="selected">
                        {s name="SortBy"}Sort by{/s}
                    </option>
                    <option value="company::asc"{if $gridState.sortBy === 'company::asc'} selected="selected"{/if}>{s name="CompanyAsc"}Company ascending{/s}</option>
                    <option value="company::desc"{if $gridState.sortBy === 'company::desc'} selected="selected"{/if}>{s name="CompanyDesc"}Company descending{/s}</option>
                    <option value="street::asc"{if $gridState.sortBy === 'street::asc'} selected="selected"{/if}>{s name="StreetAsc"}Street ascending{/s}</option>
                    <option value="street::desc"{if $gridState.sortBy === 'street::desc'} selected="selected"{/if}>{s name="StreetDesc"}Street descending{/s}</option>
                    <option value="zipcode::asc"{if $gridState.sortBy === 'zipcode::asc'} selected="selected"{/if}>{s name="PostcodeAsc"}Postcode ascending{/s}</option>
                    <option value="zipcode::desc"{if $gridState.sortBy === 'zipcode::desc'} selected="selected"{/if}>{s name="PostcodeDesc"}Postcode descending{/s}</option>
                    <option value="city::asc"{if $gridState.sortBy === 'city::asc'} selected="selected"{/if}>{s name="CityAsc"}City ascending{/s}</option>
                    <option value="city::desc"{if $gridState.sortBy === 'city::desc'} selected="selected"{/if}>{s name="CityDesc"}City descending{/s}</option>
                </select>
            </div>
        </div>
        <div class="action--search">
            <div class="search--area">
                <input type="hidden" name="filters[all][field-name]" value="_all_">
                <input type="hidden" name="filters[all][type]" value="like">
                <input onchange="this.form.page.value=1" type="text" name="filters[all][value]"
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
        {include file="frontend/_includes/messages.tpl" type="info" content="{s name="EmptyList"}There are no results yet. You can create your first one by clicking the create button.{/s}"}
    {/if}

    {if $addresses}
        <div class="panel--tr row--default-address">
            <div class="panel--th panel--label-default-address">{s name="DefaultAddress"}Default{/s}</div>
            <div class="panel--th panel--label-company">{s name="Company"}Company{/s}</div>
            <div class="panel--th panel--label-street">{s name="Street"}Street{/s}</div>
            <div class="panel--th panel--label-zipcode">{s name="Postcode"}Postcode{/s}</div>
            <div class="panel--th panel--label-city">{s name="City"}City{/s}</div>
        </div>
        {foreach $addresses as $address}
            <form action="{url action=default}" method="post" class="b2b--assignment-form ignore--b2b-ajax-panel {b2b_acl controller=b2bcontactaddressdefault action=default}">
                <input type="hidden" name="contactId" value="{$contact->id}" />
                <input type="hidden" name="addressId" value="{$address->id}" />
                <input type="hidden" name="type" value="{$type}" />

                <div class="row--address row--default-address panel--tr">
                    <div class="panel--td panel--label-default-address">
                    <span class="radio">
                        <input type="radio"
                               name="is_default"
                               value="1"
                               class="is--auto-submit is--exclusive-selection"
                               {if ($type === 'billing' && $address->id === $contact->defaultBillingAddressId)
                               || ($type === 'shipping' && $address->id === $contact->defaultShippingAddressId)}
                                   checked="checked"
                               {/if}
                        />
                        <span class="radio--state"></span>
                    </span>
                    </div>
                    <div class="panel--td panel--label-company">{$address->company}</div>
                    <div class="panel--td panel--label-street">{$address->street}</div>
                    <div class="panel--td panel--label-zipcode">{$address->zipcode}</div>
                    <div class="panel--td panel--label-city">{$address->city}</div>
                </div>

            </form>
        {/foreach}
    {/if}

{/block}

{* Modal Content: Grid Component: Bottom *}
{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <form method="get" class="form--inline" action="{url}">
        <input type="hidden" name="contactId" value="{$contact->id}" />
        <input type="hidden" name="type" value="{$type}" />

        {if $gridState.data|count}
            <div class="is--b2b-component-pagination">
                <div class="bottom--page">
                    {s name="Page"}Page{/s} {$gridState.currentPage} / {$gridState.maxPage}
                </div>
                <div class="bottom--pagination--buttons">
                    <button class="btn is--large btn--next js--action-next" name="buttonNext" value="{$gridState.currentPage + 1}" {if $gridState.currentPage === $gridState.maxPage} disabled {/if}>
                        <i class="icon--arrow-right"></i>
                    </button>
                </div>

                <div class="bottom--pagination">
                    <div class="select-field">
                        <select name="page" class="is--auto-submit">
                            {for $i = 1 to $gridState.maxPage}
                                <option value="{$i}"{if $i === $gridState.currentPage } selected="selected"{/if}>{$i}</option>
                            {/for}
                        </select>
                    </div>
                </div>

                <div class="bottom--pagination--buttons">
                    <button class="btn is--large btn--previous js--action-previous" name="buttonPrevious" value="{$gridState.currentPage - 1}" {if $gridState.currentPage === 1} disabled {/if}>
                        <i class="icon--arrow-left"></i>
                    </button>
                </div>
            </div>
        {/if}
    </form>
{/block}