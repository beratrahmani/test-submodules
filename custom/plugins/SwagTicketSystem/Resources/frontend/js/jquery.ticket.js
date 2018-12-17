;(function($, window, document, undefined) {

    // Wait 'till the document is ready
    $(function () {

        function addUpload($upload, file) {
            var $field = $upload.find('.fileupload-input'),
                    $container = $('<p>', { 'class': 'thumbnail-wrapper' }).appendTo($upload);

            if (file.error) {
                var $response = $('<div>', {
                    'html': $('<div>', {
                        'class': 'alert--icon',
                        'html': $('<i>', {
                            'class': 'icon--element icon--cross'
                        })
                    }),
                    'class': 'alert is--error is--rounded'
                }).append('<div class="alert--content">' + file.error + '</div>').appendTo($container);
            } else {
                var $response = $('<div>', {
                    'html': $('<div>', {
                        'class': 'alert--icon',
                        'html': $('<i>', {
                            'class': 'icon--element icon--check'
                        })
                    }),
                    'class': 'alert is--success is--rounded'
                }).append('<div class="alert--content">' + file.message + '</div>').appendTo($container);
            }

            window.setTimeout(function () {
                $response.slideUp('slow', function () {
                    $container.remove();
                });
            }, 2000);
        }

        var $fileUploads = $('.option_values_upload_file');
        $fileUploads.each(function () {
            var $this = $(this),
                $dropZone = $this.find('.fileupload-dropzone'),
                $field = $this.find('.fileupload-input'),
                $answerId = $this.find('input[name=answer]').val(),
                $ticketId = $this.find('input[name=ticket]').val(),
                $progress = $('<div class="progress progress-striped active">').append('<div class="bar">').append('<div class="message">').hide().appendTo($this);

            if (!$.support.xhrFileUpload) {
                $dropZone.hide();
            }
            var uploadUrl = $this.parent().attr('action');
            $field.fileupload({
                dataType: 'json',
                dropZone: $dropZone,
                url: uploadUrl + '?ticketId=' + $ticketId + '&answerId=' + $answerId,
                paramName: 'files',
                redirect: 0,
                //forceIframeTransport: true,
                formData: function (form) {
                    return [];
                },
                start: function (e, data) {
                    if ($.support.xhrFileUpload) {
                        $progress.children('.bar').css('width', 0);
                    } else {
                        $progress.children('.bar').css('width', '100%');
                    }
                    $progress.show();
                },
                progressall: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $progress.children('.bar').css('width', '' + progress + '%');
                },
                done: function (e, data) {
                    $progress.hide();
                    $.each(data.result, function (index, file) {
                        addUpload($this, file);
                    });
                }
            });
        });
    });

})(jQuery, window, document);