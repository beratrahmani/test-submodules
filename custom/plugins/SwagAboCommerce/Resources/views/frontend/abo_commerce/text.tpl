{block name="frontend_abocommerce_listing_text"}
    <div class="hero-unit abo--teaser category--teaser panel has--border is--rounded">
        {block name="frontend_abocommerce_listing_text_headline"}
            <h1 class="hero--headline panel--title">{$aboCommerceSettings.bannerHeadline}</h1>
        {/block}


        {block name="frontend_abocommerce_listing_text_subheadline"}
            <div class="hero--text panel--body is--wide"
                 data-collapse-text="true"
                 data-lines="2"
                 data-readMoreText="{s namespace='frontend/listing/listing' name='ListingCategoryTeaserShowMore'}{/s}"
                 data-readLessText="{s namespace='frontend/listing/listing' name='ListingCategoryTeaserShowLess'}{/s}">
                {$aboCommerceSettings.bannerSubheadline}
            </div>
        {/block}
    </div>
{/block}
