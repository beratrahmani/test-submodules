//

//{namespace name="backend/advisor/main"}
//{block name="backend/advisor/view/details/questions/window"}
Ext.define('Shopware.apps.Advisor.view.details.questions.Window', {
    extend: 'Shopware.window.Detail',
    alias: 'widget.Question',
    title: '{s name="question_window_title"}Create / edit a question{/s}',

    // Preventing to open more than one detail window at the same time
    modal: true,
    minimizable: false,

    snippets: {
        fillGridTitle: '{s name="fill_grid_title"}Fill grid?{/s}',
        fillGridMessage: '{s name="fill_grid_message"}Do you want to leave the Grid unfilled? No answer choices are displayed!{/s}',
        validTitle: '{s name="not_valid_question_title"}Discard data?{/s}',
        validMessage: '{s name="not_valid_question_message"}Your question doesn\'t match the requirements. Do you want to discard the data?{/s}',
        priceSliderValidTitle: '{s name="invalid_price_slider_title"}Invalid data!{/s}',
        priceSliderValidMessage: '{s name="invalid_price_slider_message"}The values for the minimum and the maximum price must be given for the price-slider. Do you want to add those values now?{/s}'
    },

    configure: function() {
        return {
            translationKey: 'advisorQuestion'
        }
    },

    /**
     * @overwrite
     */
    onSave: function () {
        var me = this;

        me.formPanel.getForm().updateRecord(me.record);

        if (!me.validatePriceSlider()) {
            me.showMessage(
                me.snippets.priceSliderValidTitle,
                me.snippets.priceSliderValidMessage,
                me.priceSliderMsgCallback
            );

            return;
        }

        if (!me.validateRecord()) {
            me.showMessage(
                me.snippets.validTitle,
                me.snippets.validMessage,
                me.defaultMsgCallback
            );

            return;
        }

        //  This is necessary on create a new Question (close it and reopen without save)
        //  if the internalId in store the question is not added again to the Store
        if (me.record.get('id') == null && !me.isInternalIdInStore(me.record.internalId, me.listing.getStore())) {
            me.listing.getStore().add(me.record);
        }

        if (me.needToShowFillGridMessage()) {
            me.showMessage(
                me.snippets.fillGridTitle,
                me.snippets.fillGridMessage,
                me.imageQuestionFallback
            );

            return;
        }

        me.destroy();
    },

    /**
     * @param { string } btn
     */
    defaultMsgCallback: function (btn) {
        var me = this;

        if (btn === 'yes') {
            me.record.reject();
            me.destroy();
        }
    },

    /**
     * @param { Object } btn
     */
    imageQuestionFallback: function (btn) {
        var me = this;

        if (btn === 'yes') {
            me.destroy();
        }
    },

    /**
     * Callback which is used, when the price-slider configuration is invalid.
     *
     * @param { string } btn
     */
    priceSliderMsgCallback: function (btn) {
        var me = this;

        if (btn === 'no') {
            me.destroy();
        }
    },

    /**
     * @param { string } title
     * @param { string } message
     * @param { function } callback
     */
    showMessage: function (title, message, callback) {
        var me = this;

        Ext.Msg.show({
            title: title,
            msg: message,
            closable: false,
            buttons: Ext.Msg.YESNO,
            fn: Ext.bind(callback, me)
        });
    },

    /**
     * @returns { boolean }
     */
    needToShowFillGridMessage: function () {
        var me = this,
            oneHasTargetId = false;

        if (me.record.get('template').indexOf('image') > -1) {
            me.record.getAnswers().each(function (answer) {
                if (answer.get('targetId') != '') {
                    oneHasTargetId = true;
                }
            });
        } else {
            oneHasTargetId = true;
        }

        return !oneHasTargetId;
    },

    /**
     * @overwrite
     *
     * @returns { * }
     */
    createCancelButton: function () {
        var me = this,
            button = me.callParent(arguments);

        button.hide();

        return button;
    },

    /**
     * @overwrite
     *
     * @returns { * }
     */
    createSaveButton: function () {
        var me = this,
            button = me.callParent(arguments);

        button.text = 'OK';

        return button;
    },

    /**
     * @param { int | string } internalId
     * @param { Ext.data.store } store
     *
     * @returns { boolean }
     */
    isInternalIdInStore: function (internalId, store) {
        var isInternalIdInStore = false;

        store.each(function (item) {
            if (item.internalId == internalId) {
                isInternalIdInStore = true;
            }
        });

        return isInternalIdInStore;
    },

    /**
     * returns boolean and checks if question and type is filled
     *
     * @returns { boolean }
     */
    validateRecord: function () {
        var me = this,
            valid = true;

        if (me.isNullOrEmptyString(me.record.get('question'))) {
            valid = false;
        }

        if (me.isNullOrEmptyString(me.record.get('type'))) {
            valid = false;
        }

        return valid;
    },

    /**
     * Validates if a price-slider-question is properly configured.
     *
     * @returns { boolean }
     */
    validatePriceSlider: function () {
        var me = this,
            isValid = true;

        if (me.record.get('type') !== 'price') {
            return true;
        }

        if (me.record.get('template') !== 'range_slider') {
            return true;
        }

        me.record.getAnswers().each(function (answer) {
            if (!answer.get('answer')) {
                isValid = false;
            }
        });

        return isValid;
    },

    /**
     * checks if the string is NULL or empty
     *
     * @param { string } str
     * @returns { boolean }
     */
    isNullOrEmptyString: function (str) {
        str = [str, ''].join('');
        return str === null || str.match(/^ *$/) !== null;
    }
});
//{/block}