{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="frontend/_base/index.tpl"}

{* B2b Account Main Content *}
{block name="frontend_index_content_b2b"}
    <div class="b2b--plugin module-fastorder content--wrapper">

        {* Drag 'n Drop Upload *}
        <div class="b2b--ajax-panel" data-id="fast-order-upload" data-url="{url action=upload}"
             data-plugins="b2bFileUpload"></div>

        {* CSV Form *}
        <div class="b2b--ajax-panel">
            <div class="panel has--border is--rounded">
                <div class="panel--title is--underline">
                    <div class="block-group b2b--block-panel">
                        <div class="block block--title">
                            <h3>{s name="FastOrder"}Fast order{/s}</h3>
                        </div>
                        <div class="block block--actions"></div>
                    </div>
                </div>
                <div class="b2b--ajax-panel" data-id="fast-order-grid" data-url="{url action=defaultList}"
                     data-plugins="b2bAjaxProductSearch" data-product-url="{url action=getProductName}"></div>

                <div class="panel--body is--wide">
                    <div class="is--b2b-ajax-panel b2b--ajax-panel"
                         data-id="fast-order-remote-box"
                         data-plugins="b2bOrderList"
                         data-url="{url controller=b2bfastorderremote action=remoteListFastOrder}"></div>
                </div>
            </div>
        </div>
    </div>
{/block}
