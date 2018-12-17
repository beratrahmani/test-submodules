{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.navigation = true}

    {$modalSettings.actions = true}
    {$modalSettings.bottom = true}

    {if !$gridState.data|count}
        {$modalSettings.bottom = false}
    {/if}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="OrderListManagement"}Order list management{/s}
{/block}

{* Modal Content: Grid Component: Actions *}
{block name="b2b_modal_base_content_inner_scrollable_inner_actions_inner"}
    <form method="get" class="form--inline" action="{url}">
        <input type="hidden" name="contactId" value="{$contact->id}"/>

        <div class="action--sort">
            <div class="select-field">
                <select name="sort-by" class="is--auto-submit">
                    <option value="" selected="selected">
                        {s name="SortBy"}Sort by{/s}
                    </option>
                    <option value="name::asc"{if $gridState.sortBy == 'name::asc'} selected="selected"{/if}>{s name="NameAsc"}Name ascending{/s}</option>
                    <option value="name::desc"{if $gridState.sortBy == 'name::desc'} selected="selected"{/if}>{s name="NameDesc"}Name descending{/s}</option>
                </select>
            </div>
        </div>
        <div class="action--search">
            <div class="search--area">
                <input type="hidden" name="filters[all][field-name]" value="_all_">
                <input type="hidden" name="filters[all][type]" value="like">
                <input type="text"
                       name="filters[all][value]"
                       value="{$gridState.searchTerm}"
                       placeholder="{s name="Search"}Search{/s}...">

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
        {include file="frontend/_includes/messages.tpl" type="info" content="{s name="EmptyAssignmentContingentList"}There are no order lists yet. You can create them in the order list managmenet panel.{/s}"}
    {/if}

    {if $gridState.data|count}
        <div class="panel--tr row--orderlist-selection">
            <div class="panel--th panel--icon" title="{s name="IsAllowedToUse"}Is allowed to do{/s}"><i class="icon--check"></i></div>
            <div class="panel--th panel--icon" title="{s name="CanGrantToOther"}Can grant to other{/s}"><i class="icon--forward"></i></div>
            <div class="panel--th panel--label-name">{s name="Name"}Name{/s}</div>
            <div class="panel--th panel--label-description">{s name="Description"}Description{/s}</div>
        </div>
        {foreach $gridState.data as $row}
            <div class="panel--tr row--orderlist-selection" data-row-id="{$row->id}">
                <form action="{url action=assign}" method="post" class="b2b--assignment-form ignore--b2b-ajax-panel {b2b_acl controller=b2bcontactorderlist action=assign}">
                    <input type="hidden" name="orderListId" value="{$row->id}"/>
                    <input type="hidden" name="contactId" value="{$contact->id}"/>
                    <div class="panel--td panel--icon">
                    <span class="checkbox">
                    <input type="checkbox"
                           name="allow"
                           value="1"
                           {if $row->foreignAllowed}checked="checked"{/if}
                            {if !$row->ownerGrantable}disabled{/if}
                           class="assign--allow is--auto-submit"
                    />
                    <span class="checkbox--state"></span>
                </span>
                    </div>
                    <div class="panel--td panel--col-checkbox">
                <span class="checkbox">
                    <input type="checkbox"
                           name="grantable"
                           value="1"
                           {if $row->foreignGrantable}checked="checked"{/if}
                            {if !$row->ownerGrantable}disabled{/if}
                           class="assign--grantable is--auto-submit"
                    />
                    <span class="checkbox--state"></span>
                </span>
                    </div>
                </form>
                <div class="panel--td panel--label-name">{$row->name}</div>
                <div class="panel--td panel--label-description">{$row->description}</div>
            </div>
        {/foreach}
    {/if}
{/block}

{* Modal Content: Grid Component: Bottom *}
{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    {if $gridState.data|count}
        <form method="get" class="form--inline" action="{url}">
            <input type="hidden" name="contactId" value="{$contact->id}"/>

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
                                <option value="{$i}"{if $i == $gridState.currentPage } selected="selected"{/if}>{$i}</option>
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

        </form>
    {/if}
{/block}