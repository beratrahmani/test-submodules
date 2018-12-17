{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal.tpl"}

{block name="b2b_modal_base_navigation_header"}
    {s name="EditOrder"}Edit order{/s}
{/block}

{block name="b2b_modal_base_navigation_entries"}
    <li>
        <a class="b2b--tab-link tab--active {b2b_acl controller=b2borderlineitemreference action=masterData}"
           data-target="order-tab-content"
           data-href="{url controller=b2borderlineitemreference action=masterData orderContextId=$orderContext->id}"
           title="{s name="MasterData"}Master Data{/s}"
        >
            {s name="MasterData"}Master Data{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2borderlineitemreference action=list}"
           data-target="order-tab-content"
           data-href="{url controller=b2borderlineitemreference action=list orderContextId=$orderContext->id}"
           title="{s name="Positions"}Positions{/s}"
        >
            {s name="Positions"}Positions{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link"
           data-target="order-tab-content"
           data-href="{url action=log orderContextId=$orderContext->id}"
           title="{s name="History"}History{/s}"
        >
            {s name="History"}History{/s}
        </a>
    </li>
{/block}

{block name="b2b_modal_base_content_inner"}
    <div class="b2b--ajax-panel"
         data-id="order-tab-content"
         data-url="{url controller=b2borderlineitemreference action=masterData orderContextId=$orderContext->id}"
         data-plugins="b2bFormInputHolder,b2bGridComponent,b2bAjaxProductSearch"></div>
{/block}