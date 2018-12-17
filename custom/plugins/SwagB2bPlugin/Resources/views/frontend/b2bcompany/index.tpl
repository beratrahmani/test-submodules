{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="frontend/_base/index.tpl"}

{* B2b Account Main Content *}
{block name="frontend_index_content_b2b"}

    <div class="panel b2b--main-panel">
        <div class="panel--body is--wide company-container" style="padding:0">

            {* Role Tree Select *}
            <div class="block b2b--ajax-panel role-block" data-url="{url controller=b2brole}" data-plugins="b2bTreeSelect">
                &nbsp;
            </div>

            {* Tab Container *}
            <div class="b2b--tab-menu js--tab-menu tab-block" data-default-tab-url="{url action=defaultTab}">

                {if {b2b_acl_check controller=b2bcontact         action=index}
                 || {b2b_acl_check controller=b2baddress         action=billing}
                 || {b2b_acl_check controller=b2baddress         action=shipping}
                 || {b2b_acl_check controller=b2bbudget          action=index}
                 || {b2b_acl_check controller=b2bcontingentgroup action=index}
                }
                    <div class="tab--navigation">
                        {block name="b2b_company_navigation_contact_entry"}
                            {include
                                file="frontend/b2bcompany/_tab.tpl"
                                controller=b2bcontact
                                action=index
                                snippetKey=Contacts
                                defaultTranslation="Contacts"
                            }
                        {/block}
                        {block name="b2b_company_navigation_billing_address_entry"}
                            {include
                                file="frontend/b2bcompany/_tab.tpl"
                                controller=b2baddress
                                action=billing
                                snippetKey=BillingAddresses
                                defaultTranslation="Billing addresses"
                            }
                        {/block}
                        {block name="b2b_company_navigation_shipping_address_entry"}
                            {include
                                file="frontend/b2bcompany/_tab.tpl"
                                controller=b2baddress
                                action=shipping
                                snippetKey=ShippingAddresses
                                defaultTranslation="Shipping addresses"
                            }
                        {/block}
                        {block name="b2b_company_navigation_budget_entry"}
                            {include
                                file="frontend/b2bcompany/_tab.tpl"
                                controller=b2bbudget
                                action=index
                                snippetKey=Budgets
                                defaultTranslation="Budgets"
                            }
                        {/block}
                        {block name="b2b_company_navigation_contingent_entry"}
                            {include
                                file="frontend/b2bcompany/_tab.tpl"
                                controller=b2bcontingentgroup
                                action=index
                                snippetKey=Contingents
                                defaultTranslation="Contingents"
                            }
                        {/block}
                    </div>

                    <div class="tab--container-list">
                        <div class="tab--container b2b--ajax-panel b2b--sync-height" data-id="company-tab-panel" data-plugins="b2bGridComponent" data-sync-group="company">
                            {include file="frontend/b2bcompany/default_tab.tpl"}
                        </div>
                    </div>
                {else}
                    <div class="tab--container-list">
                        <div class="tab--container b2b--ajax-panel b2b--sync-height" data-id="company-tab-panel" data-sync-group="company">
                            {include file="frontend/_includes/messages.tpl" type="info" content="{s name="PleaseSelectARoleNoDetails"}Please select a role no details{/s}"}
                        </div>
                    </div>
                {/if}

            </div>

        </div>
    </div>

{/block}