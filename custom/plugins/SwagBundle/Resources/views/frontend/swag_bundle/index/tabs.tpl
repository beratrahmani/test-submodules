{block name='swag_bundle_detail_tabs'}
    {if $sShowBundleBelowDesc}
        {if $sBundles}
            {foreach $sBundles as $bundle}
                <form class="bundle--form" method="POST" action="{url module=widgets controller=Bundle action=addBundleToBasket bundleId=$bundle.id productId=$sArticle.articleID}">
                    {include file='frontend/swag_bundle/bundle/bundle.tpl' bundle=$bundle longestShippingTimeProduct=$bundle.longestShippingTimeProduct}
                </form>
            {/foreach}
        {/if}
        {if $sBundlesButNotForThisVariant}
            {include file='frontend/_includes/messages.tpl' type='info' content="{s namespace='frontend/detail/bundle' name='NotForThisVariant'}{/s}"}
        {/if}
    {/if}
{/block}
