{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal.tpl"}

{block name="b2b_modal_base_navigation_header"}
    {s name="RoleDetail"}Role detail{/s}
{/block}

{block name="b2b_modal_base_navigation_entries"}
    <li>
        <a class="b2b--tab-link tab--active {b2b_acl controller=b2brole action=edit}"
           data-target="role-tab-content"
           data-href="{url action=edit id=$role->id}">
            {s name="MasterData"}Master data{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2broleroute action=index}"
           data-target="role-tab-content"
           data-href="{url controller=b2broleroute action=index roleId=$role->id}">
            {s name="PermissionManagement"}Permission management{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2brolecontactvisibility action=index}"
           data-target="role-tab-content"
           data-href="{url controller=b2brolecontactvisibility action=index roleId=$role->id}">
            {s name="ContactVisibility"}Contact Visibility{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2brolerolevisibility action=index}"
           data-target="role-tab-content"
           data-href="{url controller=b2brolerolevisibility action=index roleId=$role->id}">
            {s name="RoleVisibility"}Role Visibility{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2broleaddress action=grid}"
           data-target="role-tab-content"
           data-href="{url controller=b2broleaddress action=grid roleId=$role->id type=billing}">
            {s name="BillingAddresses"}Billing Addresses{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2broleaddress action=grid}"
           data-target="role-tab-content"
           data-href="{url controller=b2broleaddress action=grid roleId=$role->id type=shipping}">
            {s name="ShippingAddresses"}Shipping Addresses{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2brolecontingentgroup action=index}"
           data-target="role-tab-content"
           data-href="{url controller=b2brolecontingentgroup action=index roleId=$role->id}">
            {s name="ContingentManagement"}Contingent management{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2broleorderlist action=index}"
           data-target="role-tab-content"
           data-href="{url controller=b2broleorderlist action=index roleId=$role->id}">
            {s name="OrderListManagement"}Order list management{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2brolebudget action=index}"
           data-target="role-tab-content"
           data-href="{url controller=b2brolebudget action=index roleId=$role->id}">
            {s name="BudgetManagement"}Budget management{/s}
        </a>
    </li>
{/block}

{block name="b2b_modal_base_content_inner"}
    <div class="b2b--ajax-panel" data-id="role-tab-content" data-url="{url action=edit id=$role->id}" data-plugins="b2bAssignmentGrid,b2bGridComponent"></div>
{/block}