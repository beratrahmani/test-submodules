//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/ui/range"}
Ext.define('Shopware.apps.Advisor.view.details.ui.Range', {
    alias: 'widget.details-ui-Range',
    extend: 'Ext.container.Container',
    layout: 'anchor',
    anchor: '100%',

    snippets: {
        priceFromLabel: '{s name="range_price_from"}Price from{/s}',
        priceToLabel: '{s name="range_price_to"}Price to{/s}'
    },

    /**
     * the initial method
     *
     * @overwrite
     */
    initComponent: function () {
        var me = this;

        me.callParent(arguments);
    },

    /**
     * @param { Shopware.apps.Advisor.model.Question } question
     */
    createItems: function (question) {
        var me = this;

        me.priceFrom = me.createPriceFromSpinner(question);
        me.priceTo = me.createPriceToSpinner(question);

        me.leftContainer = me.createLeftContainer(question);
        me.rightContainer = me.createRightContainer();

        me.mainContainer = me.createMainContainer();

        me.add(me.mainContainer);
    },

    /**
     * @returns { Ext.container.Container }
     */
    createMainContainer: function () {
        var me = this;

        return Ext.create('Ext.container.Container', {
            anchor: '100%',
            layout: 'hbox',
            padding: '0 0 0 5px',
            items: [
                me.leftContainer,
                me.rightContainer
            ]
        })
    },

    /**
     * @param { Shopware.apps.Advisor.model.Question } question
     * @returns { Ext.container.Container }
     */
    createLeftContainer: function (question) {
        var me = this;

        return Ext.create('Ext.container.Container', {
            width: 150,
            items: [
                me.createLabel(question.get('question'))
            ]
        });
    },

    /**
     * @param { string } labelContent
     * @returns { Ext.form.field.Display }
     */
    createLabel: function (labelContent) {
        return Ext.create('Ext.form.field.Display', {
            fieldLabel: labelContent,
            labelWidth: 150
        })
    },

    /**
     * @returns { Ext.container.Container }
     */
    createRightContainer: function () {
        var me = this;

        return Ext.create('Ext.container.Container', {
            flex: 1,
            layout: 'hbox',
            items: [
                me.priceFrom,
                me.priceTo
            ]
        });
    },

    /**
     * @param { Shopware.apps.Advisor.model.Question } question
     * @returns { Ext.form.field.Number }
     */
    createPriceFromSpinner: function (question) {
        var me = this,
            name = 'q' + question.get('id') + '_values_min';

        return Ext.create('Ext.form.field.Number', {
            fieldLabel: me.snippets.priceFromLabel,
            labelWidth: 70,
            name: name,
            step: 10,
            flex: 1,
            minValue: me.minValue,
            maxValue: me.maxValue,
            value: me.minValue,
            style: {
                margin: '0 10px 0 0'
            },
            listeners: {
                'change': function (spinner, newValue) {
                    me.fromSpinnerHandler(spinner, newValue);
                }
            }
        });
    },

    /**
     * @param { Ext.form.field.Number } spinner
     * @param { int | string } newValue
     */
    fromSpinnerHandler: function (spinner, newValue) {
        var me = this,
            priceToValue = me.priceTo.getValue();

        if (newValue > priceToValue) {
            spinner.setValue(priceToValue);
        }
    },

    /**
     * @param { Shopware.apps.Advisor.model.Question } question
     * @returns { Ext.form.field.Number }
     */
    createPriceToSpinner: function (question) {
        var me = this,
            name = 'q' + question.get('id') + '_values_max';

        return Ext.create('Ext.form.field.Number', {
            fieldLabel: me.snippets.priceToLabel,
            labelWidth: 70,
            name: name,
            step: 10,
            flex: 1,
            minValue: me.minValue,
            maxValue: me.maxValue,
            value: me.maxValue,
            listeners: {
                'change': function (spinner, newValue) {
                    me.toSpinnerHandler(spinner, newValue)
                }
            }
        })
    },

    /**
     * @param { Ext.form.field.Number } spinner
     * @param { int | string } newValue
     */
    toSpinnerHandler: function (spinner, newValue) {
        var me = this,
            priceFromValue = me.priceFrom.getValue();

        if (newValue < priceFromValue) {
            spinner.setValue(priceFromValue);
        }
    },

    /**
     * @param question
     */
    setQuestion: function (question) {
        var me = this,
            min = null,
            max = null;

        me.question = question;
        me.answers = question.getAnswers();

        me.answers.each(function (answer) {
            if (answer.get('key') == 'maxPrice') {
                max = answer.get('answer');
            }
            if (answer.get('key') == 'minPrice') {
                min = answer.get('answer');
            }
        });

        me.minValue = min;
        me.maxValue = max;

        me.createItems(question);
    }
});
//{/block}