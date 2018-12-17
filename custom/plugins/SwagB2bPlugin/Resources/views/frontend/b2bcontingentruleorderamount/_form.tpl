{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="block-group b2b--form">
    <div class="block box--label  is--full">
        {s name="TimeUnit"}Time unit{/s}: *
    </div>
    <div class="block box--input is--full">
        <div class="select-field">
            <select name="timeRestriction">
                <option value="" disabled selected="selected">{s name="TimeUnit"}Time unit{/s}</option>
                {foreach $timeUnits as $timeUnit}
                    <option value="{$timeUnit}" {if $rule->timeRestriction == $timeUnit}selected="selected"{/if}>{$timeUnit|snippet:$timeUnit:"frontend/plugins/b2b_debtor_plugin"}</option>
                {/foreach}
            </select>
        </div>
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label  is--full">
        {s name="Value"}Value{/s}: *
    </div>
    <div class="block box--input  is--full">
        <input type="number" step="any" min="0" name="value" value="{$rule->value}" placeholder="{s name="Value"}Value{/s}">
    </div>
</div>
