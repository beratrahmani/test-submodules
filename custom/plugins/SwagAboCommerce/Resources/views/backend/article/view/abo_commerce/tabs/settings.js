
// {namespace name="backend/abo_commerce/article/view/main"}
// {block name="backend/article/view/abo_commerce/tabs/settings"}
Ext.define('Shopware.apps.Article.view.abo_commerce.tabs.Settings', {
    /**
     * The parent class that this class extends.
     */
    extend: 'Ext.form.Panel',

    autoScroll: true,

    bodyPadding: 10,

    cls: 'shopware-form',

    layout: 'anchor',

    border: 0,

    defaults: {
        labelWidth: 200,
        labelStyle: 'font-weight: bold'
    },

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     */
    alias: 'widget.abo-commerce-tab-settings',

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = '{s name=settings/title}{/s}';
        me.registerEvents();
        me.items = me.createFormItems();

        me.callParent(arguments);
    },

    /**
     * Adds the specified events to the list of events which this Observable may fire
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * @Event
             * Custom component event.
             * Fired when the customer select a customer group in the toolbar combo box.
             * @param Ext.data.Model The selected record
             */
            'addPrice'
        );
    },

    createFormItems: function() {
        var me = this,
            mainArticleOrdernumber = me.article.getMainDetail().first().get('number'),
            unitsStore = me.getUnitsComboStore(),
            minDeliveryInterval,
            maxDeliveryInterval,
            minDuration,
            maxDuration;

        minDeliveryInterval = Ext.create('Ext.form.field.Number', {
            name: 'minDeliveryInterval',
            allowDecimals: false,
            minValue: 1,
            allowBlank: false,
            width: 447,
            value: 2,
            fieldLabel: '{s name=settings/column_min_delivery_interval}{/s}',
            labelWidth: 200,
            labelStyle: 'font-weight: bold',
            listeners: {
                scope: me,
                change: Ext.bind(me.validateForm, me)
            },
            validator: function(value) {
                if (minDeliveryInterval.isDisabled() || maxDeliveryInterval.isDisabled() || minDuration.isDisabled()) {
                    return true;
                }
                if (value > maxDeliveryInterval.getValue()) {
                    return '{s name=settings/validation_error_max_delivery_interval}{/s}';
                } else if (value > minDuration.getValue()) {
                    return '{s name=settings/validation_error_min_delivery_interval}{/s}';
                } else {
                    return true;
                }
            }
        });

        maxDeliveryInterval = Ext.create('Ext.form.field.Number', {
            name: 'maxDeliveryInterval',
            allowDecimals: false,
            minValue: 1,
            allowBlank: false,
            width: 447,
            value: 4,
            fieldLabel: '{s name=settings/column_max_delivery_interval}{/s}',
            labelWidth: 200,
            labelStyle: 'font-weight: bold',
            listeners: {
                scope: me,
                change: Ext.bind(me.validateForm, me)
            },
            validator: function(value) {
                if (maxDeliveryInterval.isDisabled() || minDeliveryInterval.isDisabled() || maxDuration.isDisabled()) {
                    return true;
                }
                if (value < minDeliveryInterval.getValue()) {
                    return '{s name=settings/validation_error_max_delivery_interval}{/s}';
                } else if (value > maxDuration.getValue()) {
                    return '{s name=settings/validation_error_max_delivery_max_duration}{/s}';
                } else {
                    return true;
                }
            }
        });

        minDuration = Ext.create('Ext.form.field.Number', {
            name: 'minDuration',
            allowDecimals: false,
            minValue: 1,
            allowBlank: false,
            height: 22,
            width: 447,
            value: 2,
            fieldLabel: '{s name=settings/column_min_duration}{/s}',
            labelWidth: 200,
            labelStyle: 'font-weight: bold',
            listeners: {
                scope: me,
                change: Ext.bind(me.validateForm, me)
            },
            validator: function(value) {
                if (minDuration.isDisabled() || minDeliveryInterval.isDisabled() || maxDuration.isDisabled()) {
                    return true;
                }
                if (value < minDeliveryInterval.getValue()) {
                    return '{s name=settings/validation_error_min_duration}{/s}';
                }
                if (value > maxDuration.getValue()) {
                    return '{s name=settings/validation_error_min_max_duration}{/s}';
                }

                return true;
            }
        });

        maxDuration = Ext.create('Ext.form.field.Number', {
            name: 'maxDuration',
            allowDecimals: false,
            minValue: 1,
            allowBlank: false,
            margins: '',
            width: 447,
            value: 24,
            fieldLabel: '{s name=settings/column_max_duration}{/s}',
            labelWidth: 200,
            labelStyle: 'font-weight: bold',
            listeners: {
                scope: me,
                change: Ext.bind(me.validateForm, me)
            },
            validator: function(value) {
                if (maxDuration.isDisabled() || minDuration.isDisabled()) {
                    return true;
                }
                if (value < minDuration.getValue()) {
                    return '{s name=settings/validation_error_max_duration}{/s}';
                } else {
                    return true;
                }
            }
        });

        return [
            {
                xtype: 'container',

                html: '{s name=settings/column_header_text}{/s}',
                margin: '0 0 10',
                style: 'color: #999; font-style: italic'
            },
            {
                xtype: 'textfield',
                name: 'ordernumber',
                anchor: '100%',
                value: mainArticleOrdernumber + '.ABO',
                fieldLabel: '{s name=settings/column_ordernumber}{/s}'
            },
            minDeliveryInterval,
            {
                xtype: 'container',
                itemId: 'deliveryIntervalContainer',
                margin: '0 0 8',
                defaults: {
                    labelWidth: 200,
                    labelStyle: 'font-weight: bold'
                },
                layout: {
                    align: 'stretch',
                    type: 'hbox'
                },
                items: [
                    maxDeliveryInterval,
                    {
                        xtype: 'combobox',
                        name: 'deliveryIntervalUnit',
                        disabled: true,
                        hidden: true,
                        flex: 1,
                        margins: '0 0 0 10',
                        store: me.getUnitsComboStore(),
                        forceSelection: true,
                        queryMode: 'local',
                        editable: false,
                        allowBlank: false,
                        displayField: 'label',
                        valueField: 'id'
                    }
                ]
            },
            minDuration,
            {
                xtype: 'container',
                itemId: 'durationContainer',
                margin: '0 0 8',
                defaults: {
                    labelWidth: 200,
                    labelStyle: 'font-weight: bold'
                },
                layout: {
                    align: 'stretch',
                    type: 'hbox'
                },
                items: [
                    maxDuration,
                    {
                        xtype: 'combobox',
                        name: 'durationUnit',
                        flex: 1,
                        margins: '0 0 0 10',
                        fieldLabel: '',
                        store: unitsStore,
                        forceSelection: true,
                        queryMode: 'local',
                        editable: false,
                        allowBlank: false,
                        displayField: 'label',
                        valueField: 'id',
                        listeners: {
                            'afterrender': function () {
                                this.setValue(this.store.getAt('0').get('id'));
                            }
                        }
                    }
                ]
            },
            {
                xtype: 'checkboxfield',
                name: 'endlessSubscription',
                inputValue: 1,
                uncheckedValue: 0,
                anchor: '100%',
                margin: '0 0 8',
                fieldLabel: '{s name=settings/endless_subscription}{/s}',
                listeners: {
                    scope: me,
                    change: me.onChangeEndlessSubscription
                }
            }, {
                xtype: 'container',
                margin: '0 0 8',
                defaults: {
                    labelWidth: 200,
                    labelStyle: 'font-weight: bold'
                },
                layout: {
                    align: 'stretch',
                    type: 'hbox'
                },
                items: [{
                    xtype: 'numberfield',
                    fieldLabel: '{s name=settings/period_of_notice_interval}{/s}',
                    disabled: true,
                    name: 'periodOfNoticeInterval',
                    allowDecimals: false,
                    minValue: 1,
                    allowBlank: false,
                    flex: 2,
                    value: 3,
                    labelWidth: 200,
                    labelStyle: 'font-weight: bold',
                    listeners: {
                        scope: me,
                        change: me.onPeriodOfNoticeIntervalChange
                    }
                }, {
                    xtype: 'combobox',
                    disabled: true,
                    name: 'periodOfNoticeUnit',
                    flex: 2,
                    margins: '0 0 0 10',
                    store: me.getUnitsComboStore(),
                    forceSelection: true,
                    queryMode: 'local',
                    editable: false,
                    allowBlank: false,
                    displayField: 'label',
                    valueField: 'id',
                    value: 'months',
                    listeners: {
                        scope: me,
                        change: me.validateForm
                    }
                }, {
                    xtype: 'checkbox',
                    itemId: 'terminatedirectly',
                    name: 'directTermination',
                    margins: '0 0 0 10',
                    disabled: true,
                    boxLabel: '{s name=settings/terminable_anytime}{/s}',
                    inputValue: 1,
                    uncheckedValue: 0,
                    flex: 1,
                    listeners: {
                        scope: me,
                        change: me.onTerminateDirectly
                    }
                }]
            },
            {
                xtype: 'checkboxfield',
                name: 'limited',
                inputValue: 1,
                uncheckedValue: 0,
                anchor: '100%',
                margin: '0 0 8',
                fieldLabel: '{s name=settings/column_limited}{/s}',
                boxLabel: '{s name=settings/column_limited_text}{/s}'
            },
            {
                xtype: 'numberfield',
                name: 'maxUnitsPerWeek',
                minValue: 1,
                allowBlank: false,
                anchor: '100%',
                value: 50,
                fieldLabel: '{s name=settings/column_max_per_week}{/s}'
            },
            {
                xtype: 'textareafield',
                name: 'description',
                anchor: '100%',
                fieldLabel: '{s name=settings/column_description}{/s}'
            }
        ];
    },

    validateForm: function() {
        var me = this;

        me.getForm().isValid();
    },

    /**
     * @param { Ext.form.field.Checkbox} cb
     * @param { boolean } checked
     */
    onChangeEndlessSubscription: function(cb, checked) {
        var me = this,
            deliveryIntervalCt = cb.previousSibling('#deliveryIntervalContainer'),
            ct = cb.previousSibling('#durationContainer'),
            noticeCt = cb.nextSibling('container');

        ct.previousSibling('[name=minDuration]').setDisabled(checked);
        deliveryIntervalCt.down('[name=deliveryIntervalUnit]').setDisabled(!checked);
        deliveryIntervalCt.down('[name=deliveryIntervalUnit]').setVisible(checked);

        ct.down('[name=maxDuration]').setDisabled(checked);
        ct.down('[name=durationUnit]').setDisabled(checked);

        noticeCt.down('#terminatedirectly').setDisabled(!checked);
        noticeCt.down('#terminatedirectly').setValue(false);
        noticeCt.down('numberfield[name=periodOfNoticeInterval]').setDisabled(!checked);
        noticeCt.down('[name=periodOfNoticeUnit]').setDisabled(!checked);

        me.validateForm();
    },

    /**
     * @param { Ext.form.field.Checkbox} cb
     * @param { boolean } checked
     */
    onTerminateDirectly: function(cb, checked) {
        var field = cb.previousSibling('numberfield[name=periodOfNoticeInterval]'),
            unitField = cb.previousSibling('combo[name=periodOfNoticeUnit]');

        if (checked) {
            field.addCls(field['disabledCls']);
            field.setReadOnly(true);
            unitField.addCls(unitField['disabledCls']);
            unitField.setReadOnly(true);
        } else {
            field.removeCls(field['disabledCls']);
            field.setReadOnly(false);
            unitField.removeCls(unitField['disabledCls']);
            unitField.setReadOnly(false);
        }
    },

    /**
     * @param { Ext.form.field.Numberfield} field
     * @param { int } newValue
     */
    onPeriodOfNoticeIntervalChange: function(field, newValue) {
        var me = this;

        me.validateForm();
    },

    /**
     * Creates store object used for the typ column
     *
     * @return Ext.data.SimpleStore
     */
    getUnitsComboStore: function() {
        var me = this;

        return new Ext.data.SimpleStore({
            fields: ['id', 'label'],
            data: [
                ['weeks', '{s name=settings/unit_weeks}{/s}'],
                ['months', '{s name=settings/unit_months}{/s}']
            ]
        });
    }
});
// {/block}
