{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="frontend/_base/index.tpl"}

{* B2b Main Content *}
{block name="frontend_index_content_b2b"}
    <div class="b2b--plugin module-ordernumber content--wrapper">

        {* Prevent reloading if no access *}
        {if {b2b_acl_check controller=b2bordernumber action="upload"}}
            {* Drag 'n Drop Upload *}
            <div class="b2b--ajax-panel {b2b_acl controller=b2bordernumber action="upload"}" data-id="order-number-upload" data-url="{url action=upload}"
                 data-plugins="b2bFileUpload"
                 data-confirm="true"
                 data-confirm-url="{url controller="b2bconfirm" action="override"}"
            ></div>
        {/if}

        {* Ordernumber Grid *}
        <div class="b2b--ajax-panel">
            <div class="panel has--border is--rounded">
                <div class="panel--title is--underline">
                    <div class="block-group b2b--block-panel">
                        <div class="block block--title">
                            <h3>{s name="CustomOrderNumbers"}Custom OrderNumbers{/s}</h3>
                        </div>
                        <div class="block block--actions">
                            <a target="_blank" href="{url action=exportCsv}" class="btn is--right action--export is--primary ignore--b2b-ajax-panel {b2b_acl controller=b2bordernumber action="exportCsv"}">
                                {s name="Export"}Export{/s} (CSV)
                            </a>
                            <a target="_blank" href="{url action=exportXls}" class="btn is--right action--export is--primary ignore--b2b-ajax-panel {b2b_acl controller=b2bordernumber action="exportXls"}">
                                {s name="Export"}Export{/s} (XLS)
                            </a>
                        </div>
                    </div>
                </div>
                <div class="b2b--ajax-panel"
                     data-id="ordernumber-grid"
                     data-url="{url action=grid}"
                     data-plugins="b2bGridComponent,b2bAjaxProductSearch,b2bOrderNumber"
                     data-product-url="{url action=getProductName}">
                </div>
            </div>
        </div>
    </div>
{/block}