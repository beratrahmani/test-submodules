<div id="promotion-free-goods" class="premium-product panel has--border is--rounded">

    {* Headline *}
    {block name='frontend_checkout_cart_promotion_free_goods_headline'}
        <div class="premium-product--title panel--title is--underline">
            {s name="selectFreeGood" namespace="frontend/swag_promotion/main"}{/s}
        </div>
    {/block}

    {* Product slider *}
    {block name='frontend_checkout_promotion_free_goods_slider'}
        <div class="premium-product--content product-slider" data-itemMinWidth="280" data-product-slider="true">

            {* Product slider container *}
            {block name='frontend_checkout_promotion_free_goods_slider_container'}
                <div class="product-slider--container">
                    {foreach $freeGoods as $freeGood}

                        {* Product slider item *}
                        {block name='frontend_checkout_promotion_free_goods_slider_item'}
                            <div class="premium-product--product product-slider--item">
                                <div class="product--inner" data-ordernumber="{$freeGood.ordernumber}">

                                    {* Product image *}
                                    {block name='frontend_checkout_promotion_free_goods_image'}
                                        <a href="{$freeGood.linkDetails}"
                                           title="{$freeGood.articleName|escape}"
                                           class="product--image">
                                            {block name='frontend_checkout_promotion_free_goods_image_element'}
                                                <span class="image--element">
                                                    {if $freeGood.image.thumbnails}
                                                        <img srcset="{$freeGood.image.thumbnails[0].sourceSet}"
                                                             alt="{$freeGood.articleName|escape}"/>
                                                    {else}
                                                        <img src="{link file='frontend/_public/src/img/no-picture.jpg'}"
                                                             alt="{"{s name="ListingBoxNoPicture" namespace="frontend/listing/box_article"}{/s}"|escape}">
                                                    {/if}
                                                </span>
                                            {/block}
                                        </a>
                                    {/block}

                                    {*Product name*}
                                    {block name='frontend_checkout_promotion_free_goods_article_name'}
                                        <div class="product--box box--slider">
                                            <a href="{$freeGood.linkDetails}"
                                               title="{$freeGood.articleName|escape}"
                                               class="product--title">
                                                {$freeGood.articleName|truncate:50}
                                            </a>
                                        </div>
                                    {/block}

                                    {*select free product *}
                                    {block name='frontend_checkout_promotion_free_goods_form'}
                                        <form action="{url controller=SwagPromotion action=addFreeGoodToCart}" method="post">
                                            <input type="hidden" name="noXhr" value="true">
                                            <input type="hidden" name="promotionId" value="{$freeGood.promotionId}">
                                            {block name='frontend_checkout_promotion_free_goods_select_article'}
                                                {if $freeGood.variants && $freeGood.variants|@count > 1}
                                                    <div class="premium--variant">
                                                        <select class="premium--selection" name="orderNumber"
                                                                title="{s name='CheckoutSelectVariant' namespace='frontend'}{/s}">
                                                            {foreach from=$freeGood.variants item=variant}
                                                                <option value="{$variant.orderNumber}">{$variant.additionalText}</option>
                                                            {/foreach}
                                                        </select>
                                                        {block name='frontend_checkout_promotion_free_goods_info_button_small'}
                                                            <button class="premium--button btn is--primary is--align-center"
                                                                    type="submit"
                                                                    title="{s name='addToCart' namespace='frontend/swag_promotion/main'}{/s}">
                                                                <i class="icon--arrow-right is--large"></i>
                                                            </button>
                                                        {/block}
                                                    </div>
                                                {else}
                                                    <input type="hidden" name="orderNumber" value="{$freeGood.ordernumber}"/>
                                                    {block name='frontend_checkout_promotion_free_goods_info_button'}
                                                        <button class="btn is--primary is--align-center is--icon-right"
                                                                type="submit"
                                                                title="{s name='addToCart' namespace='frontend/swag_promotion/main'}{/s}">
                                                            {s name='addToCart' namespace="frontend/swag_promotion/main"}{/s}
                                                            <i class="icon--arrow-right"></i>
                                                        </button>
                                                    {/block}
                                                {/if}
                                            {/block}
                                        </form>
                                    {/block}
                                </div>
                            </div>
                        {/block}
                    {/foreach}
                </div>
            {/block}
        </div>
    {/block}
</div>
