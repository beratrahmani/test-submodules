{namespace name=frontend/plugins/b2b_debtor_plugin}

<form method="get" class="form--inline" action="{url}">
    <input type="hidden" name="roleId" value="{$role->id}"/>

    <div class="inner--actions">
        <div class="action--sort">

            <div class="select-field" title="{s name="SortBy"}Sort by{/s}">
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

                <button type="submit" value="submit" class="button--submit btn is--primary is--center" title="{s name="Search"}Search{/s}">
                    <i class="icon--arrow-right"></i>
                </button>
            </div>

        </div>

    </div>
    <div class="inner--bottom">
        {if $gridState.data|count}
            <div class="bottom--page">
                {s name="Page"}Page{/s} {$gridState.currentPage} / {$gridState.maxPage}
            </div>
            <div class="bottom--pagination--buttons">
                <button class="btn is--large btn--next js--action-next" name="buttonNext" value="{$gridState.currentPage + 1}" {if $gridState.currentPage === $gridState.maxPage} disabled {/if}>
                    <i class="icon--arrow-right"></i>
                </button>
            </div>

            <div class="bottom--pagination">

                <div class="select-field" title="{s name="SelectPage"}Select the Page to show{/s}">
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
        {/if}
    </div>
</form>

<div class="inner--content">

    {if !$gridState.data|count && $gridState.searchTerm|strlen}
        {include file="frontend/_includes/messages.tpl" type="info" content="{s name="EmptySearchList"}There are no search results found for:{/s} \"{$gridState.searchTerm}\""}
    {elseif !$gridState.data|count}
        {include file="frontend/_includes/messages.tpl" type="info" content="{s name="EmptyAssignmentRoleList"}There are no roles yet. You can create them in the role managmenet panel.{/s}"}
    {/if}

    {if $gridState.data|count}
        <div class="panel--tr">
            <div class="panel--th panel--icon" title="{s name="IsAllowedToUse"}Is allowed to use{/s}"><i class="icon--check"></i></div>
            <div class="panel--th panel--icon" title="{s name="CanGrantToOther"}Can grant to other{/s}"><i class="icon--forward"></i></div>
            <div class="panel--th panel--label-name">{s name="Name"}Name{/s}</div>
            <div class="panel--th panel--label-description">{s name="Description"}Description{/s}</div>
        </div>
        {foreach $gridState.data as $row}
            <div class="panel--tr" data-row-id="{$row->id}">
                <form action="{url action=assign}" method="post" class="b2b--assignment-form ignore--b2b-ajax-panel {b2b_acl controller=b2broleorderlist action=assign}">
                    <input type="hidden" name="orderListId" value="{$row->id}"/>
                    <input type="hidden" name="roleId" value="{$role->id}"/>
                    <div class="panel--td panel--icon">
                        <span class="checkbox">
                        <input type="checkbox"
                               name="allow"
                               value="1"
                               {if $row->foreignAllowed}checked="checked"{/if}
                                {if !$row->ownerGrantable}disabled{/if}
                               class="assign--allow is--auto-submit"
                               title="{s name="IsAllowedToUse"}Is allowed to use{/s}"
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
                               title="{s name="CanGrantToOther"}Can grant to other{/s}"
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
</div>