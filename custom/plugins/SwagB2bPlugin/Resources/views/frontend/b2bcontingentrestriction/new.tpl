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
    {s name="CreateContingentRestriction"}Create contingent restriction{/s}
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    {foreach $errors as $error}
        <div class="modal--errors error--list">
            {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
        </div>
    {/foreach}

    <form action="{url action=create}" method="post" data-ajax-panel-trigger-reload="role-grid" id="form" class="form--inline {b2b_acl controller=b2bcontingentrestriction action=update}">
        <input type="hidden" name="contingentGroupId" value="{$contingentGroupId}">

        <div class="block-group b2b--form">
            <div class="block box--label is--full">
                {s name="Type"}Type{/s}: *
            </div>
            <div class="block box--input is--full">
                <div class="select-field">
                    <select name="type" class="is--ajax-panel-navigation">
                        <option value="" disabled selected="selected">{s name="Type"}Type{/s}</option>
                        {foreach $registeredRules as $ruleType}
                            <option
                                    value="{$ruleType}"
                                    {if $rule->type == $ruleType} selected="selected"{/if}
                                    class="ajax-panel-link"
                                    data-target="contingent-rule-type-form"
                                    data-href="{url controller={"b2bcontingentrule"|cat:{$ruleType|lower}} action=new id=$rule->id}"
                            >
                                {$ruleType|snippet:$ruleType:"frontend/plugins/b2b_debtor_plugin"}
                            </option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>

        <div class="b2b--ajax-panel" data-url="" data-plugins="b2bAjaxProductSearch" data-id="contingent-rule-type-form"></div>
    </form>
{/block}

{* Modal Content: Grid Component: Bottom *}
{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <div class="bottom--actions">

        <a class="btn is--left {b2b_acl controller=b2bcontingentrestriction action=grid}"
           data-target="contingent-tab-content"
           data-href="{url controller=b2bcontingentrule action=grid id=$contingentGroupId}"
           title="{s name="Abort"}Abort{/s}"
        >
            {s name="Abort"}Abort{/s}
        </a>

        <button class="btn" type="submit" data-form-id="form">{s name="Save"}Save{/s}</button>
    </div>
{/block}