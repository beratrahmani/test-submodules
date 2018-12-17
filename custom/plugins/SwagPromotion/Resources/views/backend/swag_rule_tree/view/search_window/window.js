
//{block name="backend/swag_rule_tree/view/search_window/window"}
Ext.define('Shopware.apps.SwagRuleTree.view.SearchWindow.Window', {
    extend: 'Enlight.app.Window',

    field: undefined,

    callback: undefined,

    layout: 'fit',

    title: '{s namespace="backend/swag_promotion/snippets" name="searchWindowTitle"}Search...{/s}',

    selections: {},

    mainField: undefined,

    preSelected: [],

    initComponent: function () {
        var me = this;

        me.dockedItems = [
            {
                xtype: 'toolbar',
                ui: 'shopware-ui',
                dock: 'top',
                cls: 'shopware-toolbar',
                items: me.createSearchBar()
            }
        ];

        me.bbar = me.createToolbar();

        me.selections = {};

        me.preSelected = me.preSelected.split('|').map(function (str) {
            return str.trim();
        });

        me.callParent(arguments);

        Ext.Ajax.request({
            url: '{url controller=SwagPromotionSearch action=search}',
            params: {
                field: me.field
            },
            success: function (response) {
                var text = response.responseText,
                    decoded = Ext.decode(text);

                me.createStore(decoded);

            },
            failure: function () {
                console.log(arguments);
            }
        });

    },

    createGrid: function (store, definition) {
        var me = this,
            curColumn,
            columns = [],
            translations = me.getFieldTranslations();

        for (var i = 0; i < definition.length; i++) {
            if (definition[i] == 'internalId') {
                continue;
            }
            curColumn = {
                dataIndex: definition[i],
                text: translations[definition[i]]
            };

            if (definition[i] == 'id') {
                curColumn.width = 80;
            } else {
                curColumn.flex = 1;
            }

            columns.push(curColumn);
        }

        me.grid = Ext.create('Ext.grid.Panel', {
            columns: columns,
            store: store,
            dockedItems: [
                {
                    xtype: 'pagingtoolbar',
                    store: store,
                    dock: 'bottom',
                    displayInfo: true,
                    listeners: {
                        beforechange: function () {
                            me.backupSelectionModel();
                            return true;
                        },
                        change: function () {
                            me.restoreSelectionModel();
                        }
                    }
                }
            ],
            selModel: me.getGridSelModel()
        });

        return me.grid;
    },

    restoreSelectionModel: function () {
        var me = this,
            selectionModel = me.grid.getSelectionModel(),
            record,
            i,
            store = me.grid.getStore(),
            currentPage = me.grid.getStore().currentPage;

        var newRecordsToSelect = [];
        for (i = 0; i < me.preSelected.length; i++) {
            record = store.findRecord(me.mainField, me.preSelected[i], 0, false, true);
            if (!Ext.isEmpty(record)) {
                me.preSelected.splice(i--, 1);
                newRecordsToSelect.push(record);
            }
        }

        if (me.selections[currentPage]) {
            for (i = 0; i < me.selections[currentPage].length; i++) {
                record = store.getById(me.selections[currentPage][i].getId());
                if (!Ext.isEmpty(record)) {
                    newRecordsToSelect.push(record);
                }
            }
        }

        selectionModel.select(newRecordsToSelect);
        me.backupSelectionModel();
    },

    backupSelectionModel: function () {
        var me = this,
            selectionModel = me.grid.getSelectionModel(),
            currentPage = me.grid.getStore().currentPage;

        me.selections[currentPage] = selectionModel.getSelection();
    },

    getGridSelModel: function () {
        var me = this;

        return me.selectionModel = Ext.create('Ext.selection.CheckboxModel', {});
    },

    createStore: function (data) {
        var me = this,
            definition = Object.keys(data.data[0]),
            fields = [],
            model;

        for (var i = 0; i < definition.length; i++) {
            fields.push({ name: definition[i], type: 'string' });
        }

        me.mainField = definition[0];

        model = Ext.define('PromotionFieldModel', {
            extend: 'Ext.data.Model',
            idProperty: 'internalId',
            fields: fields
        });

        me.mainStore = Ext.create('Ext.data.Store', {
            pageSize: 20,

            model: 'PromotionFieldModel',

            proxy: {
                type: 'ajax',
                api: {
                    read: '{url controller=SwagPromotionSearch action=search}'
                },
                reader: {
                    type: 'json',
                    root: 'data',
                    totalProperty: 'total'
                },
                extraParams: {
                    field: me.field,
                    limit: 20
                }
            }
        });

        me.mainStore.load(function () {
            me.add(me.createGrid(me.mainStore, definition));
            me.restoreSelectionModel();
            me.totalCount = me.mainStore.getTotalCount();
        });
    },

    createToolbar: function () {
        var me = this;

        //create the save button which fire the save event, the save event is handled in the detail controller.
        me.saveButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            name: 'save',
            text: '{s namespace="backend/swag_promotion/snippets" name=searchWindowSaveButton}Save{/s}',
            handler: function () {
                me.backupSelectionModel();

                var pages = Object.keys(me.selections),
                    items = [],
                    result = [];

                Ext.each(pages, function (page) {
                    result = result.concat(me.selections[page]);
                });

                Ext.each(result, function (record) {
                    items.push(record.get(me.mainField));
                });

                if (items.length == 1) {
                    var item = items.shift();
                    me.callback(item);
                } else {
                    me.callback(items.concat(me.preSelected).join(' | '));
                }

                me.destroy();

            }
        });

        //creates the toolbar with a spaces, the cancel and save button.
        return Ext.create('Ext.toolbar.Toolbar', {
            items: [
                { xtype: 'tbfill' },
                me.saveButton
            ]
        });
    },

    createSearchBar: function () {
        var me = this;

        me.searchBox = Ext.create('Ext.form.TextField', {
            emptyText: '{s namespace="backend/swag_promotion/snippets" name=promotionRulesearchButon}search{/s}',
            width: 170,
            cls: 'searchfield',
            checkChangeBuffer: 500,
            listeners: {
                change: function (box, newValue) {
                    me.search(newValue);
                }
            }
        });

        return ['->', me.searchBox];
    },

    search: function (value) {
        var me = this;
        me.grid.getStore().proxy.extraParams.searchTerm = value;
        me.grid.getStore().load();
    },

    getFieldTranslations: function () {
        return {
            'email'             : '{s namespace="backend/swag_promotion/snippets" name=searchWindowEmail}Email{/s}',
            'paymentId'         : '{s namespace="backend/swag_promotion/snippets" name=searchWindowPaymentId}Payment ID{/s}',
            'paymentDescription': '{s namespace="backend/swag_promotion/snippets" name=searchWindowPayment}Payment method{/s}',
            'groupkey'          : '{s namespace="backend/swag_promotion/snippets" name=searchWindowGroupKey}Group key{/s}',
            'accountmode'       : '{s namespace="backend/swag_promotion/snippets" name=searchWindowAccountMode}Account mode{/s}',
            'validateGroupKey'  : '{s namespace="backend/swag_promotion/snippets" name=searchWindowValidation}Validation for customer group{/s}',
            'title'             : '{s namespace="backend/swag_promotion/snippets" name=searchWindowLanguageTitle}Title{/s}',
            'internalcomment'   : '{s namespace="backend/swag_promotion/snippets" name=searchWindowInternalComment}Comment{/s}',
            'countryname'       : '{s namespace="backend/swag_promotion/snippets" name=searchWindowCountry}Country{/s}',
            'stateName'         : '{s namespace="backend/swag_promotion/snippets" name=searchWindowState}State{/s}',
            'company'           : '{s namespace="backend/swag_promotion/snippets" name=searchWindowCompany}Company{/s}',
            'department'        : '{s namespace="backend/swag_promotion/snippets" name=searchWindowDepartment}Department{/s}',
            'salutation'        : '{s namespace="backend/swag_promotion/snippets" name=searchWindowSalutation}Salutation{/s}',
            'street'            : '{s namespace="backend/swag_promotion/snippets" name=searchWindowStreet}Street{/s}',
            'zipcode'           : '{s namespace="backend/swag_promotion/snippets" name=searchWindowZipCode}Zip code{/s}',
            'ustid'             : '{s namespace="backend/swag_promotion/snippets" name=searchWindowVatId}Vat. Id{/s}',
            'city'              : '{s namespace="backend/swag_promotion/snippets" name=searchWindowCity}City{/s}',
            'phone'             : '{s namespace="backend/swag_promotion/snippets" name=searchWindowPhone}Phone number{/s}',
            'birthday'          : '{s namespace="backend/swag_promotion/snippets" name=searchWindowBirthday}Birthday{/s}',
            'description'       : '{s namespace="backend/swag_promotion/snippets" name=searchWindowDescription}Description{/s}',
            'description_long'  : '{s namespace="backend/swag_promotion/snippets" name=searchWindowDescription}Description{/s}',
            'shopName'          : '{s namespace="backend/swag_promotion/snippets" name=searchWindowShopName}Shop name{/s}',
            'shortDescription'  : '{s namespace="backend/swag_promotion/snippets" name=searchWindowShortDescription}Short description{/s}',
            'customernumber'    : '{s namespace="backend/swag_promotion/snippets" name=searchWindowCustomerNumber}Customer number{/s}',
            'firstname'         : '{s namespace="backend/swag_promotion/snippets" name=searchWindowFirstname}First name{/s}',
            'lastname'          : '{s namespace="backend/swag_promotion/snippets" name=searchWindowLastname}Last name{/s}',
            'articleName'       : '{s namespace="backend/swag_promotion/snippets" name=searchWindowArticleName}Article name{/s}',
            'netPrice'          : '{s namespace="backend/swag_promotion/snippets" name=searchWindowNetPrice}Net price{/s}',
            'netPseudoprice'    : '{s namespace="backend/swag_promotion/snippets" name=searchWindowNetPseudoprice}Pseudo net price{/s}',
            'purchaseunit'      : '{s namespace="backend/swag_promotion/snippets" name=searchWindowPurchaseUnit}Purchase unit{/s}',
            'id'                : '{s namespace="backend/swag_promotion/snippets" name=searchWindowId}Id{/s}',
            'ordernumber'       : '{s namespace="backend/swag_promotion/snippets" name=searchWindowOrderNumber}Ordernumber{/s}',
            'additionaltext'    : '{s namespace="backend/swag_promotion/snippets" name=searchWindowVariant}Variant{/s}',
            'from'              : '{s namespace="backend/swag_promotion/snippets" name=searchWindowFrom}From{/s}',
            'to'                : '{s namespace="backend/swag_promotion/snippets" name=searchWindowTo}To{/s}',
            'price'             : '{s namespace="backend/swag_promotion/snippets" name=searchWindowPrice}Price{/s}',
            'pseudoprice'       : '{s namespace="backend/swag_promotion/snippets" name=searchWindowPseudoPrice}Pseudo Price{/s}',
            'baseprice'         : '{s namespace="backend/swag_promotion/snippets" name=searchWindowBasePrice}Base Price{/s}',
            'name'              : '{s namespace="backend/swag_promotion/snippets" name=searchWindowName}Name{/s}',
            'keywords'          : '{s namespace="backend/swag_promotion/snippets" name=searchWindowKeywords}Keywords{/s}',
            'kind'              : '{s namespace="backend/swag_promotion/snippets" name=searchWindowKind}Kind{/s}',
            'instock'           : '{s namespace="backend/swag_promotion/snippets" name=searchWindowInstock}Instock{/s}',
            'stockmin'          : '{s namespace="backend/swag_promotion/snippets" name=searchWindowStockmin}Stockmin{/s}',
            'weight'            : '{s namespace="backend/swag_promotion/snippets" name=searchWindowWeight}Weight{/s}',
            'height'            : '{s namespace="backend/swag_promotion/snippets" name=searchWindowHeight}Height{/s}',
            'length'            : '{s namespace="backend/swag_promotion/snippets" name=searchWindowLength}Length{/s}',
            'width'             : '{s namespace="backend/swag_promotion/snippets" name=searchWindowWidth}Width{/s}',
            'meta_title'        : '{s namespace="backend/swag_promotion/snippets" name=searchWindowMetaTitle}Meta Title{/s}',
            'metaTitle'         : '{s namespace="backend/swag_promotion/snippets" name=searchWindowMetaTitle}Meta Title{/s}',
            'metadescription'   : '{s namespace="backend/swag_promotion/snippets" name=searchWindowMetaDescription}Meta Description{/s}',
            'metakeywords'      : '{s namespace="backend/swag_promotion/snippets" name=searchWindowMetaKeywords}Meta keywords{/s}',
            'cmsheadline'       : '{s namespace="backend/swag_promotion/snippets" name=searchWindowMetaHeadline}Headline{/s}',
            'cmsText'           : '{s namespace="backend/swag_promotion/snippets" name=searchWindowDescription}Description{/s}',
            'ean'               : '{s namespace="backend/swag_promotion/snippets" name=searchWindowEan}EAN{/s}',
            'percent'           : '{s namespace="backend/swag_promotion/snippets" name=searchWindowPercent}Percent{/s}',
            'attr1'             : '{s namespace="backend/swag_promotion/field_translations" name=attr1 }Attribute 1{/s}',
            'attr2'             : '{s namespace="backend/swag_promotion/field_translations" name=attr2 }Attribute 2{/s}',
            'attr3'             : '{s namespace="backend/swag_promotion/field_translations" name=attr3 }Attribute 3{/s}',
            'attr4'             : '{s namespace="backend/swag_promotion/field_translations" name=attr4 }Attribute 4{/s}',
            'attr5'             : '{s namespace="backend/swag_promotion/field_translations" name=attr5 }Attribute 5{/s}',
            'attr6'             : '{s namespace="backend/swag_promotion/field_translations" name=attr6 }Attribute 6{/s}',
            'attr7'             : '{s namespace="backend/swag_promotion/field_translations" name=attr7 }Attribute 7{/s}',
            'attr8'             : '{s namespace="backend/swag_promotion/field_translations" name=attr8 }Attribute 8{/s}',
            'attr9'             : '{s namespace="backend/swag_promotion/field_translations" name=attr9 }Attribute 9{/s}',
            'attr10'            : '{s namespace="backend/swag_promotion/field_translations" name=attr10 }Attribute 10{/s}',
            'attr11'            : '{s namespace="backend/swag_promotion/field_translations" name=attr11 }Attribute 11{/s}',
            'attr12'            : '{s namespace="backend/swag_promotion/field_translations" name=attr12 }Attribute 12{/s}',
            'attr13'            : '{s namespace="backend/swag_promotion/field_translations" name=attr13 }Attribute 13{/s}',
            'attr14'            : '{s namespace="backend/swag_promotion/field_translations" name=attr14 }Attribute 14{/s}',
            'attr15'            : '{s namespace="backend/swag_promotion/field_translations" name=attr15 }Attribute 15{/s}',
            'attr16'            : '{s namespace="backend/swag_promotion/field_translations" name=attr16 }Attribute 16{/s}',
            'attr17'            : '{s namespace="backend/swag_promotion/field_translations" name=attr17 }Attribute 17{/s}',
            'attr18'            : '{s namespace="backend/swag_promotion/field_translations" name=attr18 }Attribute 18{/s}',
            'attr19'            : '{s namespace="backend/swag_promotion/field_translations" name=attr19 }Attribute 19{/s}',
            'attr20'            : '{s namespace="backend/swag_promotion/field_translations" name=attr20 }Attribute 20{/s}',
            'additional_address_line1'  : '{s namespace="backend/swag_promotion/snippets" name=searchWindowAdditionalAddressLine1}Additional address line 1{/s}',
            'additional_address_line2'  : '{s namespace="backend/swag_promotion/snippets" name=searchWindowAdditionalAddressLine2}Additional address line 2{/s}'
        }
    }
});
//{/block}
