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
    <form action="{url action=update}" method="post" data-ajax-panel-trigger-reload="role-tree" id="form" class="{b2b_acl controller=b2brole action=update}">
        <input type="hidden" name="id" value="{$role->id}">

        {include file="frontend/b2brole/_form.tpl"}
    </form>
{/block}

{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <div class="bottom--actions">
        <button class="btn" type="submit" data-form-id="form">{s name="Save"}Save{/s}</button>
    </div>
{/block}
