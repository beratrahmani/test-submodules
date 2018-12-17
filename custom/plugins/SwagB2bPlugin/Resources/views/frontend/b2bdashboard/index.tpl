{namespace name=frontend/plugins/b2b_debtor_plugin}
{extends file="frontend/_base/index.tpl"}

{* B2b Account Main Content *}
{block name="frontend_index_content_b2b"}
    <div class="b2b--dashboard">
        {if $emotion}
            <div class="content--emotions">
                <div class="emotion--wrapper"
                     data-controllerUrl="{url module=widgets controller=emotion action=index emotionId=$emotion->getId()}"
                     data-availableDevices="{$emotion->getDevice()}">
                </div>
            </div>
        {/if}

        {if $orderInformationMessages}
            {b2b_contingent_information information=$orderInformationMessages type='info'}
        {/if}

        {block name="b2b_dashboard_first_entry"}{/block}

        {block name="b2b_dashboard_headline"}
            <div class="dashboard--header">
                <h2>{s name="DashboardTeaser"}Welcome to your B2B Account{/s}</h2>
                <p>{s name="DashboardDescription"}Manage contacts, contingents or approve pending orders.{/s}</p>
            </div>
        {/block}

        <div class="block-group">
            {block name="b2b_dashboard_statistic_entry"}
                <a class="{b2b_acl controller=b2bstatistic action=index}" href="{url controller="b2bstatistic"}" title="{s name="Statistics"}Statistics{/s}">
                    <div class="block block--dash">
                        <div class="panel has--border is--rounded">
                            <div title="{s name="Statistics"}Statistics{/s}" class="type--icon">
                                <i class="icon--graph"></i>
                            </div>
                            <div class="type--content">
                                <h3>{s name="Statistics"}Statistics{/s}</h3>
                                <p>
                                    {s name="StatisticsDescription"}Control and export turnovers from your organisation{/s}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            {/block}

            {block name="b2b_dashboard_company_entry"}
                <a class="{b2b_acl controller=b2bcompany action=index}" href="{url controller="b2bcompany"}" title="{s name="Company"}Company{/s}">
                    <div class="block block--dash">
                        <div class="panel has--border is--rounded">
                            <div title="{s name="Company"}Company{/s}" class="type--icon">
                                <i class="icon--house"></i>
                            </div>
                            <div class="type--content">
                                <h3>{s name="Company"}Company{/s}</h3>
                                <p>
                                    {s name="CompanyDescription"}Manage your whole organization.{/s}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            {/block}

            {block name="b2b_dashboard_order_entry"}
                <a class="{b2b_acl controller=b2border action=index}" href="{url controller="b2border"}" title="{s name="Orders"}Orders{/s}">
                    <div class="block block--dash">
                        <div class="panel has--border is--rounded">
                            <div title="{s name="Orders"}Orders{/s}" class="type--icon">
                                <i class="icon--basket"></i>
                            </div>
                            <div class="type--content">
                                <h3>{s name="Orders"}Orders{/s}</h3>
                                <p>
                                    {s name="OrdersDescription"}See current orders and approve temporary orders{/s}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            {/block}

            {block name="b2b_dashboard_orderlist_entry"}
                <a class="{b2b_acl controller=b2borderlist action=index}" href="{url controller="b2borderlist"}" title="{s name="OrderLists"}Order lists{/s}">
                    <div class="block block--dash">
                        <div class="panel has--border is--rounded">
                            <div title="{s name="OrderLists"}Order lists{/s}" class="type--icon">
                                <i class="icon--list2"></i>
                            </div>
                            <div class="type--content">
                                <h3>{s name="OrderLists"}Order lists{/s}</h3>
                                <p>
                                    {s name="OrderListsDescription"}Save often used products in order lists{/s}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            {/block}

            {block name="b2b_dashboard_fastorder_entry"}
                <a class="{b2b_acl controller=b2bfastorder action=index}" href="{url controller="b2bfastorder"}" title="{s name="FastOrder"}Fast order{/s}">
                    <div class="block block--dash">
                        <div class="panel has--border is--rounded">
                            <div title="{s name="FastOrder"}Fast order{/s}" class="type--icon">
                                <i class="icon--clock"></i>
                            </div>
                            <div class="type--content">
                                <h3>{s name="FastOrder"}Fast order{/s}</h3>
                                <p>
                                    {s name="FastOrderDescription"}Place orders by fast fill or with XLSX/CSV{/s}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            {/block}

            {block name="b2b_dashboard_offer_entry"}
                <a class="{b2b_acl controller=b2boffer action=index}" href="{url controller="b2boffer"}" title="{s name="Offers"}Offers{/s}">
                    <div class="block block--dash">
                        <div class="panel has--border is--rounded">
                            <div title="{s name="Offers"}Offers{/s}" class="type--icon">
                                <i class="icon--tag"></i>
                            </div>
                            <div class="type--content">
                                <h3>{s name="Offers"}Offers{/s}</h3>
                                <p>
                                    {s name="OfferDescription"}Offer management for existing offers{/s}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            {/block}

            {block name="b2b_dashboard_productnumber_entry"}
                <a class="{b2b_acl controller=b2bordernumber action=index}" href="{url controller="b2bordernumber"}" title="{s name="OrderNumbers"}Order numbers{/s}">
                    <div class="block block--dash">
                        <div class="panel has--border is--rounded">
                            <div title="{s name="Offers"}Offers{/s}" class="type--icon">
                                <i class="icon--flow-branch"></i>
                            </div>
                            <div class="type--content">
                                <h3>{s name="OrderNumbers"}Order numbers{/s}</h3>
                                <p>
                                    {s name="CustomOrderNumbersDescription"}Enter custom order numbers for products{/s}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            {/block}
        </div>

        {block name="b2b_dashboard_last_entry"}{/block}
    </div>
{/block}