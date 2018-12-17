{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="panel is--rounded margin-top-small">
    <div class="panel--title is--underline">
        <div class="block-group b2b--block-panel">
            <div class="block block--title">
                <h3>{s name="ShippingAddresses"}Shipping addresses{/s}</h3>
            </div>
            {if $companyFilter.level}
                <div class="block block--actions">
                    <button type="button" data-target="address-detail" data-href="{url action=new type=shipping grantContext=$grantContext}" class="btn component-action-create ajax-panel-link {b2b_acl controller=b2baddress action=new}">
                        {s name="CreateAddress"}Create address{/s}
                    </button>
                </div>
            {/if}
        </div>
    </div>
    <div class="panel--body is--wide">
        {include file="frontend/_grid/address-grid.tpl" url={url action="shipping"}}
    </div>
</div>

<div class="b2b--ajax-panel b2b-modal-panel" data-id="address-detail"></div>
