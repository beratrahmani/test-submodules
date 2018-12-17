
//{block name="backend/performance/view/main/multi_request_tasks" append}
Ext.define('Shopware.apps.Performance.view.main.Advisor', {
    override: 'Shopware.apps.Performance.view.main.MultiRequestTasks',

    initComponent: function() {
        this.addProgressBar(
            {
                initialText: '{s namespace=backend/advisor/main name=listing/advisors}Advisor URLs{/s}',
                progressText: '{s namespace=backend/advisor/main name=progress/advisors}[0] of [1] advisor urls{/s}',
                requestUrl: '{url controller="advisor" action="seoAdvisor"}'
            },
            'advisor',
            'seo'
        );

        this.callParent(arguments);
    }
});
//{/block}