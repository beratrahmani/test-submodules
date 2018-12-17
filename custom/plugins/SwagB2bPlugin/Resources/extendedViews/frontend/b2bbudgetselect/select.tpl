{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/b2bbudgetselect/_panel.tpl"}

{block name="b2b_budget_panel_content"}
    {include file="frontend/_includes/messages.tpl" type="success" content={"SelectBudgetInfo"|snippet:"SelectBudgetInfo":"frontend/plugins/b2b_debtor_plugin"}}
{/block}
