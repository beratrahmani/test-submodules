{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="panel is--rounded">
    <div class="panel--title is--underline">

        <div class="block-group b2b--block-panel">
            <div class="block block--title">
                <h3>{s name="BudgetManagement"}Budget Management{/s}</h3>
            </div>
            {if $companyFilter.level}
                <div class="block block--actions">
                    <button type="button" data-target="budget-detail" data-href="{url action=new grantContext=$grantContext}" class="btn component-action-create ajax-panel-link {b2b_acl controller=b2bbudget action=new}">
                        {s name="CreateBudget"}Create Budget{/s}
                    </button>
                </div>
            {/if}
        </div>
    </div>
    <div class="panel--body is--wide">

        {include file="frontend/b2bbudget/_grid.tpl" gridState=$budgetGrid}

    </div>
</div>

{* Contact Detail Modal *}
<div class="is--b2b-ajax-panel b2b--ajax-panel b2b-modal-panel" data-id="budget-detail"></div>

