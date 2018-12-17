//{namespace name=backend/plugins/b2b_debtor_plugin}
Ext.define('Shopware.apps.b2bOffer.controller.Main', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'listingWindow', selector: 'Offer-list-window' },
    ],

    init: function () {
        var me = this;

        me.control({
            'offer-detail-positions-list': {
                savePosition: this.savePosition,
                deletePosition: this.deletePosition,
                selectedArticle: this.getArticleData,
                validateQuantity: this.validateQuantity,
            },
            'b2bOffer-detail-container': {
                saveExpiredDate: this.saveExpiredDate,
                debtorLink: this.debtorLink
            },
            'offer-detail-accept-detail': {
                acceptOffer: this.acceptOffer
            },
            'offer-detail-decline-detail': {
                declineOffer: this.declineOffer,
            },
            'offer-listing-grid': {
                debtorLink: this.debtorLink
            },
            'Offer-detail-positions-discount': {
                changeDiscount: this.changeDiscount
            },
            'Offer-detail-window-additional': {
                addComment: this.addComment
            }
        });

        me.mainWindow = me.getView('list.Window').create({}).show();
    },

    savePosition: function(e, window) {
        e.record.set('price', e.newValues.price);
        e.record.set('name', e.newValues.name);
        e.record.set('referenceNumber', e.newValues.referenceNumber);
        e.record.set('discountAmountNet', e.newValues.discountAmountNet);

        this.updatePrices(window);
    },

    changeDiscount: function(record, newValue, window) {
        record.set('discountValueNet', newValue);
        record.commit();

        this.updatePrices(window);
    },

    addComment: function(form, store, callback) {
        if (form.isValid()) {
            form.submit({
                success: function () {

                    store.load(callback);
                    form.reset();
                }
            });
        }
    },

    checkForDecline: function (record, window) {
        var me = this;

        if (record.data.acceptedByUserAt || record.data.acceptedByAdminAt) {
            Ext.Ajax.request({
                url: '{url module=backend controller=b2boffer action=declineOffer}',
                method: 'POST',
                async: false,
                params: function () {
                    return {
                        id: record.data.id
                    };
                },
                success: function () {
                    me.getListingWindow().gridPanel.getStore().load();
                    window.declineButton.hide();
                    window.acceptButton.hide();
                    window.sendOfferButton.show();
                }
            });
        }
    },

    updatePrices: function(window) {
        var container = window.up('Offer-detail-positions-container');

        var lineItems = container.positions.store.data.items;

        var data = container.discount.record.data;

        var rawData = [];

        lineItems.forEach(function (lineItem) {
            rawData.push(lineItem.data);
        });

        data.lineItems = Ext.encode(rawData);

        Ext.Ajax.request({
            url: '{url module=backend controller=b2boffer action=calculatePrice}',
            method: 'POST',
            async: false,
            params: data,
            success: function (response) {

                var data = Ext.decode(response.responseText),
                    itemList = container.offer.items.items[0].items.items[1].items.items[0].items.items;

                itemList[5].setValue(data.data['listAmountNet']);
                itemList[6].setValue(data.data['discountAmountNet']);
                itemList[7].setValue(lineItems.length);
                container.discount.items.items[0].maxValue = data.data['discountAmountNet'] + container.discount.items.items[0].value;
            }
        });
    },

    getArticleData: function(number, quantity, action) {
        var data = {
            'referenceNumber': number,
            'quantity': quantity
        };

        return Ext.Ajax.request({
            url: '{url module=backend controller=b2bofferlineitemreference action=getProductPriceForItemAndQuantity}',
            method: 'POST',
            async: false,
            params: data,
            success: function (response) {
                var reference = Ext.decode(response.responseText)['data'];

                action(reference);
            }
        });
    },

    validateQuantity: function(event, me) {
        var data = {
            'offerId': me.record.data.id,
            'referenceNumber': event.newValues.referenceNumber,
            'quantity': event.newValues.quantity
        };

        var response = Ext.Ajax.request({
            url: '{url module=backend controller=b2bofferlineitemreference action=validateEntity}',
            method: 'POST',
            async: false,
            params: data,
            success: function (response) {
                return response
            }
        });

        var json = Ext.decode(response.responseText);

        if (json['quantity']) {
            Shopware.Notification.createGrowlMessage(
                '{s name="Info"}Info{/s}',
                '{s name="InvalidQuantity"}The minimum, maximum or graduation of your quantity is not valid.{/s}',
                '{s name="Offer"}Offer{/s}'
            );
        }

        if (json['referenceNumber']) {
            Shopware.Notification.createGrowlMessage(
                '{s name="Info"}Info{/s}',
                '{s name="ProductNotFound"}Product not found.{/s}',
                '{s name="Offer"}Offer{/s}'
            );
        }

        return json['valid'];
    },

    deletePosition: function (editor, rowIndex, record, window) {
        var me = this;

        editor.store.removeAt(rowIndex);

        window.up('Offer-detail-positions-container').discount.items.items[0].setValue(0);

        me.updatePrices(window);

        me.checkForDecline(record, window);
    },

    saveExpiredDate: function (expiredAt, offerId, dateField, timeField, window) {
        var statusChanged = false;

        Ext.Ajax.request({
                url: '{url module=backend controller=b2boffer action=updateOfferExpiredDate}',
                method: 'POST',
                async: false,
                params: {
                        'expiredAt': expiredAt,
                        'id': offerId
                    },
                success: function (response) {
                    var jsonResponse = Ext.decode(response.responseText),
                        expiredAt = jsonResponse.data.expiredAt;

                    if (!expiredAt) {
                        dateField.setValue(null);
                        timeField.setValue(null);
                        return;
                    }

                    var date = new Date(expiredAt.date);
                    dateField.setValue(date);
                    timeField.setValue(date);

                    statusChanged = window.record.get('status') != jsonResponse.data.status;
                }
        });

        if (statusChanged) {
            this.getListingWindow().gridPanel.getStore().load();
            window.up('Offer-detail-window').close();
        }
    },

    declineOffer: function (record, window) {
        Ext.Ajax.request({
            url: '{url module=backend controller=b2boffer action=declineOffer}',
            method: 'POST',
            async: false,
            params: function () {
                return {
                    id: record.data.id
                };
            }
        });

        this.getListingWindow().gridPanel.getStore().load();

        window.close();
    },

    acceptOffer: function (record, window) {
        var me = this;

        window.detailForm.positions.store.getProxy().setExtraParam('offer_id', record.data.id);

        var error = false;

        window.detailForm.positions.store.sync({
            success: function (batch) {

                batch.operations.forEach(function (operation) {
                    var jsonResponse = Ext.decode(operation.response.responseText);

                    if (!jsonResponse.success) {
                        me.ValidationErrorNotification(jsonResponse.error);
                        error = true;
                    }

                    if (jsonResponse.discountMessage) {
                        error = true;
                        Shopware.Notification.createGrowlMessage(
                            '{s name="Info"}Info{/s}',
                            '{s name="DiscountGreaterThanAmountMessage"}The discount has been removed, because the total discount was greater than the order amount.{/s}',
                            '{s name="Offer"}Offer{/s}'
                        );
                    }
                });
            }
        });

        Ext.Ajax.request({
            url: '{url module=backend controller=b2boffer action=updateDiscount}',
            method: 'POST',
            async: false,
            params: function () {
                return {
                    id: record.data.id,
                    discount: record.data.discountValueNet
                };
            },
            success: function (response) {
                var jsonResponse = Ext.decode(response.responseText);

                if (!jsonResponse.success) {
                    me.ValidationErrorNotification(jsonResponse.error);
                    error = true;
                }

                if (jsonResponse.discountMessage) {
                    error = true;
                    Shopware.Notification.createGrowlMessage(
                        '{s name="Info"}Info{/s}',
                        '{s name="DiscountGreaterThanAmountMessage"}The discount has been removed, because the total discount was greater than the order amount.{/s}',
                        '{s name="Offer"}Offer{/s}'
                    );
                }
            }
        });

        if(error) {
            return;
        }

        Ext.Ajax.request({
            url: '{url module=backend controller=b2boffer action=acceptOffer}',
            method: 'POST',
            async: false,
            params: function () {
                return {
                    id: record.data.id
                };
            },
            success: function (response) {
                var jsonResponse = Ext.decode(response.responseText);

                if (!jsonResponse.success) {
                    me.ValidationErrorNotification(jsonResponse.error);
                    error = true;
                }

                me.getListingWindow().gridPanel.getStore().load();

                window.close();
            }
        });
    },

    debtorLink: function (record) {
        Ext.Ajax.request({
            url: '{url module=backend controller=b2boffer action=fetchDebtorIdByOfferId}',
            method: 'POST',
            params: function () {
                return {
                    id: record.data.id
                };
            },
            success: function (response) {
                var jsonResponse = Ext.decode(response.responseText);

                Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.Customer',
                    action: 'detail',
                    params: {
                        customerId: jsonResponse.debtorId
                    }
                });
            }
        });
    },

    ValidationErrorNotification: function (error) {
        if (Ext.isArray(error)) {
            var errors = '';
            Ext.each(error, function (item) {
                errors += item + "\r\n";
            });
            error = errors;
        }

        Shopware.Notification.createGrowlMessage(
            '{s name="Error"}Error{/s}',
            error,
            '{s name="Offer"}Offer{/s}'
        );
    },
});
