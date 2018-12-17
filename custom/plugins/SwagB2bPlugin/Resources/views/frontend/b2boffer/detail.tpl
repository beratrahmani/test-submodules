{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal.tpl"}

{block name="b2b_modal_base_navigation_header"}
    {s name="Offer"}Offer{/s}
{/block}

{block name="b2b_modal_base_navigation_entries"}
    <li>
        <a class="b2b--tab-link tab--active {b2b_acl controller=b2boffer action=edit}"
           data-target="offer-tab-content"
           data-href="{url controller=b2boffer action=edit offerId=$offer->id}">
            {s name="MasterData"}Master data{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link {b2b_acl controller=b2bofferlineitemreference action=grid}"
           data-target="offer-tab-content"
           data-href="{url controller=b2bofferlineitemreference action=grid offerId=$offer->id}">
            {s name="Positions"}Positions{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link"
           data-target="offer-tab-content"
           data-plugins="b2bGridComponent"
           data-href="{url controller=b2bofferlog action=commentList orderContextId=$offer->orderContextId}">
            {s name="Comments"}Comments{/s}
        </a>
    </li>
    <li>
        <a class="b2b--tab-link"
           data-target="offer-tab-content"
           data-href="{url controller=b2bofferlog action=log orderContextId=$offer->orderContextId}">
            {s name="History"}History{/s}
        </a>
    </li>
{/block}

{block name="b2b_modal_base_content_inner"}
    <div class="b2b--ajax-panel"
         data-id="offer-tab-content"
         data-plugins="b2bAjaxProductSearch,b2bGridComponent"
         data-url="{url controller=b2boffer action=edit offerId=$offer->id}">
    </div>
{/block}