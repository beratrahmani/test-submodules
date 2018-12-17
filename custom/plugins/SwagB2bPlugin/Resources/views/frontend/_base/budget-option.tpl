{if $budget->identifier}
    {$budget->identifier}
    ({$budget->name})
{else}
    ({$budget->name})
{/if}
-
{{$prefixedComponentName|snippet:"BudgetAvailable":"frontend/plugins/b2b_debtor_plugin"}|sprintf:{$budget->currentStatus->availableBudget|currency}}
-
{if $budget->currentStatus->isSufficient }
    {s name="Sufficient"}sufficient{/s}
{else}
    {s name="NotSufficient"}not sufficient{/s}
{/if}
