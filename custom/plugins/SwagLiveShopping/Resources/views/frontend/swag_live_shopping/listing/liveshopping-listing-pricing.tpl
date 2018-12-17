{block name="frontend_liveshopping_listing_pricing"}
    {$liveShopping = $sArticle.liveShopping}
    <div class="liveshopping--container">
        {* Default price *}
        {block name="frontend_liveshopping_listing_price"}
            <span class="liveshopping--price">
				{$liveShopping.currentPrice|currency}
                {s name="Star" namespace="frontend/listing/box_article"}{/s}
			</span>
        {/block}

        {* Pseudo price *}
        {block name="frontend_liveshopping_listing_pseudoprice"}
            <span class="liveshopping--pseudoprice is--line-through">
				{if $liveShopping.type === 1 || $liveShopping.type === 2}
                    {s name="reducedPrice" namespace="frontend/live_shopping/main"}{/s}
                    {$liveShopping.startPrice|currency}
                    {s name="Star" namespace="frontend/listing/box_article"}{/s}
                {else}
                    {s name="reducedPrice" namespace="frontend/live_shopping/main"}{/s}
                    {$liveShopping.endPrice|currency}
                    {s name="Star" namespace="frontend/listing/box_article"}{/s}
                {/if}
			</span>
        {/block}
    </div>
{/block}
