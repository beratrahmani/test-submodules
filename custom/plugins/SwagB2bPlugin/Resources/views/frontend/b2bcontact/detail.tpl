{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal.tpl"}

{block name="b2b_modal_base_navigation_header"}
    {s name="EditContact"}Edit Contact{/s}
{/block}

{block name="b2b_modal_base_navigation_entries"}
    <li class="tab--group">
        {s name="Account"}Account{/s}
    </li>
    <li>
        <a class="b2b--tab-link tab--active {b2b_acl controller=b2bcontact action=edit}"
           data-target="contact-tab-content"
           data-href="{url action=edit id=$contact->id}"
           title="{s name="MasterData"}Master Data{/s}"
        >
            {s name="MasterData"}Master Data{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2bcontactroute action=index}"
           data-target="contact-tab-content"
           data-href="{url controller=b2bcontactroute action=index contactId=$contact->id}"
           title="{s name="PermissionManagement"}Permission management{/s}"
        >
            {s name="PermissionManagement"}Permission management{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2bcontactrole action=index}"
           data-target="contact-tab-content"
           data-href="{url controller=b2bcontactrole action=index contactId=$contact->id}"
           title="{s name="RoleAssignment"}Role assignment{/s}"
        >
            {s name="RoleAssignment"}Role assignment{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2bcontactaddressdefault action=grid}"
           data-target="contact-tab-content"
           data-href="{url controller=b2bcontactaddressdefault action=grid contactId=$contact->id type=billing}"
           title="{s name="DefaultBillingAddress"}Default selection for billing address{/s}"
        >
            {s name="DefaultBillingAddress"}Default billing address{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2bcontactaddressdefault action=grid}"
           data-target="contact-tab-content"
           data-href="{url controller=b2bcontactaddressdefault action=grid contactId=$contact->id type=shipping}"
           title="{s name="DefaultShippingAddress"}Default selection for shipping address{/s}"
        >
            {s name="DefaultShippingAddress"}Default shipping address{/s}
        </a>
    </li>


    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2bcontactcontingent action=index}"
           data-target="contact-tab-content"
           data-href="{url controller=b2bcontactcontingent action=index contactId=$contact->id}"
           title="{s name="ContingentManagement"}Contingent management{/s}"
        >
            {s name="ContingentManagement"}Contingent management{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2bcontactbudget action=index}"
           data-target="contact-tab-content"
           data-href="{url controller=b2bcontactbudget action=index contactId=$contact->id}"
           title="{s name="BudgetManagement"}Budgets management{/s}"
        >
            {s name="BudgetManagement"}Budgets management{/s}
        </a>
    </li>

    <li class="tab--group">
        {s name="Visibility"}Visibility{/s}
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2bcontactaddress action=grid}"
           data-target="contact-tab-content"
           data-href="{url controller=b2bcontactaddress action=grid contactId=$contact->id type=billing}"
           title="{s name="BillingAddresses"}Billing addresses{/s}"
        >
            {s name="BillingAddresses"}Billing addresses{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2bcontactaddress action=grid}"
           data-target="contact-tab-content"
           data-href="{url controller=b2bcontactaddress action=grid contactId=$contact->id type=shipping}"
           title="{s name="ShippingAddresses"}Shipping addresses{/s}"
        >
            {s name="ShippingAddresses"}Shipping addresses{/s}
        </a>
    </li>

    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2bcontactcontactvisibility action=index}"
           data-target="contact-tab-content"
           data-href="{url controller=b2bcontactcontactvisibility action=index contactId=$contact->id}"
           title="{s name="ContactVisibility"}Contact visibility{/s}"
        >
            {s name="ContactVisibility"}Contact visibility{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2bcontactrolevisibility action=index}"
           data-target="contact-tab-content"
           data-href="{url controller=b2bcontactrolevisibility action=index contactId=$contact->id}"
           title="{s name="RoleVisibility"}Role visibility{/s}"
        >
            {s name="RoleVisibility"}Role visibility{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2bcontactorderlist action=index}"
           data-target="contact-tab-content"
           data-href="{url controller=b2bcontactorderlist action=index contactId=$contact->id}"
           title="{s name="OrderListManagement"}Order list management{/s}"
        >
            {s name="OrderListManagement"}Order list management{/s}
        </a>
    </li>

{/block}

{block name="b2b_modal_base_content_inner"}
    <div class="b2b--ajax-panel" data-id="contact-tab-content" data-url="{url action=edit id=$contact->id}" data-plugins="b2bAssignmentGrid,b2bAjaxPanelDefaultAddress,b2bGridComponent"></div>
{/block}