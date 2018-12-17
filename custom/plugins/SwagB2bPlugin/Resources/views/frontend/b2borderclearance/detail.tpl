{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="b2b--modal">
    <div class="block-navigation modal--tabs">
        <ul>
            <li class="tab--header">{s name="EditOrderClearance"}Edit order clearance{/s}</li>
            <li>
                <a class="b2b--tab-link tab--active {b2b_acl controller=b2borderlineitemreference action=masterData}"
                   data-target="contact-tab-content"
                   data-href="{url controller=b2borderlineitemreference action=masterData orderContextId=$orderContext->id}"
                   title="{s name="MasterData"}Master Data{/s}"
                >
                    {s name="MasterData"}Master Data{/s}
                </a>
            </li>
            <li>
                <a class="b2b--tab-link {b2b_acl controller=b2borderlineitemreference action=list}"
                   data-target="contact-tab-content"
                   data-href="{url controller=b2borderlineitemreference action=list orderContextId=$orderContext->id}"
                   title="{s name="Positions"}Positions{/s}"
                >
                    {s name="Positions"}Positions{/s}
                </a>
            </li>
            <li>
                <a class="b2b--tab-link"
                   data-target="contact-tab-content"
                   data-href="{url controller=b2border action=log orderContextId=$orderContext->id}"
                   title="{s name="History"}History{/s}"
                >
                    {s name="History"}History{/s}
                </a>
            </li>
        </ul>
    </div>
    <div class="block-content has--navigation">
        <div title="{s name="Loading"}Loading{/s}" class="content--loading is--hidden">
            <i class="icon--loading-indicator"></i>
        </div>

        <div class="b2b--ajax-panel scrollable-grid"
             data-id="contact-tab-content"
             data-url="{url controller=b2borderlineitemreference action=masterData orderContextId=$orderContext->id}"
             data-plugins="b2bFormInputHolder,b2bGridComponent,b2bAjaxProductSearch"></div>
    </div>
</div>
