//{namespace name=backend/plugins/b2b_debtor_plugin}
//{block name="backend/risk_management/view/risk_management/panel"}
//{$smarty.block.parent}
/** global: Ext */
Ext.define('Shopware.apps.RiskManagementRule.view.risk_management.panel', {
    override: 'Shopware.apps.RiskManagement.view.risk_management.Panel',

    createData: function() {
        var me = this,
            elements = me.callParent(arguments);

        elements.push([
            '<b>{s name=b2b_riskManagementRule}B2B Account{/s}</b>',
            '{s name=b2b_riskManagementRuleSyntax}1 or 0{/s}',
            '{s name=b2b_riskManagementRuleExample}1 blocks the payment for b2b users and 0 for normal users{/s}'
        ]);

        return elements;
    }
});
//{/block}