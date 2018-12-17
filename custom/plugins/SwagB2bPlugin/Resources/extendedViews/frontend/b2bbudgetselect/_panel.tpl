{if $b2bBudgets}
    <div class="b2b-budget--checkout panel has--border">

        <div class="block-group group--budget">
            <div class="block block--headline">

                <h3 class="panel--title primary">
                    {s name="BudgetHelpText" namespace="frontend/plugins/b2b_debtor_plugin"}Assign a budget to be charged when the order is placed{/s}
                </h3>

            </div>
            <div class="block block--select">

                {if $b2bBudgets|count > 1}
                    <div class="select-field">
                        <form method="post" action="{url controller=b2bbudgetselect action=select amount=$amount}" data-ajax-panel-trigger-reload="_WINDOW_">
                            <select name="b2bBudgetReference" class="is--auto-submit">
                                {foreach $b2bBudgets as $budget}
                                    <option
                                            value="{$budget->id}"
                                            {if !$budget->currentStatus->isSufficient}disabled="disabled"{/if}
                                            {if $budget->id === $b2bSelectedBudget->id}selected="selected"{/if}
                                    >
                                        {include file="frontend/_base/budget-option.tpl" budget=$budget}
                                    </option>
                                {/foreach}
                            </select>
                        </form>
                    </div>
                {/if}

            </div>
        </div>

        <div class="panel--body is--wide">
            {block name="b2b_budget_panel_content"}{/block}
        </div>
    </div>
{/if}
