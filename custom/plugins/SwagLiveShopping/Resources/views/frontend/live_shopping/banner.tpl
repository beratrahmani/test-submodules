{* Live Shopping banner *}
{block name="frontend_listing_banner"}
    {if $listingBanner}
        {block name='frontend_listing_image_only_banner'}
            <div class="liveshopping--banner">
                <img class="liveshopping--banner-img"
                     alt="{s name="emotionLiveshoppingHeader" namespace="frontend/live_shopping/main"}{/s}"
                     src="{$listingBanner}"/>
            </div>
        {/block}
    {/if}
{/block}
