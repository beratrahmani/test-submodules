{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal.tpl"}

{block name="b2b_modal_base" prepend}
    {$modalSettings.navigation = false}
{/block}

{block name="b2b_modal_base_content_inner"}
    <form action="{url action=create}" method="post" data-ajax-panel-trigger-reload="order-list-grid,order-list-remote-box,fast-order-remote-box" data-close-success="true" class="form--inline {b2b_acl controller=b2borderlist action=create}">
        {include file="frontend/b2borderlist/_new.tpl"}
    </form>
{/block}