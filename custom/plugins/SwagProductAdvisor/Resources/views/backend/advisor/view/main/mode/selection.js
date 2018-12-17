//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/main/mode/selection"}
Ext.define('Shopware.apps.Advisor.view.main.mode.Selection', {
    extend: 'Enlight.app.Window',
    alias: 'widget.AdvisorModeSelectionWindow',
    title: '{s name="mode_selection_title"}Berater Typ wählen{/s}',

    layout: 'fit',
    width: 600,
    height: null,

    snippets: {
        sidebarDescription: '{s name="mode_selection_descriptionSidebar"}The Sidebar consultant is perfect for technical ranges. You can specifically for the requirements or application purposes of your customers ask and suggest them the perfect matching products. Customers can, depending on your preferences, at any time to display intermediate results and refine their desires again.{/s}',
        wizardDescription: '{s name="mode_selection_descriptionWizard"}In this product the individual taste and style are important criteria? Based on a visual-based search you quickly prepare your clients with step-by-step advisor the right offer. also via an optional customer registration you will receive important information for other marketing activities.{/s}',
        sidebarModeButtonText: '{s name="mode_selection_createSidebar"}Create sidebar advisor{/s}',
        wizardModeButtonText: '{s name="mode_selection_createWizard"}Create step by step advisor{/s}'
    },

    images: {
        sidebarImage: '{link file="backend/_resources/images/sidebar.jpg"}',
        wizardImage: '{link file="backend/_resources/images/stepbystep.jpg"}'
    },

    selectionResults: {
        sideBarMode: 'sidebar_mode',
        wizardMode: 'wizard_mode'
    },

    /**
     * the initMethod
     */
    initComponent: function () {
        var me = this;

        me.items = me.createSelection();

        me.callParent(arguments);
    },

    /**
     * create the imageContainer by mode
     *
     * @param { string } mode
     * @returns { Ext.container.Container }
     */
    createImage: function (mode) {
        var me = this,
            image;

        if (mode == me.selectionResults.sideBarMode) {
            image = '<img src="' + me.images.sidebarImage + '" width="240" height="180" />';
        } else if (mode == me.selectionResults.wizardMode) {
            image = '<img src="' + me.images.wizardImage + '" width="240" height="180" />';
        }

        return Ext.create('Ext.container.Container', {
            height: 180,
            style: 'margin-left: 15px;',
            html: image
        });
    },

    /**
     * create the button by mode.
     * For each mode we need different text and handler parameter
     *
     * @param { string } mode
     * @returns { Ext.button.Button }
     */
    createButton: function (mode) {
        var me = this,
            button = Ext.create('Ext.button.Button', {
                cls: 'primary',
                style: {
                    marginTop: '15px'
                }
            });

        if (mode == me.selectionResults.sideBarMode) {
            button.text = me.snippets.sidebarModeButtonText;
            button.handler = Ext.bind(me.modeButtonHandler, me, mode, true);
        } else if (mode == me.selectionResults.wizardMode) {
            button.text = me.snippets.wizardModeButtonText;
            button.handler = Ext.bind(me.modeButtonHandler, me, mode, true);
        }

        return button;
    },

    /**
     * @param { Ext.button.Button } button
     * @param { * } e
     * @param { string } mode
     */
    modeButtonHandler: function (button, e, mode) {
        var me = this,
            record = Ext.create('Shopware.apps.Advisor.model.Advisor');

        record.set('mode', mode);

        Ext.create('Shopware.apps.Advisor.view.details.Main', {
            record: record
        }).show();

        me.close();
    },

    /**
     * create the textPane by mode.
     * This create the panel with the description for the mode
     *
     * @param { string } mode
     * @returns { Ext.container.Container }
     */
    createTextPanel: function (mode) {
        var me = this,
            pStart = '<p style="text-align: justify">',
            pEnd = '</p>',
            spacer = '<div style="height: 14px;"></div>',
            container = Ext.create('Ext.container.Container', {
                style: 'margin-top:10px;',
                flex: 1,
                border: 0
            });

        if (mode == me.selectionResults.sideBarMode) {
            container.html = [
                pStart,
                me.snippets.sidebarDescription,
                pEnd,
                spacer
            ].join('');

        } else if (mode == me.selectionResults.wizardMode) {
            container.html = [
                pStart,
                me.snippets.wizardDescription,
                pEnd,
                spacer
            ].join('');
        }

        return container;
    },

    /**
     * @param { * } image
     * @param { * } text
     * @param { * } button
     * @returns { Ext.panel.Panel }
     */
    createContainer: function (image, text, button) {
        return Ext.create('Ext.panel.Panel', {
            flex: 1,
            border: 0,
            layout: {
                type: 'vbox',
                align: 'stretch',
                padding: 15,
                border: 0
            },
            items: [
                image,
                text,
                button
            ]
        });
    },

    /**
     * return the possible mode selection
     * wizardMode
     * sidebarMode
     *
     * @returns { [] }
     */
    createSelection: function () {
        var me = this, hbox, leftContainer, rightContainer;

        leftContainer = me.createContainer(
            me.createImage(me.selectionResults.sideBarMode),
            me.createTextPanel(me.selectionResults.sideBarMode),
            me.createButton(me.selectionResults.sideBarMode)
        );

        rightContainer = me.createContainer(
            me.createImage(me.selectionResults.wizardMode),
            me.createTextPanel(me.selectionResults.wizardMode),
            me.createButton(me.selectionResults.wizardMode)
        );

        hbox = Ext.create('Ext.panel.Panel', {
            border: 0,
            layout: {
                type: 'hbox',
                align: 'stretch'
            },
            items: [
                leftContainer,
                rightContainer
            ]
        });

        return [
            hbox
        ];
    }
});
//{/block}