{block name="frontend_detail_liveshopping_data"}
    {if $liveShopping}
        {* Liveshopping detail page *}
        {block name="frontend_liveshopping_detail"}
            <div class="liveshopping--details"
                 data-live-shopping="true"
                 data-validTo="{$liveShopping.validTo}"
                 data-liveShoppingId="{$liveShopping.id}"
                 data-dataUrl="{url module=widgets controller="LiveShopping" action="getLiveShoppingData" liveShoppingId=$liveShopping.id}"
                 data-liveShoppingType="{$liveShopping.type}"
                 data-star="{s namespace="frontend/listing/box_article" name="Star"}*{/s}"
                 data-initialSells="{$liveShopping.sells}"
                 data-currencyHelper="{0|currency}">

                {* Liveshopping counter *}
                {block name="frontend_liveshopping_detail_counter"}
                    <div class="counter is--align-center">
                        <div class="counter--time {if $liveShopping.limited === 1}is--stock{/if}">

                            {* Liveshopping counter headline *}
                            {block name="frontend_liveshopping_detail_counter_headline"}
                                <div class="counter--headline">
                                    {s name="sLiveHeadline" namespace="frontend/live_shopping/main"}{/s}
                                </div>
                            {/block}

                            {* Liveshopping counter *}
                            {block name='frontend_liveshopping_detail_counter_include'}
                                {include file='frontend/swag_live_shopping/_includes/liveshopping-counter.tpl'}
                            {/block}
                        </div>

                        {* Liveshopping stock *}
                        {block name='frontend_liveshopping_detail_stock'}
                            {if $liveShopping.limited === 1}
                                {include file='frontend/swag_live_shopping/_includes/liveshopping-stock.tpl'}
                            {/if}
                        {/block}

                    </div>
                {/block}

                {* Liveshopping content with price and discount *}
                {block name='frontend_liveshopping_detail_content'}
                    <div class="liveshopping--prices">

                        {* Icon, regular price, discount price, unit price *}
                        {block name='frontend_liveshopping_detail_pricing_include'}
                            {include file="frontend/swag_live_shopping/detail/liveshopping-detail-pricing.tpl"}
                        {/block}
                    </div>
                {/block}

            </div>
        {/block}
    {/if}
{/block}
