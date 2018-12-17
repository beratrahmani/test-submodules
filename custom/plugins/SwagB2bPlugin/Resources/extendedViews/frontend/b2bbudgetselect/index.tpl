{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/b2bbudgetselect/_panel.tpl"}

{block name="b2b_budget_panel_content"}
    {if $b2bSelectedBudget}
        <div class="block-group">
            <div class="block">
                <strong>{s name="Identifier"}Identifier{/s}</strong><br>
                {$b2bSelectedBudget->identifier}

            </div>
            <div class="block">
                <strong>{s name="Name"}Name{/s}</strong><br>
                {$b2bSelectedBudget->name}
            </div>
            <div class="block">
                <strong>{s name="BudgetAmountAvailable"}Available{/s}</strong><br>
                {$b2bSelectedBudget->currentStatus->availableBudget|currency}
            </div>
            <div class="block">
                <strong>{s name="BudgetCapacity"}Capacity{/s}</strong><br>
               <div class="b2b-progress-bar">
                    <span class="b2b-progress-value-text">{$b2bSelectedBudget->currentStatus->percentage}%</span>
                    <div class="b2b-progress-value" style="width: {$b2bSelectedBudget->currentStatus->percentage}%;"></div>
                </div>
            </div>
        </div>
    {/if}
{/block}
