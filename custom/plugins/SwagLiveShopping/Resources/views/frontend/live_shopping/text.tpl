{* Live Shopping text *}
{block name="frontend_listing_text"}
    {if $listingText || $listingHeadline}
        {block name="frontend_liveshopping_listing_text_container"}
            <div class="liveshopping--cat-text category--teaser panel hero-unit has--border is--rounded">
                {block name="frontend_liveshopping_listing_headline"}
                    {if $listingHeadline}
                        <h1 class="cat-text--headline hero--headline panel--title">{$listingHeadline}</h1>
                    {/if}
                {/block}

                {if $listingText}
                    {block name="frontend_liveshopping_listing_text"}
                        <div class="hero--text panel--body is--wide cat-text--inner-container liveshopping">
                            {$listingText}
                        </div>
                    {/block}
                {/if}
            </div>
        {/block}
    {/if}
{/block}
