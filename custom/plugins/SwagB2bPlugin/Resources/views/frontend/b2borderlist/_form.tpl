{namespace name=frontend/plugins/b2b_debtor_plugin}

{foreach $errors as $error}
    <div class="modal--errors error--list">
        {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
    </div>
{/foreach}

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="OrderListName"}Name{/s}: *
    </div>
    <div class="block box--input is--full">
        <input type="text" name="name" autofocus="autofocus" value="{$orderList->name}" placeholder="{s name="OrderListName"}Name{/s}">
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="Budget"}Budget{/s}:
    </div>
    <div class="block box--input is--full">
        <div class="select-field">
            <select name="budgetId">
                <option value="">{s name="BudgetSelectNone"}None{/s}</option>
                {foreach $budgets as $budget}
                    <option {if $budget->id === $orderList->budgetId}selected{/if} value="{$budget->id}">
                        {$budget->name}
                    </option>
                {/foreach}
            </select>
        </div>
    </div>
</div>