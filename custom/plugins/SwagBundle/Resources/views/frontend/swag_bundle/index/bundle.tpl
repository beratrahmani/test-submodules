{block name='swag_bundle_detail_bundle'}
    {if !$sShowBundleBelowDesc}
        {if $sBundles}
            {foreach $sBundles as $bundle}
                {if $bundle@first}
                    {block name='bundle_panel_error_message'}
                        <div class="bundle-panel--no-bundle-available is--hidden">
                            {include file='frontend/_includes/messages.tpl' type='info' content="{s namespace='frontend/detail/bundle' name='NotForThisVariant'}{/s}"}
                        </div>
                    {/block}
                {/if}
                <form class="bundle--form" method="POST" action="{url module=widgets controller=Bundle action=addBundleToBasket bundleId=$bundle.id productId=$sArticle.articleID}">
                    {include file='frontend/swag_bundle/bundle/bundle.tpl' bundle=$bundle longestShippingTimeProduct=$bundle.longestShippingTimeProduct}
                </form>
            {/foreach}
        {/if}
    {/if}
{/block}
