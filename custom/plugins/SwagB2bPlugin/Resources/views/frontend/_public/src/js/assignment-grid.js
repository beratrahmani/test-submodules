/**
 * Handles the assignment grid view extensions
 *
 * * Submit the form through ajax
 * * Disable Grant buttons if access is disabled
 *
 * global: document
 */
$.plugin('b2bAssignmentGrid', {
    defaults: {
        gridFormSelector: 'form.b2b--assignment-form',

        allowInputSelector: '.assign--allow',

        denyInputSelector: '.assign--grantable',

        rowParentSelector: '.panel--tr',

        actionSearchSelector: '.action--search',

        errorClass: 'grid--errors'
    },

    init: function() {
        var me = this,
            $searchInput = me.$el.find(me.defaults.actionSearchSelector).find('input[type=text]');

        this.applyDataAttributes();

        me.setSearchInputFocus($searchInput);

        me._on(document, 'tree_toggle_menu', $.proxy(me.renderGrid, me));

        me.renderGrid(me);
    },

    setSearchInputFocus: function($searchInput) {

        if(!$searchInput.length) {
            return;
        }

        $searchInput.focus();

        if(!$searchInput.length) {
            return;
        }

        var searchTerm = $searchInput.val();
        if(!searchTerm.length) {
            return;
        }

        $searchInput[0].setSelectionRange(searchTerm.length, searchTerm.length);
    },

    parseJSON: function(input) {
        try {
            return JSON.parse(input);
        } catch (e) {
            return null;
        }
    },

    renderGrid: function() {
        var me = this,
            inAction = false,
            inRevert = false,
            createPreventedChangeEvent = function() {
                var $event = $.Event('change');
                $event.preventDefault();

                return $event;
            };

        var $forms = me.$el.find(me.defaults.gridFormSelector);

        $forms.filter('.b2b--assignment-row-form').each(function() {
            var $form = $(this),
                $allowCheckbox = $form.find(me.defaults.allowInputSelector),
                $grantableCheckbox  = $form.find(me.defaults.denyInputSelector),
                $allAllowCheckboxes = $form.closest(me.defaults.rowParentSelector).find(me.defaults.allowInputSelector + ':gt(0):not(:disabled)'),
                $allGrantableCheckboxes = $form.closest(me.defaults.rowParentSelector).find(me.defaults.denyInputSelector + ':gt(0):not(:disabled)'),
                allAllowChecked = function() {
                    return ($allAllowCheckboxes.length === $allAllowCheckboxes.filter(':checked').length);
                },
                allGrantableChecked = function() {
                    return ($allGrantableCheckboxes.length === $allGrantableCheckboxes.filter(':checked').length);
                };

            $allowCheckbox.prop('checked', allAllowChecked());

            me._on($allAllowCheckboxes, 'change', function () {
                if(inAction) {
                    return;
                }
                inAction = true;
                $allowCheckbox
                    .prop('checked', allAllowChecked())
                    .trigger(createPreventedChangeEvent());
                inAction = false;
            });

            $grantableCheckbox.prop('checked', allGrantableChecked());

            me._on($allGrantableCheckboxes, 'change', function () {
                if(inAction) {
                    return;
                }
                inAction = true;
                $grantableCheckbox
                    .prop('checked', allGrantableChecked())
                    .trigger(createPreventedChangeEvent());
                inAction = false;
            });

            me._on($allowCheckbox, 'change', function(event) {
                if(inAction) {
                    return;
                }

                inAction = true;
                $allAllowCheckboxes
                    .prop('checked', $(this).is(':checked'))
                    .trigger(createPreventedChangeEvent());
                inAction = false;
            });

            me._on($grantableCheckbox, 'change', function(event) {
                if(inAction) {
                    return;
                }

                inAction = true;
                $allGrantableCheckboxes
                    .prop('checked', $(this).is(':checked'))
                    .trigger(createPreventedChangeEvent());
                inAction = false;
            });
        });

        $forms.each(function() {
            var $form = $(this),
                allowCheckbox = $form.find(me.defaults.allowInputSelector),
                grantableCheckbox = $form.find(me.defaults.denyInputSelector);

            if(!allowCheckbox.is(':checked')) {
                grantableCheckbox.prop('disabled', true);
            }

            me._on(allowCheckbox, 'change', function() {
                if(inRevert) {
                    return;
                }

                grantableCheckbox.data('previous-state', grantableCheckbox.prop('checked'));

                if(allowCheckbox.is(':checked')) {
                    grantableCheckbox.prop('disabled', false);
                } else {
                    grantableCheckbox.attr('checked', false);
                    grantableCheckbox.prop('disabled', true);
                }
            });
        });

        this._off($forms, 'submit');
        this._on($forms, 'submit', function(event) {
            event.preventDefault();

            var $form = $(this),
                $checkbox = $(document.activeElement),
                $targetId = $form.data('target'),
                $errorTarget = $('[data-id="' + $targetId + '"]');
            $checkbox.closest('span.checkbox').addClass('is--loading');
            $errorTarget.html('');

            $.ajax({
                url: $form.attr('action'),
                method: $form.attr('method'),
                data: $form.serialize(),
                success: function(response) {
                    if ($errorTarget && response['errors']) {
                        $.each(response['errors'], function (index, value) {
                            $errorTarget.append($('<div>', {
                                'class': me.defaults.errorClass,
                                'html': value
                            }));
                        });

                        var $allowCheckbox = $form.find(me.defaults.allowInputSelector),
                            $grantableCheckbox = $form.find(me.defaults.denyInputSelector),
                            $previousState = $grantableCheckbox.data('previous-state');

                        inAction = true;
                        inRevert = true;
                        $grantableCheckbox
                            .prop('checked', $previousState)
                            .prop('disabled', false)
                            .trigger(createPreventedChangeEvent());
                        $allowCheckbox
                            .prop('checked', true)
                            .trigger(createPreventedChangeEvent());
                        inAction = false;
                        inRevert = false;

                        return;
                    }

                    var jsonResponse = me.parseJSON(response);

                    if (jsonResponse && jsonResponse['routes']) {
                        $.each(jsonResponse['routes'], function(index, value) {
                            var $input = $('#' + value);

                            inAction = true;

                            $input
                                .prop('checked', true)
                                .trigger(createPreventedChangeEvent());

                            inAction = false;
                        });
                    }
                }
            }).complete(function () {
                $checkbox.closest('span.checkbox').removeClass('is--loading');
                $form.find(me.defaults.denyInputSelector).removeAttr('previous-state');
            });
        });
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});