{block name="frontend_wishlist_index_list_articles"}
    <div class="list-container--article-table panel">
        <div class="article-table--table panel--table" data-compare-ajax="true">
            {block name="frontend_wishlist_index_list_articles_header"}
                <div class="article-table--header panel--tr">
                    {block name="frontend_wishlist_index_list_articles_header_article"}
                        <div class="panel--th column--article">
                            {s name='Article' namespace='frontend/plugins/swag_advanced_cart/plugin'}{/s}
                        </div>
                    {/block}

                    {block name="frontend_wishlist_index_list_articles_header_price"}
                        <div class="panel--th column--price">
                            {s name='Price' namespace='frontend/plugins/swag_advanced_cart/plugin'}{/s}
                        </div>
                    {/block}
                </div>
            {/block}

            {foreach from=$wishList.items item=item name=itemIteration}
                {block name="frontend_wishlist_index_list_articles_item"}
                    {include file="frontend/wishlist/item_form.tpl" item=$item sBasketItem=$item.article}
                {/block}
            {/foreach}
        </div>
    </div>
{/block}
