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
    <form action="{url action=update}" method="post" data-ajax-panel-trigger-reload="contact-grid,company-tab-panel" id="form" class="{b2b_acl controller=b2bcontact action=update}">
        <input type="hidden" name="id" value="{$contact->id}">

        {include file="frontend/b2bcontact/_form.tpl" type="edit"}
    </form>
{/block}

{* Modal Content: Grid Component: Bottom *}
{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <div class="bottom--actions">
        {if $isNew}
            <span class="create-additional-contact visible-modal-xs">{s name="CreateAdditionalContactShort"}Create another contact{/s}</span>
            <span class="create-additional-contact visible-modal-sm visible-modal-lg">{s name="CreateAdditionalContact"}Create another contact after saving{/s}</span>
            <span class="checkbox create-additional-contact">
             <input type="checkbox" name="createAdditionalContact" title="{s name="CreateAdditionalContactHelp"}This option reopens this form after saving to create another contact{/s}" value="1" {if ($contact->createAdditionalContact && $isNew) || $createMultiple}checked="checked"{/if}>
             <span class="checkbox--state"></span>
         </span>
        {/if}

        <button class="btn {b2b_acl controller=b2bcontact action=update}" type="submit" data-form-id="form">{s name="Save"}Save{/s}</button>
    </div>
{/block}