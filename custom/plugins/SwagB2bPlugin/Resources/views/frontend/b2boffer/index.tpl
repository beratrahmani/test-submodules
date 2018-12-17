{extends file="frontend/_base/index.tpl"}

{namespace name=frontend/plugins/b2b_debtor_plugin}

{* B2b Account Main Content *}
{block name="frontend_index_content_b2b"}
    <div class="b2b--ajax-panel">
        <div class="panel has--border is--rounded">
            <div class="panel--title is--underline">
                <div class="block-group b2b--block-panel">
                    <div class="block block--title">
                        {s name="Offers"}Offers{/s}
                    </div>
                </div>
            </div>
            <div class="panel--body is--wide">
                <div class="b2b--ajax-panel" data-id="offer-grid" data-url="{url controller="b2boffer" action=grid}" data-plugins="b2bGridComponent"></div>
            </div>
        </div>
    </div>

    <div class="is--b2b-ajax-panel b2b--ajax-panel b2b-modal-panel" data-id="offer-detail"></div>
{/block}
