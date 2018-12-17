{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.navigation = true}

    {$modalSettings.actions = true}
    {$modalSettings.content.padding = false}
    {$modalSettings.bottom = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="ContactVisibility"}Contact visibility{/s}
{/block}

{* Modal Content: Grid Component: Actions *}
{block name="b2b_modal_base_content_inner_scrollable_inner_actions_inner"}
<form method="get" class="form--inline" action="{url}">
    <input type="hidden" name="roleId" value="{$role->id}" />

    <div class="action--sort">
        <div class="select-field">
            <select name="sort-by" class="is--auto-submit">
                <option value="" selected="selected">
                    {s name="SortBy"}Sort by{/s}
                </option>
                <option value="firstname::asc"{if $gridState.sortBy == 'firstname::asc'} selected="selected"{/if}>{s name="FirstNameAsc"}Firstname ascending{/s}</option>
                <option value="firstname::desc"{if $gridState.sortBy == 'firstname::desc'} selected="selected"{/if}>{s name="FirstNameDesc"}Firstname descending{/s}</option>
                <option value="lastname::asc"{if $gridState.sortBy == 'lastname::asc'} selected="selected"{/if}>{s name="LastNameAsc"}Lastname ascending{/s}</option>
                <option value="lastname::desc"{if $gridState.sortBy == 'lastname::desc'} selected="selected"{/if}>{s name="LastNameDesc"}Lastname descending{/s}</option>
                <option value="email::asc"{if $gridState.sortBy == 'email::asc'} selected="selected"{/if}>{s name="EmailAsc"}E-Mail ascending{/s}</option>
                <option value="email::desc"{if $gridState.sortBy == 'email::desc'} selected="selected"{/if}>{s name="EmailDesc"}E-Mail descending{/s}</option>
                <option value="active::asc"{if $gridState.sortBy == 'active::asc'} selected="selected"{/if}>{s name="StateAsc"}State ascending{/s}</option>
                <option value="active::desc"{if $gridState.sortBy == 'active::desc'} selected="selected"{/if}>{s name="StateDesc"}State descending{/s}</option>
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
        {include file="frontend/_includes/messages.tpl" type="info" content="{s name="EmptyAssignmentContactList"}There are no contacts yet. You can create them in the contact managmenet panel.{/s}"}
    {/if}

    {if $contacts}
        <div class="panel--tr row--contact-selection">
            <div class="panel--th panel--icon"><i class="icon--check" title="{s name="GrantRight"}Grant right{/s}"></i></div>
            <div class="panel--th panel--icon"><i class="icon--forward" title="{s name="InheritRight"}Inherit right{/s}"></i></div>
            <div class="panel--th panel--label-firstname">{s name="FirstName"}Firstname{/s}</div>
            <div class="panel--th panel--label-lastname">{s name="SurName"}Surname{/s}</div>
            <div class="panel--th panel--label-email">{s name="Email"}E-Mail{/s}</div>
        </div>
        {foreach $contacts as $contact}
            <form action="{url action=assign}" method="post" class="b2b--assignment-form {b2b_acl controller=b2brolecontactvisibility action=assign}">
                <input type="hidden" name="roleId" value="{$role->id}" />
                <input type="hidden" name="contactId" value="{$contact->id}" />
                <input type="hidden" name="email" value="{$contact->email}" />
                <input type="hidden" name="type" value="{$type}" />

                <div class="panel--tr row--contact-selection">
                    <div class="panel--td panel--icon">
                    <span class="checkbox">
                        <input type="checkbox"
                               name="allow"
                               value="1"
                               {if $contact->foreignAllowed}checked="checked"{/if}
                                {if !$contact->ownerGrantable}disabled{/if}
                               class="assign--allow is--auto-submit"
                        />
                        <span class="checkbox--state"></span>
                    </span>
                    </div>
                    <div class="panel--td panel--icon">
                        <span class="checkbox">
                            <input type="checkbox"
                                   name="grantable"
                                   value="1"
                                   {if $contact->foreignGrantable}checked="checked"{/if}
                                    {if !$contact->ownerGrantable}disabled{/if}
                                   class="assign--grantable is--auto-submit"
                            />
                            <span class="checkbox--state"></span>
                        </span>
                    </div>
                    <div class="panel--td panel--label-firstname">{$contact->firstName}</div>
                    <div class="panel--td panel--label-lastname">{$contact->lastName}</div>
                    <div class="panel--td panel--label-email">{$contact->email}</div>
                </div>
            </form>
        {/foreach}
    {/if}
{/block}

{* Modal Content: Grid Component: Bottom *}
{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <form method="get" class="form--inline" action="{url}">
        <input type="hidden" name="roleId" value="{$role->id}" />

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
        {/if}
    </form>
{/block}