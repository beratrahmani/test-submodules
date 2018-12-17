{extends file='parent:widgets/swag_emotion_advanced/index.tpl'}
{namespace name='frontend/detail/bundle'}

{block name='swag_emotion_advanced_quick_view_product_detail_button'}
    {$smarty.block.parent}
    {if $sBundles && $swagBundleIsEmotionAdvancedQuickView}
        {block name='frontend_detail_buy_button_bundle_hint'}
            <div class="quick-view--bundle-hint">
                {include file='frontend/_includes/messages.tpl' content="{s name=quickViewBundleHint}This product is also available as bundle.{/s}" type='info'}
            </div>
        {/block}
    {/if}
{/block}
