{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.navigation = true}

    {$modalSettings.actions = false}
    {$modalSettings.content.padding = true}
    {$modalSettings.bottom = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="MasterData"}Master data{/s}
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    {foreach $errors as $error}
        <div class="modal--errors error--list">
            {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
        </div>
    {/foreach}

    <form action="{url action=update orderlist=$orderList->id}" method="post" data-ajax-panel-trigger-reload="order-list-grid" id="form" class="{b2b_acl controller=b2borderlist action=update}">
        {include file="frontend/b2borderlist/_form.tpl"}
    </form>
{/block}

{* Modal Content: Grid Component: Bottom *}
{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <div class="bottom--actions {b2b_acl controller=b2borderlist action=update}">
        <button class="btn" type="submit" data-form-id="form">{s name="Save"}Save{/s}</button>
    </div>
{/block}