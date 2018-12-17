
//{namespace name="backend/swag_promotion/main"}
//{block name="backend/swag_rule_tree/view/tree_field"}
Ext.define('Shopware.apps.SwagRuleTree.view.TreeField', {

    extend: 'Ext.form.FieldContainer',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     * @type { String }
     */
    alias: 'widget.shopware-rule-tree',

    /**
     * Defines the component layout.
     * @type { Object }
     */
    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    /**
     * List of classes to mix into this class.
     * @type { Object }
     */
    mixins: [
        'Shopware.model.Helper',
        'Ext.form.field.Base'
    ],

    height: 250,
    productsOnly: false,

    /**
     * Current value of the media field.
     * Can be set over the { @link #setValue } function.
     * To get the value use the { @link #getValue } function.
     */
    value: undefined,

    /**
     * Record of the current selected media object.
     * This property is set through the { @link #requestMediaData } function.
     *
     * @type { Shopware.data.Model }
     */
    record: undefined,

    /**
     * Get the reference to the class from which this object was instantiated. Note that unlike self, this.statics()
     * is scope-independent and it always returns the class from which it was called, regardless of what
     * this points to during run-time.
     *
     * The statics object contains the shopware default configuration for
     * this component. The different shopware configurations are stored
     * within the displayConfig object.
     *
     * @type { object }
     */
    statics: {
        /**
         * The statics displayConfig contains the default shopware configuration for
         * this component.
         * To set the shopware configuration, you can use the configure function and set an object as return value
         *
         * @example
         *      Ext.define('Shopware.apps.Product.view.detail.Media', {
         *          extend: 'Shopware.form.field.Media',
         *          configure: function() {
         *              return {
         *                  selectButtonText: 'Select medium',
         *                  ...
         *              }
         *          }
         *      });
         */
        displayConfig: {},

        /**
         * Static function to merge the different configuration values
         * which passed in the class constructor.
         * @param { Object } userOpts
         * @param { Object } definition
         * @returns Object
         */
        getDisplayConfig: function (userOpts, definition) {
            var config = {};

            if (userOpts && typeof userOpts.configure == 'function') {
                config = Ext.apply({}, config, userOpts.configure());
            }
            if (definition && typeof definition.configure === 'function') {
                config = Ext.apply({}, config, definition.configure());
            }
            config = Ext.apply({}, config, this.displayConfig);

            return config;
        },

        /**
         * Static function which sets the property value of
         * the passed property and value in the display configuration.
         *
         * @param prop
         * @param val
         * @returns boolean
         */
        setDisplayConfig: function (prop, val) {
            var me = this;

            if (!me.displayConfig.hasOwnProperty(prop)) {
                return false;
            }
            me.displayConfig[prop] = val;
            return true;
        }
    },

    /**
     * Override required!
     * This function is used to override the { @link #displayConfig } object of the statics() object.
     *
     * @returns { Object }
     */
    configure: function () {
        return {};
    },

    /**
     * Class constructor which merges the different configurations.
     * @param opts
     */
    constructor: function (opts) {
        var me = this;

        me._opts = me.statics().getDisplayConfig(opts, this);
        me.callParent(arguments);
    },

    /**
     * Helper function to get config access.
     * @param prop string
     * @returns mixed
     * @constructor
     */
    getConfig: function (prop) {
        var me = this;
        return me._opts[prop];
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first, with each initComponent method up the hierarchy
     * to Ext.Component being called thereafter. This makes it easy to implement and, if needed, override the constructor
     * logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class'
     * initComponent method is also called.
     * All config options passed to the constructor are applied to this before initComponent is called, so you
     * can simply access them with this.someOption.
     */
    initComponent: function () {
        var me = this;

        me.items = me.createItems();
        me.callParent(arguments);

        me.ruleDefinition = Ext.create('Shopware.apps.SwagRuleTree.model.Fields', {});

        me.treeController = Ext.create('Shopware.apps.SwagTreeRule.controller.Tree', {
            treeStore: me.treeStore,
            treePanel: me.treePanel
        });

        me.formController = Ext.create('Shopware.apps.SwagTreeRule.controller.Form', {
            form: me.formPanel,
            ruleDefinition: me.ruleDefinition,
            treeController: me.treeController
        });
        me.formController.init();
        me.treeController.init(me.value);

    },

    /**
     * Creates all components for this class.
     * The Shopware.form.field.Media component creates
     * a container for the { @link #resetButton } and { @link #selectButton }.
     * Additionally the media field contains a container to display
     * the current select image.
     * This container contains a { @link #Ext.Img } object.
     *
     * @returns { Array }
     */
    createItems: function () {
        var me = this;

        return [
            me.createTree(),
            me.createFormPanel()
        ];
    },

    createFormPanel: function () {
        var me = this;

        me.formPanel = Ext.create('Shopware.apps.SwagRuleTree.view.Form', {
            productsOnly: me.productsOnly
        });

        return me.formPanel;
    },

    createTreeStore: function () {
        var me = this;

        me.treeStore = Ext.create('Ext.data.TreeStore', {
            autoLoad: false,

            root: {
                expanded: true,
                id: 'root',
                children: []
            },

            constructor: function (config) {
                config.root = Ext.clone(this.root);
                this.callParent([config]);
            }
        });
        return me.treeStore;
    },

    createTree: function () {
        var me = this;

        me.treePanel = Ext.create('Ext.tree.Panel', {
            bodyStyle: { "background-color": "white" },
            region: 'north',
            // height: 150,
            // border: 0,
            title: '{s namespace="backend/swag_promotion/snippets" name=promotionTitleRuleSelection}Rule selection{/s}',
            store: me.createTreeStore(),
            viewConfig: {
                plugins: {
                    ptype: 'swagruletree'
                },
                listeners: {
                    drop: function (node, data, overModel, dropPosition, eOpts) {
                        if (dropPosition === 'append') {
                            if (overModel.get('type') !== 'iteration') {
                                overModel.set('type', '');
                                overModel.set('iconCls', '');
                            }
                        }

                        me.treeStore.sync({
                            success: function () {
                                // fix selection
                                me.treePanel.getSelectionModel().deselectAll(true);
                                me.treePanel.expand();
                                me.treePanel.getSelectionModel().select(me.treeStore.getById(data.records[0].get('id')));
                            }
                        });
                    }
                }
            },
            rootVisible: true,
            width: 450,
            useArrows: true,
            expandChildren: true,
            dockedItems: [
                {
                    itemId: 'toolbar',
                    xtype: 'toolbar',
                    // dock: 'bottom',
                    style: {
                        borderRight: '1px solid #A4B5C0',
                        borderLeft: '1px solid #A4B5C0',
                        borderTop: '1px solid #A4B5C0'
                    },
                    items: [
                        {
                            itemId: 'createRule',
                            text: '{s namespace="backend/swag_promotion/snippets" name=promotionTitleNewRule}New rule{/s}',
                            disabled: false,
                            handler: function () {
                                var node = me.selectedNode;
                                if (me.selectedNode.raw.ruleNormalized != 'and' && me.selectedNode.raw.ruleNormalized != 'or') {
                                    node = me.selectedNode.parentNode;
                                }

                                newNode = node.appendChild({
                                    expanded: true,
                                    leaf: true,
                                    text: '{s namespace="backend/swag_promotion/snippets" name=promotionTitleNewRule}New rule{/s}',
                                    rule: '',
                                    ruleNormalized: '',
                                    ruleConfig: []
                                });
                            }
                        },
                        {
                            itemId: 'deleteSelected',
                            text: '{s namespace="backend/swag_promotion/snippets" name=promotiondeleteButton}Delete{/s}',
                            disabled: true,
                            handler: function () {
                                me.selectedNode.destroy();
                                me.treePanel.getSelectionModel().select(0);
                                me.formPanel.showElements();
                            }
                        }
                    ]
                }
            ],
            listeners: {
                select: {
                    fn: function (view, record, item, index, event) {
                        me.selectedNode = record;
                        me.selectedIndex = item;
                        me.formController.show(record);

                        var toolbar = this.dockedItems.get('toolbar');

                        toolbar.items.get('createRule').setDisabled(false);
                        toolbar.items.get('deleteSelected').setDisabled(false);
                    }
                }
            }
        });

        return me.treePanel;
    },

    /**
     * Returns the current value of the media field.
     * This function can returns different values:
     *  - undefined => No image is selected
     *  - string => Path of the media model (If the { @link #valueField } parameter is set to `path`)
     *  - int => Id of the media model (If the { @link #valueField } parameter is set to `id`)
     *
     * @returns { string|undefined|int }
     */
    getValue: function () {
        var me = this;
        return me.treeController.getTreeJson();
    },

    /**
     * Sets the current value of the media field.
     * This function is used by the { @link Ext.form.Base } object
     * to load a record into the form panel.
     *
     * @param value
     */
    setValue: function (value) {
        var me = this;

        if (!value) {
            value = '{literal}{"and":{"true1":[]}}{/literal}';
        }

        if (value !== me.value) {
            me.treeController.buildTree(value);
            if (!me.selectedIndex) {
                me.selectedIndex = 0;
            }
            me.treePanel.getSelectionModel().select(me.selectedIndex);
        }

        this.value = value;
    },

    /**
     * This function is used if an { @link Ext.data.Model } will be
     * updated with the form data.
     * The function has to return an object with the values which will
     * be updated in the model.
     *
     * @returns { Object }
     */
    getSubmitData: function () {
        var me = this;

        var value = {};
        value[this.name] = me.treeController.getTreeJson();
        return value;
    }

});
//{/block}
