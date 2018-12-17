{namespace name=frontend/plugins/b2b_debtor_plugin}

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
                            data-href="{url controller={"b2bcontingentrule"|cat:{$ruleType|lower}} action=$action id=$rule->id}"
                    >
                        {$ruleType|snippet:$ruleType:"frontend/plugins/b2b_debtor_plugin"}
                    </option>
                {/foreach}
            </select>
        </div>
    </div>
</div>

<div class="b2b--ajax-panel height--auto" data-url="" data-plugins="b2bAjaxProductSearch" data-id="contingent-rule-type-form"></div>