;(function ($, window) {
    // Initialize the plugin when the default emotion plugin is ready
    $.subscribe('plugin/swEmotion/onInitElements', function (event, emotion) {
        if (emotion.$el.is('[data-quickview="true"]')) {
            emotion.$el.swQuickView();
        }

        $('.emotion--side-view').swSideView();
    });

    $.subscribe('plugin/swEmotionLoader/onInitEmotion', function (event, wrapper) {
        wrapper.$el.find('[data-storytelling="true"]').swStoryTelling();
    });

    $.subscribe('plugin/swEmotionLoader/onShowEmotion', function (event, wrapper) {
        if (wrapper.$emotion.length) {
            window.setTimeout(function () {
                wrapper.$el.find('[data-storytelling="true"]').swStoryTelling();
            }, 10);
        }
    });

    $.subscribe('plugin/swEmotionLoader/onHideEmotion', function (event, wrapper) {
        wrapper.$el.find('[data-storytelling="true"]').each(function (index, el) {
            var storytelling = $(el).data('plugin_swStoryTelling');

            if (storytelling !== undefined) {
                storytelling.destroy();
            }
        });
    });
})(jQuery, window);
