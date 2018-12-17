{if $sArticle.attributes.swag_bundle && {config name=SwagBundleShowBundleIcon}}
    <div class="product--badge badge--bundle">
        {block name='frontend_bundle_listing_badge_content'}
            <i class="icon--link"></i>
            <span>{s name='bundleBadge' namespace='frontend/listing/bundle'}{/s}</span>
        {/block}
    </div>
{/if}
