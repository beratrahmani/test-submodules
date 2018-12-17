{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal.tpl"}

{block name="b2b_modal_base_navigation_header"}
    {s name="ContingentGroupDetail"}Contingent Group detail{/s}
{/block}

{block name="b2b_modal_base_navigation_entries"}
    <li>
        <a class="b2b--tab-link tab--active {b2b_acl controller=b2bcontingentgroup action=edit}"
           data-target="contingent-tab-content"
           data-href="{url action=edit id=$id}"
           title="{s name="MasterData"}Master data{/s}"
        >
            {s name="MasterData"}Master data{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2bcontingentrule action=grid}"
           data-target="contingent-tab-content"
           data-href="{url controller=b2bcontingentrule action=grid id=$id}"
           title="{s name="ContingentRules"}Contingent rules{/s}"
        >
            {s name="ContingentRules"}Contingent rules{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2bcontingentrestriction action=grid}"
           data-target="contingent-tab-content"
           data-href="{url controller=b2bcontingentrestriction action=grid id=$id}"
           title="{s name="ContingentRestrictions"}Contingent Restrictions{/s}"
        >
            {s name="ContingentRestrictions"}Contingent Restrictions{/s}
        </a>
    </li>
{/block}

{block name="b2b_modal_base_content_inner"}
    <div class="b2b--ajax-panel" data-id="contingent-tab-content" data-plugins="b2bAjaxProductSearch,b2bGridComponent,b2bAssignmentGrid" data-url="{url action=edit id=$id}"></div>
{/block}