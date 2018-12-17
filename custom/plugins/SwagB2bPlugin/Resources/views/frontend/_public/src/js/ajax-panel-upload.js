/**
 * B2B File Upload Plugin for Drag 'n Drop Upload with native file upload fallback
 *
 * 3 ways to upload a file are implemented:
 *  1. Drag File and Drop File inside the drop zone
 *  2. Choose a file by clicking inside the drop zone
 *  3. If drag 'n drop upload is not supported the native file picker will be shown
 *
 * Usage:
 * <div class="b2b--ajax-panel" data-id="example-upload" data-url="{url action=upload}" data-plugins="b2bFileUpload"></div>
 *
 * Inside Ajax Panel:
 * <form class="form--upload" method="post" action="{url action=processUpload}" enctype="multipart/form-data" data-b2b-upload="true" data-url="{url action=processUpload}" data-target-panel-id="target-content-panel--id">
 *  <div class="upload--input">
 *
 *      {* Drag 'n Drop Upload *}
 *      <label for="file">
 *          <h3>Choose a file</h3>
 *          <span class="box--dragndrop"> or drag the file from your desktop here</span>
 *      </label>
 *
 *      {* Fallback File Picker *}
 *      <input class="input--file" type="file" name="file" id="file" />
 *
 *  </div>
 * </form>
 */
$.plugin('b2bFileUpload', {
    defaults: {
        errors: {
            configuration: 'You are trying to load the file upload without a correct upload handler.',
            notSupported: 'HTML 5 Drag n Drop Upload is not supported on your browser.',
            fallingBack: 'Falling back to native File Upload'
        },

        advancedUploadCls: 'has-advanced-upload',
        additionalFormInputsContainer: '.form--additional-inputs',
        dragOverCls: 'is-dragover',

        nativeFileInputSelector: '.input--file'
    },

    init: function() {
        this.applyDataAttributes();
        this.registerGlobalListeners();
    },

    registerGlobalListeners: function() {
        var me = this;
        var $form = me.$el.find('.b2b--upload-form');

        if(!$form) {
            console.error(me.defaults.errors.configuration);
            return;
        }

        me.$form = $form;
        me.opts.fetchUrl = $form.data('url');

        if (!me.isAdvancedUpload) {
            console.warn(me.defaults.errors.notSupported);
            console.info(me.defaults.errors.fallingBack);
            return;
        }

        $form.addClass(me.defaults.advancedUploadCls);

        me._on(me.$el, 'drag dragstart dragend dragover dragenter dragleave drop', $.proxy(me.onDragStopStopEvent, me));
        me._on(me.$el, 'dragover dragenter', $.proxy(me.onDragOverOrEnterMarkFormAsDragOver, me));
        me._on(me.$el, 'dragleave dragend drop', $.proxy(me.onDragLeaveDropSubmitFile, me));
        me._on(me.$el.find(me.defaults.nativeFileInputSelector), 'change', $.proxy(me.onFileInputChangeSubmit, me));
        me._on(me.$el, 'drop', $.proxy(me.onDropSubmitFile, me));
        me._on($form, 'submit', $.proxy(me.confirmSubmitFile, me));
    },

    isAdvancedUpload: function() {
        var div = document.createElement('div');
        return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
    },

    onDragStopStopEvent: function(event) {
        event.preventDefault();
        event.stopPropagation();
    },

    onDragOverOrEnterMarkFormAsDragOver: function() {
        var me = this,
            $form = me.$el.find('.b2b--upload-form');

        $form.addClass(me.defaults.dragOverCls);
    },

    onDragLeaveDropSubmitFile: function() {
        var me = this,
            $form = me.$el.find('.b2b--upload-form');

        $form.removeClass(me.defaults.dragOverCls);
        var file = $(me.defaults.nativeFileInputSelector).prop('files')[0];

        if(!file) {
            return;
        }
        me.submitFile(file);
    },

    onDropSubmitFile: function(event) {
        var me = this,
            droppedFiles = event.originalEvent.dataTransfer.files,
            file = droppedFiles[0];

        if(!file) {
            return;
        }

        me.confirmSubmitFile(event, file);
    },

    onFileInputChangeSubmit: function(event){
        var me = this,
            $fileInput = $(me.defaults.nativeFileInputSelector);

        var file = $fileInput.prop('files')[0];

        if(!file) {
            return;
        }

        $fileInput.val(null);
        me.confirmSubmitFile(event, file);
    },

    confirmSubmitFile: function(event, file) {
        var me = this,
            $target = $(event.currentTarget),
            $form = $target.closest('form');

        if ($target.data('confirm')) {
            var articleCount = $('.table--ordernumber tbody').find('tr').length - 1;

            if(!articleCount){
                me.submitFile(file);
                return;
            }
            me.defaults.activeForm = $form;
            $.ajax({
                'url': $target.data('confirm-url'),
                'type': 'post',
                'data': $form.serialize(),
                success: function(response) {
                    me.showConfirmModal(file, response);
                },
                error: function(jqXHR) {
                    console.warn("An error occurred: " + jqXHR.responseText);
                }
            });
        }else {
            me.submitFile(file);
        }
    },

    showConfirmModal: function(file, response){
        var me = this;

        $.b2bConfirmModal.open(response, {
            'confirm': function() {
                $.b2bConfirmModal.close();
                me.submitFile(file);
            },
            'cancel': function() {
                $.b2bConfirmModal.close();
            }
        });
    },

    submitFile: function(droppedFile) {
        var me = this,
            formData = new FormData();

        formData.append('uploadedFile', droppedFile);

        $(me.defaults.additionalFormInputsContainer + ' input').each(function () {
                var $input = $(this),
                    value = null;
                if ($input.attr('type') === 'checkbox') {
                    value = $input.is(':checked');
                } else {
                    value = $input.val();
                }
                formData.append($input.attr('name'), value);
            }
        );

        $.ajax({
            url: me.opts.fetchUrl,
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            type: 'POST',
            success: function(data){
                var panelId = me.$form.data('target-panel-id');
                if(!panelId) {
                    return;
                }

                var $ajaxTargetPanel = $('.b2b--ajax-panel[data-id="'+ panelId +'"]');
                if(!$ajaxTargetPanel) {
                    return;
                }

                $ajaxTargetPanel.find('.panel--body').html(data);
            }
        });
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});