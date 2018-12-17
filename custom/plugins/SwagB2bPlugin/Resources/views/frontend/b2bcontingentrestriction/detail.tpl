{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.actions = false}
    {$modalSettings.content.padding = true}
    {$modalSettings.bottom = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="EditContingentRestriction"}EditContingentRestriction{/s}
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}

    {foreach $errors as $error}
        <div class="modal--errors error--list">
            {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
        </div>
    {/foreach}

    <form action="{url action=update}" method="post" data-ajax-panel-trigger-reload="role-grid" id="form" class="{b2b_acl controller=b2bcontingentrule action=update}">
        <input type="hidden" name="id" value="{$rule->id}">
        <input type="hidden" name="contingentGroupId" value="{$rule->contingentGroupId}">
        {include file="frontend/b2bcontingentrestriction/_form.tpl" action=edit contingentGroupId=$contingentGroupId}
    </form>

{/block}

{* Modal Content: Grid Component: Bottom *}
{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <div class="bottom--actions">

        <a class="btn is--left {b2b_acl controller=b2bcontingentrestriction action=grid}"
           data-target="contingent-tab-content"
           data-href="{url controller=b2bcontingentrestriction action=grid id=$contingentGroupId}"
           title="{s name="Abort"}Abort{/s}"
        >
            {s name="Abort"}Abort{/s}
        </a>

        <button class="btn" type="submit" data-form-id="form">{s name="Save"}Save{/s}</button>
    </div>
{/block}