{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.actions = false}
    {$modalSettings.content.padding = false}
    {$modalSettings.bottom = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="AddLineItem"}Add line item{/s}
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    <form action="{url action=create}" method="post" data-ajax-panel-trigger-reload="order-grid,order-clearance-grid" id="form" class="form--inline {b2b_acl controller=b2borderlineitemreference action=create}">
        <input type="hidden" name="orderContextId" value="{$orderContextId}">

        <div class="scrollable without--any with--padding">
            {foreach $errors as $error}
                <div class="modal--errors error--list">
                    {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
                </div>
            {/foreach}

            {include file="frontend/b2borderlineitemreference/_form.tpl"}
        </div>
    </form>
{/block}

{* Modal Content: Grid Component: Bottom *}
{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <div class="bottom--actions">
        <button class="btn" type="submit" data-form-id="form" tabindex="8">{s name="Save"}Save{/s}</button>
    </div>
{/block}