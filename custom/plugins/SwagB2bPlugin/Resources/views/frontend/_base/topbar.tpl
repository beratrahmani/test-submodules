{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="b2b--navigation">
    {block name="b2b_base_topbar_navigation_submenu"}
        <ul class="navigation--submenu">
            <li class="has--padding">
                {action module=widgets controller=b2baccount}
            </li>
        </ul>
        <ul class="navigation--submenu submenu--right">
            <li class="is--account">
                <a title="{s name="MyAccount"}My account{/s}" href="{url controller='b2baccount'}">
                    {s name="MyAccount"}My account{/s}
                </a>
            </li>
            <li class="is--logout">
                <a title="{s name="Logout"}Logout{/s}" href="{url controller='account' action='logout'}">
                    <i class="icon--logout"></i> {s name="Logout"}Logout{/s}
                </a>
            </li>
        </ul>
    {/block}
    {block name="b2b_base_topbar_navigation_menu"}
        <div class="b2b--navigation-container" data-menu-scroller="true" data-listselector=".navigation--menu" data-viewportselector=".b2b--navigation-wrapper">
            <div class="b2b--navigation-wrapper">
                <ul class="navigation--menu">
                    {block name="b2b_base_topbar_first_entry"}{/block}

                    {if $isSalesRep}
                        {block name="b2b_base_topbar_sales_representative_client_overview"}
                            <li>
                                <a href="{url controller="b2bsalesrepresentative"}" title="{s name="ClientOverview"}Client Overview{/s}" class="navigation--link {b2b_acl controller=b2bsalesrepresentative action=index} {if $Controller == 'b2bsalesrepresentative'}is--active{/if}">
                                    {s name="ClientOverview"}Client Overview{/s}
                                </a>
                            </li>
                        {/block}
                    {else}
                        {block name="b2b_base_topbar_dashbard_entry"}
                            <li>
                                <a href="{url controller='b2bdashboard'}" title="{s name="Dashboard"}Dashboard{/s}" class="navigation--link {b2b_acl controller=b2bdashboard action=index} {if $Controller == 'b2bdashboard'}is--active{/if}">
                                    {s name="Dashboard"}Dashboard{/s}
                                </a>
                            </li>
                        {/block}
                        {block name="b2b_base_topbar_company_entry"}
                            <li>
                                <a href="{url controller='b2bcompany'}" title="{s name="Company"}Company{/s}" class="navigation--link {b2b_acl controller=b2bcompany action=index} {if $Controller == 'b2bcompany'}is--active{/if}">
                                    {s name="Company"}Company{/s}
                                </a>
                            </li>
                        {/block}
                        {block name="b2b_base_topbar_statistic_entry"}
                            <li>
                                <a href="{url controller='b2bstatistic'}" title="{s name="Statistics"}Statistics{/s}" class="navigation--link {b2b_acl controller=b2bstatistic action=index} {if $Controller == 'b2bstatistic'}is--active{/if}">
                                    {s name="Statistics"}Statistics{/s}
                                </a>
                            </li>
                        {/block}
                        {block name="b2b_base_topbar_order_entry"}
                            <li>
                                <a href="{url controller='b2border'}" title="{s name="Orders"}Orders{/s}" class="navigation--link {b2b_acl controller=b2border action=index} {if $Controller == 'b2border'}is--active{/if}">
                                    {s name="Orders"}Orders{/s}
                                </a>
                            </li>
                        {/block}
                        {block name="b2b_base_topbar_order_list_entry"}
                            <li>
                                <a href="{url controller='b2borderlist'}" title="{s name="OrderLists"}Order lists{/s}" class="navigation--link {b2b_acl controller=b2borderlist action=index} {if $Controller == 'b2borderlist'}is--active{/if}">
                                    {s name="OrderLists"}Order lists{/s}
                                </a>
                            </li>
                        {/block}
                        {block name="b2b_base_topbar_fast_order_entry"}
                            <li>
                                <a href="{url controller='b2bfastorder'}" title="{s name="FastOrder"}Fast order{/s}" class="navigation--link {b2b_acl controller=b2bfastorder action=index} {if $Controller == 'b2bfastorder'}is--active{/if}">
                                    {s name="FastOrder"}Fast order{/s}
                                </a>
                            </li>
                        {/block}
                        {block name="b2b_base_topbar_offer_list_entry"}
                            <li>
                                <a href="{url controller='b2boffer'}" title="{s name="Offers"}Offers{/s}" class="navigation--link {b2b_acl controller=b2boffer action=index} {if $Controller == 'b2boffer'}is--active{/if}">
                                    {s name="Offers"}Offers{/s}
                                </a>
                            </li>
                        {/block}
                        {block name="b2b_base_topbar_ordernumber_entry"}
                            <li>
                                <a href="{url controller='b2bordernumber'}" title="{s name="OrderNumbers"}Order numbers{/s}" class="navigation--link {b2b_acl controller=b2bordernumber action=index} {if $Controller == 'b2bordernumber'}is--active{/if}">
                                    {s name="OrderNumbers"}Order numbers{/s}
                                </a>
                            </li>
                        {/block}
                    {/if}

                    {block name="b2b_base_topbar_last_entry"}{/block}
                </ul>
            </div>
        </div>
    {/block}
</div>
