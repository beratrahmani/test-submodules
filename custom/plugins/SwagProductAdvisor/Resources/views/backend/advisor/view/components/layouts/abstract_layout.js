//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/components/layouts/singleChoice"}
Ext.define('Shopware.apps.Advisor.view.components.layouts.AbstractLayout', {
    extend: 'Ext.Component',

    /**
     * The gridConfig controls the appearance of the AnswerGrid.
     * 'Shopware.apps.Advisor.view.details.ui.AnswerGrid'
     */
    defaultGridConfig: {
        mediaAllowed: false,
        designerAllowed: false,
        addAnswerAllowed: false,
        editValueAllowed: false,
        answerEditorIsNumberField: false,
        answerSelectionAllowed: false
    },

    /**
     * Create the layout on init the component
     */
    initComponent: function () {
        var me = this;

        me.layout = Ext.create('Shopware.apps.Base.model.ProductBoxLayout', {
            key: me.key,
            label: me.label,
            description: me.description,
            image: me.image
        });

        me.setGridConfig(me.defaultGridConfig);
    },

    /**
     * @returns { Shopware.apps.Base.model.ProductBoxLayout }
     */
    getProductBoxLayoutModel: function () {
        return this.layout;
    },

    /**
     *
     * @returns { { mediaAllowed,
     *              designerAllowed,
     *              addAnswerAllowed,
     *              editValueAllowed,
     *              answerEditorIsNumberField
 *              } }
     */
    getGridConfig: function () {
        var me = this;

        return {
            mediaAllowed: me.layout.mediaAllowed,
            designerAllowed: me.layout.designerAllowed,
            addAnswerAllowed: me.layout.addAnswerAllowed,
            editValueAllowed: me.layout.editValueAllowed,
            answerEditorIsNumberField: me.layout.answerEditorIsNumberField,
            answerSelectionAllowed: me.layout.answerSelectionAllowed
        };
    },

    /**
     * @param { boolean } value
     * @returns { boolean }
     */
    setAnswerSelectionAllowed: function (value) {
        if (!value) {
            return false;
        }

        this.layout.answerSelectionAllowed = value;

        return true;
    },

    /**
     * @param { boolean } value
     * @returns { boolean }
     */
    setAnswerEditorIsNumberField: function (value) {
        if (!value) {
            return false;
        }

        this.layout.answerEditorIsNumberField = value;

        return true;
    },

    /**
     * @param  { boolean } value
     * @returns { boolean }
     */
    setEditValueAllowed: function (value) {
        if (!value) {
            return false;
        }

        this.layout.editValueAllowed = value;

        return true;
    },

    /**

     * @param { boolean } value
     * @returns { boolean }
     */
    setAddAnswerAllowed: function (value) {
        if (!value) {
            return false;
        }

        this.layout.addAnswerAllowed = value;

        return true;
    },

    /**
     * @param { boolean } value
     * @returns { boolean }
     */
    setDesignerAllowed: function (value) {
        if (!value) {
            return false;
        }

        this.layout.designerAllowed = value;

        return true;
    },

    /**
     * @param { boolean } value
     * @returns { boolean }
     */
    setMediaAllowed: function (value) {
        if (!value) {
            return false;
        }

        this.layout.mediaAllowed = value;

        return true;
    },

    /**
     * Needs a config like
     * {
     *      mediaAllowed,
     *      designerAllowed,
     *      addAnswerAllowed,
     *      editValueAllowed,
     *      answerEditorIsNumberField
     * }
     *
     * @param { * } config
     * @returns { boolean }
     */
    setGridConfig: function (config) {
        var me = this;

        if (!config) {
            return false;
        }

        me.layout.mediaAllowed = config.mediaAllowed;
        me.layout.designerAllowed = config.designerAllowed;
        me.layout.addAnswerAllowed = config.addAnswerAllowed;
        me.layout.editValueAllowed = config.editValueAllowed;
        me.layout.answerEditorIsNumberField = config.answerEditorIsNumberField;
        me.layout.answerSelectionAllowed = config.answerSelectionAllowed;

        return true;
    }
});
//{/block}