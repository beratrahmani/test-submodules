{namespace name=frontend/plugins/b2b_debtor_plugin}

{foreach $errors as $error}
    <div class="modal--errors error--list">
        {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
    </div>
{/foreach}

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="Identifier"}Identifier{/s}: *
    </div>
    <div class="block box--input is--full">
        <input type="text" name="identifier" value="{$budget->identifier}" placeholder="{s name="Identifier"}Identifier{/s}">
    </div>
</div>

<div class="block-group  b2b--form">
    <div class="block box--label is--full">
        {s name="Name"}Name{/s}: *
    </div>
    <div class="block box--input is--full">
        <input type="text" name="name" value="{$budget->name}" placeholder="{s name="Name"}Name{/s}">
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="Amount"}Amount{/s}: *
    </div>
    <div class="block box--input is--full">
        <input type="number" min="0" step="any" name="amount" value="{$budget->amount}" placeholder="{s name="Amount"}Amount{/s}">
    </div>
</div>

<div class="block-group  b2b--form">
    <div class="block box--label is--full">
        {s name="RefreshType"}RefreshType{/s}: *
    </div>
    <div class="block box--input is--full">
        <div class="select-field">
            <select name="refreshType">
                <option {if $budget->refreshType === 'none'}selected{/if} value="none">{s name="BudgetOptionNever"}Never{/s}</option>
                <option {if $budget->refreshType === 'yearly'}selected{/if} value="yearly">{s name="BudgetOptionYearly"}Yearly{/s}</option>
                <option {if $budget->refreshType === 'biannual'}selected{/if} value="biannual">{s name="BudgetOptionBiannual"}Biannual{/s}</option>
                <option {if $budget->refreshType === 'quarterly'}selected{/if} value="quarterly">{s name="BudgetOptionQuarterly"}Quarterly{/s}</option>
                <option {if $budget->refreshType === 'monthly'}selected{/if} value="monthly">{s name="BudgetOptionMonthly"}Monthly{/s}</option>
            </select>
        </div>
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="Active"}Active{/s}:
    </div>
    <div class="block box--input is--full">
        <span class="checkbox">
            <input type="checkbox" name="active" value="1" {if $budget->active || $isNew}checked="checked"{/if}>
            <span class="checkbox--state"></span>
        </span>
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="ResponsibleContact"}Responsible contact{/s}:
    </div>
    <div class="block box--input is--full">
        <div class="select-field">
            <select name="ownerId">
                <optgroup label="{s name="OptionNone"}None{/s}">
                    <option value="">{s name="OptionNone"}None{/s}</option>
                </optgroup>
                <optgroup label="{s name="Debtor"}Debtor{/s}">
                    <option {if $budget->ownerId == $debtorAuthId}selected{/if} value="{$debtorAuthId}">{$debtor->lastName}, {$debtor->firstName} ({$debtor->email})</option>
                </optgroup>
                <optgroup label="{s name="Contacts"}Contacts{/s}">
                    {foreach $contacts as $contact}
                        <option {if $budget->ownerId == $contact->authId}selected{/if} value="{$contact->authId}">{$contact->lastName}, {$contact->firstName} ({$contact->email})</option>
                    {/foreach}
                </optgroup>
            </select>
        </div>
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="FiscalYearStart"}Fiscal year start{/s}:
    </div>
    <div class="block box--input is--full">
        <input class="datepicker" data-datepicker="true" name="fiscalYear" type="text" data-defaultDate="{if $budget->fiscalYear}{$budget->fiscalYear}{else}{$smarty.now|date_format:'Y-m-d'}{/if}"/>
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="NotifyAuthor"}Send a mail if percentage is reached{/s}:
    </div>
    <div class="block box--input is--full">
        <span class="checkbox">
            <input type="checkbox" name="notifyAuthor" value="1" {if $budget->notifyAuthor}checked="checked"{/if}>
            <span class="checkbox--state"></span>
        </span>
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="NotifyAuthorPercentage"}Percentage to send mail{/s}:
    </div>
    <div class="block box--input is--full">
        <input type="number" name="notifyAuthorPercentage" value="{if $budget->notifyAuthorPercentage}{$budget->notifyAuthorPercentage}{else}{s name="NotifyAuthorDefaultPercentage"}80{/s}{/if}">
    </div>
</div>