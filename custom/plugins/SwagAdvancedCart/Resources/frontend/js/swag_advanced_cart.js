;(function($, undefined) {
    $.plugin('swAdvancedCart', {
        alias: 'advancedCart',

        /**
         * Defines the plugin defaults
         * Override these to use own classes by adding data-attributes to the element, which triggered the plugin
         * e.g. data-save-cart
         */
        defaults: {
            /**
             * Selector of the element which has to be clicked to open the "add to wishlist"-modal on the detail-page.
             */
            openAddWishListSelector: '*[data-open-wishlist-modal=true]',

            /**
             * Selector of the button which has to be clicked to trigger the "save cart as wishlist"-functionality.
             */
            saveCartSelector: '.save-cart--button',

            /**
             * Selector of the element which has to be clicked to slide down the list-information on the wishlist-listing.
             */
            openWishListDataSelector: '.list-container--row',

            /**
             * Selector of the element which has to be changed to change the public-state of a list.
             * Should be a checkbox.
             */
            changePublicStateSelector: '.list-container--publish-check',

            /**
             * Selector of the element which has to be clicked to open the "confirm"-modal to share a list via mail.
             */
            openConfirmModalSelector: '.manage-container--delete',

            /**
             * Selector of the element which has to be clicked to show the rename-textfield.
             */
            renameListSelector: '.manage-container--rename',

            /**
             * Selector of the element which has to trigger the focusout- and the keyup-event for the rename-textfield disappear again.
             * After this element triggered one of the events above, the rename-function will be called.
             */
            nameFieldSelector: '.list-container--name-input',

            /**
             * Selector of the element which has to be clicked to open the facebook-window.
             */
            selectFacebookSelector: '.select-item--facebook',

            /**
             * Selector of the element which has to be clicked to open the twitter-window.
             */
            selectTwitterSelector: '.select-item--twitter',

            /**
             * Selector of the element which has to be clicked to open the google-plus-window.
             */
            selectGooglePlusSelector: '.select-item--google-plus',

            /**
             * Selector of the element which has to be clicked to open the mail-modal.
             */
            selectMailSelector: '.select-item--mail',

            /**
             * Selector of the element which has to be clicked to an product to a list.
             * Mostly a button.
             */
            addArticleButtonSelector: '.add-article--button',

            /**
             * Selector of the element which has to be clicked to confirm the deletion of a list.
             * There is no more check after this element got clicked.
             */
            confirmDeleteButtonSelector: '.js--modal .modal-btn-container--btn',

            /**
             * Selector of the element which has to be clicked to confirm adding an product to a list.
             * There is no more check after this element got clicked.
             */
            submitAddToWishListSelector: '.js--modal .add-wishlist--button',

            /**
             * Selector of the form which has to be submitted to add an product to a wishlist.
             */
            addToWishListInputSelector: '.add-wishlist--name',

            /**
             * Selector of the button on the confirm- and cart-page, which has to be clicked to create a new wishlist from the current cart.
             */
            saveWishListButtonSelector: '.save-wishlist--button',

            /**
             * Selector of the input-field on the confirm- and cart-page, which contains the name for the new wishlist.
             */
            saveWishListInputSelector: '.save-wishlist--input',

            /**
             * Selector of the select-options on the confirm- and cart-page, which has to be clicked to load an existing list into the cart.
             */
            listSelectSelector: '.load-wishlist--select',

            /**
             * Selector of the button, which has to be clicked to send the share-mail.
             */
            shareMailButtonSelector: '.cart--modal-share-btn',

            /**
             * Selector of the select-box, which contains the quantities for the wishlist-items.
             */
            cartQuantitySelector: '.advancedCartQuantity',

            /**
             * Selector of the button, which has to be clicked to remove product from wishlist.
             */
            cartProductRemoveSelector: '.note--delete',

            /**
             * Selector of an active quick-view container.
             */
            quickViewActiveSelector: '.quick-view.is--active',

            /**
             * Selector of the view-container inside the quick-view.
             */
            quickViewContainerSelector: '.quick-view--view',

            /**
             * Regex to replace the last /{ number | id } of the add to Cart button Url
             * This Regex find the last "/0123456789" of the url
             */
            defaultUrlSuffixRegEx: /\/[0-9]+$/,

            /**
             * Selector for the product search text field
             */
            productSearchFieldSelector: '.add-article--text-field',

            /**
             * Selector for the product list container
             */
            listContainerSelector: '.list-container--content',

            /**
             * Selector for the "Bundle relation" message
             */
            bundleMessageSelector: '.cart--header-info-bundle'
        },

        /**
         * Plugin constructor
         */
        init: function() {
            var me = this;

            me.isSecure = window.location.protocol === 'https:';

            me.applyDataAttributes();
            me.registerEvents();
            me.initSlider();
            me.initAutoComplete();
        },

        /**
         * Method to register all needed events
         */
        registerEvents: function() {
            var me = this,
                $body = $('body');

            me._on(me.opts.saveCartSelector, 'click', $.proxy(me.onSaveCart, me));
            me._on(me.opts.openWishListDataSelector, 'click', $.proxy(me.onClickListContainer, me));
            me._on(me.opts.changePublicStateSelector, 'change', $.proxy(me.onChangePublicState, me));
            me._on(me.opts.openConfirmModalSelector, 'click', $.proxy(me.onOpenConfirmModal, me));
            me._on(me.opts.renameListSelector, 'click', $.proxy(me.onRenameList, me));
            me._on(me.opts.nameFieldSelector, 'focusout', $.proxy(me.onFocusOut, me));
            me._on(me.opts.nameFieldSelector, 'keyup', $.proxy(me.onNameKeyUp, me));
            me._on(me.opts.selectFacebookSelector, 'click', $.proxy(me.onClickSocial, me));
            me._on(me.opts.selectTwitterSelector, 'click', $.proxy(me.onClickSocial, me));
            me._on(me.opts.selectGooglePlusSelector, 'click', $.proxy(me.onClickSocial, me));
            me._on(me.opts.selectMailSelector, 'click', $.proxy(me.onClickMail, me));
            me._on(me.opts.addArticleButtonSelector, 'click', $.proxy(me.onAddArticle, me));
            me._on(me.opts.saveWishListButtonSelector, 'click', $.proxy(me.onCreateNewList, me));
            me._on(me.opts.saveWishListInputSelector, 'keydown', $.proxy(me.onInputKeyDown, me));
            me._on(me.opts.listSelectSelector, 'change', $.proxy(me.onLoadList, me));

            $body.on('change', me.opts.cartQuantitySelector, $.proxy(me.onChangeQuantity, me));
            $body.on('click', me.opts.openAddWishListSelector, $.proxy(me.onOpenDetailModal, me));
            $body.on('click', me.opts.cartProductRemoveSelector, $.proxy(me.onProductDelete, me));
            $body.on('click', me.opts.confirmDeleteButtonSelector, $.proxy(me.onConfirmDelete, me));
            $body.on('click', me.opts.submitAddToWishListSelector, $.proxy(me.onSubmitAddToWishList, me));
            $body.on('click', me.opts.shareMailButtonSelector, $.proxy(me.onSendShareMail, me));
            $body.on('keydown', me.opts.addToWishListInputSelector, $.proxy(me.onPreventEnter, me));
        },

        /**
         * Method to init the wishlist-slider on the detail-page
         */
        initSlider: function() {
            StateManager.addPlugin('*[data-cart-product-slider=true]', 'swProductSlider', {});
        },

        /**
         * Initialize autocomplete-plugin on product search input
         */
        initAutoComplete: function() {
            var me = this;

            $(me.opts.productSearchFieldSelector).autocomplete({
                lookup: function (query, onFetchedSuggestions) {
                    var queryUrl = jsUrlObject.search + '?q=' + query;
                    me.callAjax(queryUrl, { }, me.transformAutocompleteResults, me, $.noop, onFetchedSuggestions);
                },
                formatResult: function (suggestion) {
                    return suggestion.data + ' (' + suggestion.value + ')';
                }
            });
        },

        /**
         * Transform the ajax response for autocomplete suggestions into a format compatible with the autocomplete-plugin
         *
         * @param response
         * @param onFetchedSuggestions Callback from the autocomplete-plugin
         */
        transformAutocompleteResults: function(response, onFetchedSuggestions) {
            var transformedSuggestions = $.map(JSON.parse(response), function (product) {
                return { value: product[0], data: product[1] };
            });

            onFetchedSuggestions({ suggestions: transformedSuggestions});
        },

        /**
         * @param { string } url
         * @param { object|null } parameter
         * @param { function|null } callback
         * @param { object|null } scope
         * @param { function|null } errorCallback
         * @param { object|null } extraParams
         */
        callAjax: function(url, parameter, callback, scope, errorCallback, extraParams) {
            var me = this,
                parameter = parameter || {},
                callback = callback || $.noop,
                scope = scope || me,
                errorCallback = errorCallback || $.noop,
                extraParams = extraParams || null;

            if (!url) {
                throw 'Cannot call ajax request without url';
            }

            if (!CSRF.checkToken()) {
                $.subscribe(
                    'plugin/swCsrfProtection/init',
                    $.proxy(me.csrfAfterInit, me, url, parameter, callback, scope, errorCallback, extraParams)
                );
                CSRF.requestToken();
                return;
            }

            $.ajax({
                type: 'POST',
                url: me.prepareUrl(url),
                data: parameter
            }).done(function(response) {
                callback.call(scope, response, extraParams);
            }).fail(function() {
                errorCallback.call(scope, extraParams);
            });
        },

        /**
         * @param { string } url
         * @param { object|null } parameter
         * @param { function|null } callback
         * @param { object|null } scope
         * @param { function|null } errorCallback
         * @param { object|null } extraParams
         */
        csrfAfterInit: function(url, parameter, callback, scope, errorCallback, extraParams) {
            var me = this;

            $.unsubscribe('plugin/swCsrfProtection/init');
            me.callAjax(url, parameter, callback, scope, errorCallback, extraParams);
        },

        /**
         * This event is triggered when the user sends a mail to share a wishlist
         * @param event
         */
        onChangeQuantity: function(event) {
            var me = this,
                $el = $(event.currentTarget),
                itemID = $el.attr('data-item-id'),
                url = $el.attr('data-quantity-url'),
                parameter = { itemId: itemID, quantity: $el.val() };

            me.callAjax(url, parameter, me.afterQuantityChanged, me, $.noop, $el);
        },

        /**
         * @param { string } response
         * @param { object } $el
         */
        afterQuantityChanged: function(response, $el) {
            var status = $.parseJSON(response),
                priceCt = $el.parents('.note--item').find('.note--price');

            if (status.success) {
                priceCt.html(status.totalPrice);
            }
        },

        /**
         * This event is triggered when the user sends a mail to share a wishlist
         * @param event
         */
        onSendShareMail: function(event) {
            var me = this,
                $el = $(event.currentTarget),
                form = $el.parents('#inner--cart-share');

            event.preventDefault();

            me.callAjax(form.attr('action'), form.serialize(), me.afterSendShareMail, me, $.noop, form);
        },

        /**
         * @param { string } response
         * @param { object } form
         */
        afterSendShareMail: function(response, form) {
            var status = $.parseJSON(response);

            if (status.success) {
                form.prev('.cart--share-alert').slideDown('slow')
                    .delay(3000)
                    .slideUp('slow');
            }
        },

        /**
         * This event is triggered when the user wants to load an existing list into the cart
         *
         * @param event
         */
        onLoadList: function(event) {
            var $select = $(event.currentTarget),
                $selectedEl = $select.find(':selected');

            window.location = $selectedEl.attr('data-wishlist-link');
        },

        /**
         * This event is triggered when the user wants to create a new list from the current cart
         *
         * @param event
         */
        onCreateNewList: function(event) {
            var me = this,
                $el = $(event.currentTarget),
                parent = $el.parents('.save-wishlist--button-container'),
                container = $el.parents('.cart--option-containers'),
                $input = parent.find(me.opts.saveWishListInputSelector),
                cartName = $input.val(),
                url = jsUrlObject.saveWishList,
                parameter = { name: cartName, published: 0 };

            $.loadingIndicator.open();

            me.callAjax(url, parameter, me.afterCreateNewList, me, $.noop, { input: $input, container: container });
        },

        /**
         * @param { string } response
         * @param { object } data
         */
        afterCreateNewList: function(response, data) {
            var me = this,
                status = $.parseJSON(response),
                messageContainerSelector = '.cart--header-alert',
                errorMessageContainerSelector = '.cart--header-error';

            $.loadingIndicator.close();

            if (!status.success) {
                messageContainerSelector = errorMessageContainerSelector;
                if (status.error.length > 0) {
                    alertCt = me.prepareErrorMessage(data, status.error, errorMessageContainerSelector);
                }
            }

            if (status.success) {
                data.input.val('');
            }

            data.container.parent().find(messageContainerSelector).slideDown('slow')
                .delay(5000)
                .slideUp('slow');

            me.requireBundleMessage(status.requireBundleMessage);
        },

        /**
         * @param { object } data
         * @param { Array } errorArray
         * @param { string } errorMessageContainerSelector
         */
        prepareErrorMessage: function(data, errorArray, errorMessageContainerSelector) {
            var message,
                errorMessageContentSelector = '.alert--content',
                errorCode = errorArray[0],
                messageContainer = data.container.parent().find(errorMessageContainerSelector),
                contentContainer = messageContainer.find(errorMessageContentSelector);

            switch (errorCode) {
                case 110: // name is empty
                    message = messageContainer.attr('data-noName');
                    contentContainer.html(message);
                    break;
                case 120: // name already exists
                    message = messageContainer.attr('data-nameExists');
                    contentContainer.html(message);
                    break;
            }
        },

        /**
         * @param { boolean } requireMessage
         */
        requireBundleMessage: function(requireMessage) {
            var me = this;

            if (requireMessage) {
                $(me.opts.bundleMessageSelector).slideDown('slow').delay(3000).slideUp('slow');
            }
        },

        /**
         * Helper method to trigger the submit-button, when the user presses 'enter'
         */
        onInputKeyDown: function(event) {
            var me = this,
                $el = $(event.currentTarget),
                button = $el.next(me.opts.saveWishListButtonSelector);

            // 13 == Enter-Button
            if (event.keyCode == 13) {
                button.click();
            }
        },

        /**
         * Helper method to prevent the form from submitting by pressing 'enter'
         */
        onPreventEnter: function(event) {
            var me = this;

            // 13 == Enter-Button
            if (event.keyCode == 13) {
                me.onSubmitAddToWishList(event);
            }

            $.publish('plugin/swAdvancedCart/onInputKeyUp');

            /** @deprecated changed to 'sw' prefixed version */
            $.publish('plugin/advancedCart/onInputKeyUp');
        },

        /**
         * This event is triggered when the user wants to add an product to a list with the auto-complete function
         * @param event
         */
        onAddArticle: function(event) {
            var me = this,
                $el = $(event.currentTarget);

            me.addArticle($el);

            $.publish('plugin/swAdvancedCart/onAddArticle');
        },

        /**
         * Helper method to create the template for the modal-alert info
         * @param elementClass
         * @param name
         * @param additionalValue
         * @returns {*[]}
         */
        buildAlertElement: function(elementClass, name, additionalValue) {
            var element = $('.' + elementClass);
            element.find('.save-cart--name-placeholder').html(name);
            element.find('.save-cart--value-placeholder').html(additionalValue);

            return element;
        },

        /**
         * Helper method to slide down the success-message.
         *
         * @param alertContainer
         * @param button
         */
        slideOutList: function(alertContainer, button) {
            alertContainer.slideDown('fast')
                .delay('2000')
                .slideUp(function() {
                    button.attr('disabled', false);
                    button.html(jsSnippetObject.add);
                });
        },

        /**
         * Helper method to send the ajax-call for adding an product and then even insert a new product into the template
         * @param $el
         */
        addArticle: function($el) {
            var me = this,
                contentCt = $el.parents(me.opts.listContainerSelector),
                $lastRow = $el.parents('.article-table--add-article'),
                $textField = $lastRow.find(me.opts.productSearchFieldSelector),
                productName = $textField.val(),
                basketId = $lastRow.find('.add-article--hidden').val(),
                url = jsUrlObject.getArticle,
                parameter = { basketId: basketId, articleName: productName },
                extraParams = {
                    el: $el,
                    textField: $textField,
                    contentCt: contentCt,
                    lastRow: $lastRow
                };

            if (!productName) {
                return;
            }

            $el.attr('disabled', true);
            $el.html(jsSnippetObject.pleaseWait);

            me.callAjax(url, parameter, me.afterAddArticle, me, $.noop, extraParams);
        },

        /**
         * @param { string } response
         * @param { object } extraParams
         */
        afterAddArticle: function(response, extraParams) {
            var me = this,
                productTable = extraParams.contentCt.find('.article-table--header'),
                status = $.parseJSON(response),
                alertContainer, alert, $template;

            // Clear Input
            extraParams.textField.val('');

            if (status.success == true) {
                extraParams.contentCt.find('.cart--hidden').removeClass('cart--hidden');

                // Article was added already
                if (status.type === 'readded') {
                    me.slideOutList(extraParams.lastRow.find('.wishlist-alert--readded'), extraParams.el);
                }
                // Normal success
                else if (status.type === 'added') {
                    $template = $(status.template);
                    productTable.after($template);

                    $template.find('.advancedCartQuantity').swSelectboxReplacement();
                    window.picturefill();
                }
            } else {
                // Failed, because the product couldn't be found
                if (status.type !== 'notfound') {
                    return;
                }

                alertContainer = extraParams.lastRow.find('.wishlist-alert--not-found');
                alertContainer.find('.alert--content').html(status.message);

                me.slideOutList(alertContainer, extraParams.el);
            }

            extraParams.el.attr('disabled', false);
            extraParams.el.html(jsSnippetObject.add);

            window.StateManager.updatePlugin('*[data-add-article="true"]', 'swAddArticle');
        },

        /**
         * This method is called when the user wants to share a list via mail
         * @param event
         */
        onClickMail: function(event) {
            var me = this,
                $el = $(event.currentTarget),
                hash = $el.attr('data-hash');

            event.preventDefault();

            if ($el.parent().hasClass('cart--disabled')) {
                return;
            }

            me.openShareModal(hash);

            $.publish('plugin/swAdvancedCart/onClickMail');

            /** @deprecated changed to 'sw' prefixed version */
            $.publish('plugin/advancedCart/onClickMail');
        },

        /**
         * This method opens the modal-window for the share via mail
         * @param hash
         */
        openShareModal: function(hash) {
            var me = this;

            me.callAjax(jsUrlObject.shareModal, { hash: hash }, me.onOpenShareModal, me);
        },

        /**
         * @param { string } response
         */
        onOpenShareModal: function(response) {
            // Open modal
            $.modal.open(response, {
                width: 750,
                sizing: 'auto',
                title: jsSnippetObject.shareTitle
            });
        },

        /**
         * This method is called every time the user wants to share a list via google+, twitter or facebook
         * @param event
         * @returns {boolean}
         */
        onClickSocial: function(event) {
            var $el = $(event.currentTarget),
                url = $el.attr('href'),
                width = $el.attr('data-width'),
                height = $el.attr('data-height');
            event.preventDefault();

            if ($el.parent().hasClass('cart--disabled')) {
                return false;
            }

            var win = window.open(url, null, 'width=' + width + ',height=' + height + ',resizable=no,toolbar=no,menubar=no,location=no');
            win.focus();

            $.publish('plugin/swAdvancedCart/onClickSocial');

            /** @deprecated changed to 'sw' prefixed version */
            $.publish('plugin/advancedCart/onClickSocial');
            return false;
        },

        /**
         * This event is needed to enable the enter-button in order to change the list-name
         * @param event
         */
        onNameKeyUp: function(event) {
            // 13 == Enter-Button
            if (event.keyCode == 13) {
                $(event.currentTarget).focusout();
            }

            $.publish('plugin/swAdvancedCart/onNameKeyUp');

            /** @deprecated changed to 'sw' prefixed version */
            $.publish('plugin/advancedCart/onNameKeyUp');
        },

        /**
         * We need this event, so the name-change is triggered by clicking out of the text-box
         * @param event
         */
        onFocusOut: function(event) {
            var me = this,
                $el = $(event.currentTarget),
                listContainer = $el.parents('.saved-lists--list-container'),
                id = $el.attr('data-list-id'),
                val;

            // strip html tags
            $el.val($el.val().replace(/(<([^>]+)>)/ig, ''));

            val = $el.val();

            $el.parent().hide();
            listContainer.find('.list-container--text-name').html(val);
            listContainer.find('.list-container--text-name, .list-container--text-count, .list-container--text-state').show();
            $(me.opts.openConfirmModalSelector).attr('data-name', val);

            me.callAjax(jsUrlObject.changeName, { newName: val, basketId: id });

            $.publish('plugin/swAdvancedCart/onFocusOut');

            /** @deprecated changed to 'sw' prefixed version */
            $.publish('plugin/advancedCart/onFocusOut');
        },

        /**
         * This method is called when the user clicks on the "rename"-entry.
         * It will display the hidden text-box and sets the focus on it
         * @param event
         */
        onRenameList: function(event) {
            event.preventDefault();
            var me = this,
                $el = $(event.currentTarget),
                parentContainer = $el.parents(me.opts.listContainerSelector),
                inputContainer = parentContainer.find('.list-container--name-hidden'),
                listContainer = parentContainer.parents('.saved-lists--list-container');

            // display the input over the name-div
            inputContainer.show();
            // set focus, so the "focus-out" is triggered for sure when the user clicks anywhere else
            inputContainer.find('input').focus();

            // The name-change-input is just an overlay over the row.
            // Long names cause the information (e.g. "Public list") to be displayed next to the overlay input, so we better hide it.
            listContainer.find('.list-container--text-name, .list-container--text-count, .list-container--text-state').hide();

            $.publish('plugin/swAdvancedCart/onRenameList');

            /** @deprecated changed to 'sw' prefixed version */
            $.publish('plugin/advancedCart/onRenameList');
        },

        /**
         * This method is needed to delete an product from a list after the confirmation-modal
         * @param event
         */
        onConfirmDelete: function(event) {
            var me = this,
                $el = $(event.currentTarget);

            window.location = me.prepareUrl($el.attr('href'));

            $.publish('plugin/swAdvancedCart/onConfirmDelete');

            /** @deprecated changed to 'sw' prefixed version */
            $.publish('plugin/advancedCart/onConfirmDelete');
        },

        /**
         * This event is triggered when the user remove product from wishlist
         * @param event
         */
        onProductDelete: function (event) {
            var me = this,
                $el = $(event.currentTarget),
                url = $el.attr('href');

            /**
             * if tagName = "BUTTON" the shopware notice list deletes a product
             * so the event must executed. If tagName = "A" the event must be prevented.
             */
            if ($el.prop('tagName') === 'A') {
                event.preventDefault();
            }

            $.publish('plugin/swAdvancedCart/onRemoveArticle', [me, event]);

            /** @deprecated changed to 'sw' prefixed version */
            $.publish('plugin/advancedCart/onRemoveArticle', [me, event]);

            me.callAjax(url, {}, me.afterDeleteProduct, me, $.noop, { el: $el, event: event });
        },

        /**
         * @param { string } response
         * @param { object } extraParams
         */
        afterDeleteProduct: function(response, extraParams) {
            var me = this,
                $parent = extraParams.el.parent(),
                $cart = extraParams.el.closest('.list-container--article-table'),
                $cartButton = extraParams.el.parents().find('.article-table--add-cart'),
                $cartButtonBottom = extraParams.el.parents().find('.list-container--manage-buttons a'),
                result = $.parseJSON(response);

            $parent.remove();

            if (result.count <= 0) {
                $cart.addClass('cart--hidden');
                $cartButton.addClass('cart--hidden');
                $cartButtonBottom.addClass('cart--hidden');
            }

            $.publish('plugin/swAdvancedCart/onAfterRemoveArticle', [me, extraParams.event]);

            /** @deprecated changed to 'sw' prefixed version */
            $.publish('plugin/advancedCart/afterRemoveArticle', [me, extraParams.event]);
        },

        /**
         * Opens the confirmation-modal when the user wants to delete a wish-list
         * @param event
         */
        onOpenConfirmModal: function(event) {
            var me = this,
                $el = $(event.currentTarget),
                url = $el.attr('data-url'),
                name = $el.attr('data-name');

            event.preventDefault();

            me.callAjax(jsUrlObject.wishlistConfirmModal, { name: name, deleteUrl: url }, me.openConfirmModal, me);

            $.publish('plugin/swAdvancedCart/onOpenConfirmModal');

            /** @deprecated changed to 'sw' prefixed version */
            $.publish('plugin/advancedCart/onOpenConfirmModal');
        },

        /**
         * @param { string } response
         */
        openConfirmModal: function(response) {
            $.modal.open(response, {
                width: 500,
                sizing: 'content',
                title: jsSnippetObject.confirmTitle
            });
        },

        /**
         * This method gets called every time the state of the "Public"-Checkbox is changed
         * @param event
         */
        onChangePublicState: function(event) {
            var me = this,
                target = $(event.currentTarget),
                id = target.attr('data-list-id'),
                parent = target.parents('.saved-lists--list-container'),
                publicIcon = parent.find('.list-container--lock-icon'),
                linkArea = target.parents('.list-container--header').find('.header--sharing-container'),
                publicText = parent.find('.list-container--text-state'),
                actionLinks = parent.find('.public-list--action-link');

            // If the value of the checkbox changed from private to public
            if (target.is(':checked')) {
                linkArea.removeClass('list-container--disabled');
                publicIcon.removeClass('icon--lock').addClass('icon--eye').attr('title', jsSnippetObject.listIsPublic);
                publicText.html(jsSnippetObject.publicListText);
                actionLinks.removeClass('cart--disabled');

                me.callAjax(jsUrlObject.changePublished, { basketId: id, newState: 1 });
            } else {
                linkArea.addClass('list-container--disabled');
                publicIcon.removeClass('icon--eye').addClass('icon--lock').attr('title', jsSnippetObject.listIsPrivate);
                publicText.html(jsSnippetObject.privateListText);
                actionLinks.addClass('cart--disabled');

                me.callAjax(jsUrlObject.changePublished, { basketId: id, newState: 0 });
            }

            $.publish('plugin/swAdvancedCart/onChangePublicState');

            /** @deprecated changed to 'sw' prefixed version */
            $.publish('plugin/advancedCart/onChangePublicState');
        },

        /**
         * This method slides the list-content up or down
         * @param event
         */
        onClickListContainer: function(event) {
            var me = this,
                $currentTarget = $(event.currentTarget).parent(),
                isActive = $currentTarget.attr('data-is-active') === 'true',
                $currentIcon = $currentTarget.find('.list-container--icon');

            $currentTarget.find(me.opts.listContainerSelector).slideToggle();

            // Is needed to toggle the arrow
            if (isActive) {
                $currentIcon.removeClass('icon--arrow-up').addClass('icon--arrow-down');
                $currentTarget.attr('data-is-active', false);
            } else {
                $currentIcon.removeClass('icon--arrow-down').addClass('icon--arrow-up');
                $currentTarget.attr('data-is-active', true);
            }

            $.publish('plugin/swAdvancedCart/onClickListContainer');

            /** @deprecated changed to 'sw' prefixed version */
            $.publish('plugin/advancedCart/onClickListContainer');
        },

        /**
         * Helper method to handle the response after a cart has been saved
         * @param data
         * @param name
         * @param checkFlag
         * @param row
         */
        handleDoneResponse: function(data, name, checkFlag, row) {
            var me = this,
                result = $.parseJSON(data),
                // Indicates whether a custom item was being saved
                element;

            row.fadeOut(400, function() {
                element.delay(100).slideDown('slow');
                $('.save-cart--show-lists').show();
                $('#save-cart--list-button .show-lists--button').delay(1000).animate({
                    opacity: 1
                }, 400);
            });

            // Check if share flag is set
            if (checkFlag) {
                me.openShareModal(result.hash);
            }
        },

        /**
         * This method is called when the user wants to save the current cart as a new wish-list.
         * @param event
         */
        onSaveCart: function(event) {
            event.preventDefault();

            var me = this,
                $el = $(event.currentTarget),
                $name = $('#wishlist--name'),
                name = $name.val(),
                row = $('.save-cart--content'),
                checkFlag = $('#share--checkout-cart').prop('checked'),
                waitText, parameter, extraParameter;

            // Remove tags from name
            name = name.replace(/(<([^>]+)>)/ig, '');

            if (name.length >= 3) {
                // Disable submit button
                waitText = $el.attr('data-wait-text');
                $el.html(waitText);
                $el.attr('disabled', 'disabled');

                parameter = { name: name, published: checkFlag };
                extraParameter = {
                    el: $el,
                    name: name,
                    nameElement: $name,
                    checkFlag: checkFlag,
                    row: row
                };

                me.callAjax(jsUrlObject.saveWishList, parameter, me.afterSaveCart, me, me.onSaveCartError, extraParameter);
            } else {
                row.find('.wishlist-alert--name-length').slideDown('slow')
                    .delay(3000)
                    .slideUp('slow');

                // Set focus to input
                $name.focus();
            }

            $.publish('plugin/swAdvancedCart/onSaveCart');

            /** @deprecated changed to 'sw' prefixed version */
            $.publish('plugin/advancedCart/onSaveCart');
        },

        /**
         * @param { string } response
         * @param { object } extraParams
         */
        afterSaveCart: function(response, extraParams) {
            var me = this;
            me.handleDoneResponse(data, extraParams.name, extraParams.checkFlag, extraParams.row);
        },

        /**
         * @param { object } extraParams
         */
        onSaveCartError: function(extraParams) {
            var me = this;

            extraParams.row.find('.wishlist-alert--save-cart').slideDown('slow')
                .delay(3000)
                .slideUp('slow');

            // Activate submit button
            extraParams.el.html(jsSnippetObject.save);
            extraParams.el.removeAttr('disabled');

            // Set focus to input
            extraParams.nameElement.focus();
        },

        /**
         * It is called when an product should be added to a wish-list.
         * It simply opens the modal-window, which displays the possible lists.
         * @param event
         * @returns {boolean}
         */
        onOpenDetailModal: function(event) {
            var me = this,
                id = $(me).attr('id'),
                target = $(event.currentTarget),
                qty = $('.quantity--select').val(),
                customizedClass = $('.customizing--data-wrapper'),
                customized = 1,
                parameter = {
                    orderNumber: target.attr('data-ordernumber'),
                    quantity: qty || 1,
                    customized: customized
                };

            event.preventDefault();

            // No customizing-product
            if (!customizedClass || !customizedClass.length) {
                parameter.customized = 0;
            }

            me.callAjax(jsUrlObject.detailModal, parameter, me.openDetailModal, me);

            $.publish('plugin/swAdvancedCart/onDetailAddToWishList');

            /** @deprecated changed to 'sw' prefixed version */
            $.publish('plugin/advancedCart/onDetailAddToWishList');
            return false;
        },

        /**
         * @param { string } response
         */
        openDetailModal: function(response) {
            var me = this;
            me.openModal(response);
        },

        /**
         * This opens the modal-window containing the advanced-cart content.
         * @param {string} content
         */
        openModal: function(content) {
            var me = this,
                sizing = 'content';

            if (StateManager.isCurrentState('xs')) {
                sizing = 'auto';
            }

            me.modal = $.modal.open(content, {
                width: 750,
                sizing: sizing,
                title: jsSnippetObject.addToWishList
            });
        },

        /**
         * This method is called after the user decided to add an product to a special wish-list
         * and after he clicked the submit-button on the modal-window
         * @param event
         * @returns {boolean}
         */
        onSubmitAddToWishList: function(event) {
            var me = this,
                data,
                successCt;
            event.preventDefault();

            data = $('.js--modal .cart--form-add-article').serialize();

            $.loadingIndicator.open({
                openOverlay: false
            });

            me.callAjax(
                jsUrlObject.addToWishList,
                data,
                me.afterSubmitAddToWishList,
                me,
                me.onSubmitAddToWishListError
            );

            $.publish('plugin/swAdvancedCart/onSubmitAddToWishList');

            /** @deprecated changed to 'sw' prefixed version */
            $.publish('plugin/advancedCart/onSubmitAddToWishList');
            return false;
        },

        /**
         * @param { string } response
         */
        afterSubmitAddToWishList: function(response) {
            var me = this,
                status = $.parseJSON(response),
                successCt;

            if (status.success == true && status.lists == null) {
                successCt = $('.js--modal .wishlist-alert--min-one');
            } else if (status.success == true) {
                me.modal.close();
                successCt = $('.wishlist-alert--add-success');
            } else {
                successCt = $('.js--modal .wishlist-alert--add-error');
            }

            successCt.slideDown('slow').delay(2000).slideUp('slow');
            $.loadingIndicator.close();
        },

        onSubmitAddToWishListError: function() {
            $('.js--modal .wishlist-alert--add-error').fadeIn('slow').delay(3500).fadeOut('slow');
            $.loadingIndicator.close();
        },

        destroy: function() {
            var me = this,
                $body = $('body');

            $body.off('change', me.opts.cartQuantitySelector);
            $body.off('click', me.opts.confirmDeleteButtonSelector);
            $body.off('click', me.opts.submitAddToWishListSelector);
            $body.off('keydown', me.opts.addToWishListInputSelector);
            $body.off('click', me.opts.shareMailButtonSelector);
            $body.off('click', me.opts.cartProductRemoveSelector);
            $body.off('click', me.opts.openAddWishListSelector);

            me._destroy();
        },

        /**
         * @param { string } url
         * @return { string }
         */
        prepareUrl: function(url) {
            var me = this,
                regEx = /^http:\/\//;

            if (me.isSecure) {
                url = url.replace(regEx, 'https://');
            }

            return url;
        }
    });

    $(function() {
        $('body').swAdvancedCart();
    });

    $.subscribe('plugin/swAjaxVariant/onBeforeRequestData', function() {
        $('body').data('plugin_swAdvancedCart').destroy();
    });

    $.subscribe('plugin/swAjaxVariant/onRequestData', function() {
        $('body').swAdvancedCart();
    });
})(jQuery);
