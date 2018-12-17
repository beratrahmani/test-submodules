{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal.tpl"}

{block name="b2b_modal_base_navigation_header"}
    {s name="EditOrderList"}Edit order list{/s}
{/block}

{block name="b2b_modal_base_navigation_entries"}
    <li>
        <a class="b2b--tab-link tab--active {b2b_acl controller=b2borderlist action=edit}"
           data-target="orderlist-tab-content"
           data-href="{url action=edit orderlist=$orderList->id}">
            {s name="MasterData"}Master Data{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2borderlistlineitemreference action=index}"
           data-target="orderlist-tab-content"
           data-href="{url controller=b2borderlistlineitemreference action=index orderlist=$orderList->id}">
            {s name="Positions"}Positions{/s}
        </a>
    </li>
{/block}

{block name="b2b_modal_base_content_inner"}
    <div class="b2b--ajax-panel"
         data-id="orderlist-tab-content"
         data-url="{url action=edit orderlist=$orderList->id}"
         data-plugins="b2bGridComponent,b2bAjaxProductSearch"
    ></div>
{/block}
