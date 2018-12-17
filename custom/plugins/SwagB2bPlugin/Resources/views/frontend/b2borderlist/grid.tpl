{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="panel has--border is--rounded">
    <div class="panel--title is--underline">

        <div class="block-group b2b--block-panel">
            <div class="block block--title">
                {s name="OrderLists"}Order lists{/s}
            </div>
            <div class="block block--actions">

                <button type="button" data-href="{url action=new}" data-target="order-list-detail"  class="btn component-action-create ajax-panel-link {b2b_acl controller=b2borderlist action=new}">
                    {s name="CreateOrderList"}Create order list{/s}
                </button>

            </div>
        </div>
    </div>
    <div class="panel--body panel--order-list is--wide">
        {include file="frontend/b2borderlist/_grid.tpl"}
    </div>
</div>
