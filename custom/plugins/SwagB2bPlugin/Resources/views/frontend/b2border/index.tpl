{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="frontend/_base/index.tpl"}

{* B2b Account Main Content *}
{block name="frontend_index_content_b2b"}

    {* Active Order Container *}
    <div class="b2b--ajax-panel">
        <div class="panel has--border is--rounded">
            <div class="panel--title is--underline">
                <div class="block-group b2b--block-panel">
                    <div class="block block--title">
                        <h3>{s name="Orders"}Orders{/s}</h3>
                    </div>
                </div>
            </div>
            <div class="panel--body is--wide">
                <div class="b2b--ajax-panel" data-id="order-grid" data-url="{url action=grid}" data-plugins="b2bGridComponent"></div>
            </div>
        </div>
    </div>

    {* Order Clearance Container *}
    <div class="b2b--ajax-panel">
        <div class="panel has--border is--rounded">
            <div class="panel--title is--underline">
                <div class="block-group b2b--block-panel">
                    <div class="block block--title">
                        <h3>{s name="OrderClearances"}Order clearances{/s}</h3>
                    </div>
                </div>
            </div>
            <div class="panel--body is--wide">
                <div class="b2b--ajax-panel" data-id="order-clearance-grid" data-url="{url controller=b2borderclearance action=grid}" data-plugins="b2bGridComponent"></div>
            </div>
        </div>
    </div>

    {* Order Modal Container *}
    <div class="is--b2b-ajax-panel b2b--ajax-panel b2b-modal-panel" data-id="order-detail"></div>

    {* Orderlist Modal Container *}
    <div class="is--b2b-ajax-panel b2b--ajax-panel b2b-modal-panel" data-id="orderlist-detail"></div>
{/block}
