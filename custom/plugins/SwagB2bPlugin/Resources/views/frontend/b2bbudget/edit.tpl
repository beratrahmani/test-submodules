{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.actions = false}
    {$modalSettings.bottom = true}
    {$modalSettings.content.padding = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="EditBudget"}Edit budget{/s}
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    <form action="{url action=update}" method="post" data-ajax-panel-trigger-reload="budget-grid,company-tab-panel" id="form" class="form--inline {b2b_acl controller=b2bbudget action=update}">
        <input type="hidden" name="id" value="{$budget->id}">

        {include file="frontend/b2bbudget/_form.tpl"}
    </form>
{/block}

{* Modal Content: Grid Component: Bottom *}
{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <div class="bottom--actions">
        <button class="btn {b2b_acl controller=b2bbudget action=update}" type="submit" tabindex="8" data-form-id="form">{s name="Save"}Save{/s}</button>
    </div>
{/block}