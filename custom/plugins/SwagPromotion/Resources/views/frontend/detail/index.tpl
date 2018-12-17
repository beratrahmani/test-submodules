{extends file='parent:frontend/detail/index.tpl'}

{block name='frontend_index_header_javascript_jquery_lib'}
    {$smarty.block.parent}
    {block name='frontend_index_header_javascript_jquery_promotion'}
        <script type="text/javascript">
            var asyncConf = ~~('{$theme.asyncJavascriptLoading}');
            var subscribeFn = function() {
                jQuery.subscribe('plugin/swOffcanvasMenu/onCloseMenu', function() {
                    var plugin = jQuery('.free_goods-product--selection').data('plugin_promotionFreeGoodsSlider');
                    if (plugin !== undefined) {
                        plugin.destroy();
                    }
                });
            };
            if (asyncConf === 1) {
                document.asyncReady(subscribeFn);
            } else {
                subscribeFn();
            }
        </script>
    {/block}
{/block}