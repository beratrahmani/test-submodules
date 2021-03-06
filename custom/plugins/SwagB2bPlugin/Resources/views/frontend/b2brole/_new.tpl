{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.actions = false}
    {$modalSettings.bottom = true}
    {$modalSettings.content.padding = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="CreateRole"}Create role{/s}
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    <form action="{url action=create}" method="post"  data-ajax-panel-trigger-reload="role-tree" id="form" class="{b2b_acl controller=b2brole action=create}">
        <input type="hidden" name="parentId" value="{$parentId}">
        {include file="frontend/b2brole/_form.tpl"}
    </form>
{/block}

{* Modal Content: Grid Component: Bottom *}
{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <div class="bottom--actions">
        <button class="btn" type="submit" data-form-id="form">{s name="Save"}Save{/s}</button>
    </div>
{/block}