// {namespace name="backend/swag_promotion/main"}
// {block name="backend/swag_rule_tree/controller/form"}
Ext.define('Shopware.apps.SwagTreeRule.controller.Form', {
    extend: 'Enlight.app.Controller',

    form: undefined,
    ruleDefinition: undefined,
    treeController: undefined,
    selectedNode: undefined,

    operatorStores: {
        customer_stream: Ext.create('Ext.data.Store', {
            fields: ['operator', 'name'],
            data: [
                { 'operator': 'in', 'name': '{s name=oneOf}is one of{/s}' },
                { 'operator': 'notin', 'name': '{s name=notOneOf}is NOT one of{/s}' }
            ]
        }),
        string: Ext.create('Ext.data.Store', {
            fields: ['operator', 'name'],
            data: [
                { 'operator': '=', 'name': '=' },
                { 'operator': '!=', 'name': '{s name=unequal}unequal{/s}' },
                { 'operator': 'in', 'name': '{s name=oneOf}is one of{/s}' },
                { 'operator': 'notin', 'name': '{s name=notOneOf}is NOT one of{/s}' },
                { 'operator': 'contains', 'name': '{s name=contains}contains{/s}' },
                { 'operator': 'notcontains', 'name': '{s name=notContains}does NOT contain{/s}' }
            ]
        }),
        numeric: Ext.create('Ext.data.Store', {
            fields: ['operator', 'name'],
            data: [
                { 'operator': '=', 'name': '=' },
                { 'operator': '!=', 'name': '!=' },
                { 'operator': '>=', 'name': '>=' },
                { 'operator': '<=', 'name': '<=' },
                { 'operator': '<', 'name': '<' },
                { 'operator': '>', 'name': '>' },
                { 'operator': 'in', 'name': '{s name=oneOf}is one of{/s}' },
                { 'operator': 'notin', 'name': '{s name=notOneOf}is NOT one of{/s}' },
                { 'operator': 'istrue', 'name': '{s name=isTrue}is true{/s}' },
                { 'operator': 'isfalse', 'name': '{s name=isFalse}is false{/s}' }
            ]
        }),
        decimal: Ext.create('Ext.data.Store', {
            fields: ['operator', 'name'],
            data: [
                { 'operator': '=', 'name': '=' },
                { 'operator': '!=', 'name': '!=' },
                { 'operator': '>=', 'name': '>=' },
                { 'operator': '<=', 'name': '<=' },
                { 'operator': '<', 'name': '<' },
                { 'operator': '>', 'name': '>' },
                { 'operator': 'in', 'name': '{s name=oneOf}is one of{/s}' },
                { 'operator': 'notin', 'name': '{s name=notOneOf}is NOT one of{/s}' },
                { 'operator': 'istrue', 'name': '{s name=isTrue}is true{/s}' },
                { 'operator': 'isfalse', 'name': '{s name=isFalse}is false{/s}' }
            ]
        }),
        text: Ext.create('Ext.data.Store', {
            fields: ['operator', 'name'],
            data: [
                { 'operator': 'contains', 'name': '{s name=contains}contains{/s}' },
                { 'operator': 'notcontains', 'name': '{s name=notContains}does NOT contain{/s}' }
            ]
        }),
        boolean: Ext.create('Ext.data.Store', {
            fields: ['operator', 'name'],
            data: [
                { 'operator': 'istrue', 'name': '{s name=isTrue}is true{/s}' },
                { 'operator': 'isfalse', 'name': '{s name=isFalse}is false{/s}' }
            ]
        }),
        date: Ext.create('Ext.data.Store', {
            fields: ['operator', 'name'],
            data: [
                { 'operator': '=', 'name': '=' },
                { 'operator': '!=', 'name': '!=' },
                { 'operator': '>=', 'name': '>=' },
                { 'operator': '<=', 'name': '<=' },
                { 'operator': '<', 'name': '<' },
                { 'operator': '>', 'name': '>' }
            ]
        }),
        datetime: Ext.create('Ext.data.Store', {
            fields: ['operator', 'name'],
            data: [
                { 'operator': '=', 'name': '=' },
                { 'operator': '!=', 'name': '!=' },
                { 'operator': '>=', 'name': '>=' },
                { 'operator': '<=', 'name': '<=' },
                { 'operator': '<', 'name': '<' },
                { 'operator': '>', 'name': '>' }
            ]
        })

    },

    init: function() {
        var me = this;
        me.operatorStores.integer = me.operatorStores.numeric;
        me.operatorStores.float = me.operatorStores.numeric;

        me.createStores();
        me.registerEvents();
    },

    getOperatorStore: function(type, field) {
        var me = this;

        type = type.replace('CompareRule', '');

        if (!me.ruleDefinition.fields[type]) {
            throw new Error('Type not found: ' + type);
        }
        if (!me.ruleDefinition.fields[type][field]) {
            throw new Error('Field ' + field + ' not found for type ' + type);
        }
        if (!me.operatorStores[me.ruleDefinition.fields[type][field]]) {
            throw new Error('Operator store not defined for field ' + field + ' in type ' + type + '. Datatype: ' + me.ruleDefinition.fields[type][field]);
        }

        return me.operatorStores[me.ruleDefinition.fields[type][field]];
    },

    registerEvents: function() {
        var me = this;

        me.form.fieldCombo.on('change', function(combo, newValue, oldValue) {
            if (oldValue != newValue && oldValue != undefined) {
                var valueField = me.form.value,
                    comboField = me.form.fieldCombo;
                Ext.Msg.show({
                    title: '{s name=changeRuleConfirmMessageTitle}Caution{/s}',
                    msg: '{s name=changeRuleConfirmMessageBody}If you change this selection, all settings of this rule will be deleted. Do you want to proceed?{/s}',
                    buttons: Ext.Msg.YESNO,
                    fn: function(btn) {
                        if (btn === 'yes') {
                            valueField.reset();
                            me.prepareFields(me.form.typeCombo.getValue(), newValue);
                        } else if (btn === 'no') {
                            comboField.setValue(oldValue);
                            Ext.Msg.hide();
                            return false;
                        }
                    }
                });
            } else {
                return false;
            }
        });
        me.form.operatorCombo.on('select', function() {
            me.updateTree();
        });
        me.form.typeCombo.on('select', function(el, records) {
            var value = records[0].get('type');

            if (el.store.totalCount === 3) {
                me.prepareFields(value, null, null, null, true);
            } else {
                me.prepareFields(value);
            }

            me.form.showElements();
        });

        me.form.value.on('change', function() {
            me.updateTree();
        });

        me.form.streamCombo.on('select', function(el, records) {
            me.form.value.setValue(records[0].get('id'));
        });
    },

    prepareFields: function(type, field, operator, value, isRoot) {
        var me = this;

        if (isRoot) {
            me.form.typeCombo.bindStore(me.form.rootStore);
        } else {
            me.form.typeCombo.bindStore(me.form.defaultStore);
        }

        if (!type) {
            me.form.typeCombo.reset();
            me.form.fieldCombo.reset();
            me.form.operatorCombo.reset();
            me.form.value.reset();
            return;
        }

        type = type.replace('CompareRule', '');

        if (type === 'and' || type === 'or' || type === 'true') {
            me.updateTree();
            return;
        }

        if (type === 'stream') {
            me.form.streamCombo.searchStore.load({
                params: { ids: Ext.JSON.encode([value]) },
                callback: function(records) {
                    var record = records[0];

                    me.form.streamCombo.setValue(record);
                }
            });
            me.form.value.setValue(value);

            return;
        }

        me.form.fieldCombo.bindStore(me[type + 'Store']);

        if (!field) {
            field = me.form.fieldCombo.getStore().getAt(0).get('field');
        }
        me.form.fieldCombo.setValue(field);

        me.form.operatorCombo.bindStore(me.getOperatorStore(type, field));
        if (!operator) {
            operator = me.form.operatorCombo.getStore().getAt(0).get('operator');
        }

        // Show or hide the value-field and the search-button depending on the field-type.
        me.form.value[me.ruleDefinition.fields[type][field] === 'boolean' ? 'hide' : 'show']();
        if (me.ruleDefinition.fields[type][field] === 'boolean' || type === 'basket') {
            me.form.searchButton['hide']();
        } else {
            me.form.searchButton['show']();
        }

        me.form.operatorCombo.setValue(operator);
        if (value) {
            me.form.value.setValue(value);
        }
        me.updateTree();
    },

    updateTree: function() {
        var me = this;

        if (me.form.typeCombo.getValue() === null) {
            return;
        }

        me.treeController.reconfigureNode(me.selectedNode, me.form.typeCombo.getValue(), [
            me.form.fieldCombo.getValue(),
            me.form.operatorCombo.getValue(),
            me.form.value.getValue()
        ]);
    },

    createStores: function() {
        var me = this,
            data = [],
            translator = Ext.create('Shopware.apps.SwagTreeRule.components.Translator', {});

        translator.init();

        Ext.each(Object.keys(me.ruleDefinition.fields['product']), function(field) {
            data.push({
                'field': field,
                'name': translator.translateSnippet(field)
            });
        });
        me.productStore = Ext.create('Ext.data.Store', {
            fields: ['field', 'name'],
            data: data
        });

        data = [];
        Ext.each(Object.keys(me.ruleDefinition.fields['basket']), function(field) {
            data.push({
                'field': field,
                'name': translator.translateSnippet(field)
            });
        });
        me.basketStore = Ext.create('Ext.data.Store', {
            fields: ['field', 'name'],
            data: data
        });

        data = [];
        Ext.each(Object.keys(me.ruleDefinition.fields['customer']), function(field) {
            data.push({
                'field': field,
                'name': translator.translateSnippet(field)
            });
        });
        me.customerStore = Ext.create('Ext.data.Store', {
            fields: ['field', 'name'],
            data: data
        });
    },

    show: function(record) {
        var me = this;

        me.selectedNode = record;

        me.form.typeCombo.select(record.raw.ruleNormalized);

        me.prepareFields(
            record.raw.ruleNormalized.replace('CompareRule', ''),
            record.raw.ruleConfig ? record.raw.ruleConfig[0] : undefined,
            record.raw.ruleConfig ? record.raw.ruleConfig[1] : undefined,
            record.raw.ruleConfig ? record.raw.ruleConfig[2] : undefined,
            record.raw.id === 'root'
        );

        me.form.showElements();
    }
});
// {/block}
