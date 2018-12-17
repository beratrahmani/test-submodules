{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.actions = false}
    {$modalSettings.content.padding = true}
    {$modalSettings.bottom = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="CreateOrderList"}Create order list{/s}
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    {include file="frontend/b2borderlist/_form.tpl"}
{/block}

{* Modal Content: Grid Component: Bottom *}
{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <div class="bottom--actions">
        <button class="btn" type="submit">{s name="Save"}Save{/s}</button>
    </div>
{/block}