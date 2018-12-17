{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="frontend/_base/index.tpl"}

{* B2b Account Main Content *}
{block name="frontend_index_content_b2b"}

    <div class="b2b--plugin content--wrapper module--orderlists">

        {* Order list Grid *}
        <div class="b2b--ajax-panel" data-id="order-list-grid" data-url="{url action=grid}" data-plugins="b2bGridComponent,b2bOrderList"></div>

        {* Modal Box *}
        <div class="is--b2b-ajax-panel b2b--ajax-panel b2b-modal-panel" data-id="order-list-detail" data-plugins="b2bAjaxProductSearch"></div>
    </div>
{/block}
