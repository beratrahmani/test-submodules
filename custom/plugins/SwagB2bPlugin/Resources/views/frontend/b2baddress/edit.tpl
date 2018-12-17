{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.actions = false}
    {$modalSettings.content.padding = true}
    {$modalSettings.bottom = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="EditAddress"}Edit Address{/s}
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}

    <form action="{url action=update}" method="post" data-ajax-panel-trigger-reload="address-shipping-grid,address-billing-grid,company-tab-panel" id="form" class="form--inline {b2b_acl controller=b2baddress action=update}">
        <input type="hidden" name="id" value="{$address->id}">

        {include file="frontend/b2baddress/_form.tpl"}
    </form>

{/block}

{* Modal Content: Grid Component: Bottom *}
{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <div class="bottom--actions {b2b_acl controller=b2baddress action=update}">
        <button class="btn" type="submit"  data-form-id="form">{s name="Save"}Save{/s}</button>
    </div>
{/block}