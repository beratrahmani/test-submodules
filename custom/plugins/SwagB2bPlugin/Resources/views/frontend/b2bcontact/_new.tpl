{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.content.padding = true}
    {$modalSettings.bottom = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="CreateContact"}Create contact{/s}
{/block}

{block name="b2b_modal_base_content_inner_scrollable"}
    <div class="scrollable with--grid">
        {if $modalSettings.actions}
            {block name="b2b_modal_base_content_inner_scrollable_inner_actions"}
                <div class="inner--actions">
                    {block name="b2b_modal_base_content_inner_scrollable_inner_actions_inner"}{/block}
                </div>
            {/block}
        {/if}

        <form action="{url action=create}" method="post" data-ajax-panel-trigger-reload="company-tab-panel" id="form"
              class="form--inline {b2b_acl controller=b2bcontact action=create}">
            <div class="inner--content{if !$modalSettings.actions} without--actions{/if}{if !$modalSettings.bottom} without--bottom{/if}{if $modalSettings.content.padding} with--padding{/if}">
                {include file="frontend/b2bcontact/_form.tpl" type="create"}
                <input type="hidden" name=" grantContext" value="{$grantContext}"/>
            </div>

            {if $modalSettings.bottom}
                {block name="b2b_modal_base_content_inner_scrollable_inner_bottom"}
                    <div class="inner--bottom">
                        <div class="bottom--actions">
                            {if $isNew}
                                <span class="create-additional-contact visible-modal-xs">{s name="CreateAdditionalContactShort"}Create another contact{/s}</span>
                                <span class="create-additional-contact visible-modal-sm visible-modal-lg">{s name="CreateAdditionalContact"}Create another contact after saving{/s}</span>
                                <span class="checkbox create-additional-contact">
                                    <input type="checkbox" name="createAdditionalContact"
                                        title="{s name="CreateAdditionalContactHelp"}This option reopens this form after saving to create another contact{/s}"
                                        value="1"
                                        {if ($contact->createAdditionalContact && $isNew) || $createMultiple}checked="checked"{/if}>
                                    <span class="checkbox--state"></span>
                                </span>
                            {/if}

                            <button class="btn" type="submit">{s name="Save"}Save{/s}</button>
                        </div>
                    </div>
                {/block}
            {/if}
        </form>
    </div>
{/block}