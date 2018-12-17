{block name='promotion_checkout_free_goods_body'}
    {if $freeGoods|count}
        <div class="free_goods-product panel" data-url="{url controller=SwagPromotion action=addFreeGoodToCart}">

            {* Headline *}
            {block name='promotion_checkout_free_goods_headline'}
                <div class="free_goods-product--title panel--title is--underline">
                    {s name="selectFreeGood" namespace="frontend/swag_promotion/main"}Select free product{/s}
                </div>
            {/block}

            {* Product slider *}
            {block name='promotion_checkout_free_goods_slider'}
                <div class="free_goods-product--content product-slider" data-itemMinWidth="280">

                    {* Product slider container *}
                    {block name='promotion_checkout_free_goods_slider_container'}
                        <div class="product-slider--container" style="overflow: scroll;">
                            {foreach $freeGoods as $article}

                                {* Product slider item *}
                                {block name='promotion_checkout_free_goods_slider_item'}
                                    <div class="container--product product-slider--item">

                                        <div class="product--inner">

                                            {* Product image *}
                                            {block name='promotion_checkout_free_goods_premium_image'}
                                                <a href="{$article.linkDetails}" title="{$article.articleName|escape}" class="product--image">

                                                    {block name='promotion_checkout_free_goods_image_element'}
                                                        <span class="image--element">
		                                                    {if $article.image.thumbnails}
                                                                <img srcset="{$article.image.thumbnails[0].sourceSet}"
                                                                     alt="{$article.articleName|escape}"/>
                                                            {else}
                                                                <img src="{link file='frontend/_public/src/img/no-picture.jpg'}"
                                                                     alt="{"{s name="PremiumInfoNoPicture"}{/s}"|escape}">
                                                            {/if}
														</span>
                                                    {/block}

                                                </a>
                                            {/block}

                                            <div class="item--title">
                                                <span>{$article.articleName}</span>
                                            </div>

                                            {block name='promotion_checkout_free_goods_select_article'}
                                                {if $article.variants && $article.variants|count > 1}
                                                    <div class="free_goods-product--variant">
                                                        <select class="free_goods-product--selection quantity--select" name="addFreeGood{$article.articleID}">
                                                            {foreach from=$article.variants item=variant}
                                                                <option value="{$variant.orderNumber}">{$variant.additionalText}</option>
                                                            {/foreach}
                                                        </select>
                                                        {block name='promotion_checkout_free_goods_info_button_small'}
                                                            <button class="free_goods-product--button ten-percent btn is--primary is--align-center" data-type="select" data-name="addFreeGood{$article.articleID}" data-promotionId="{$article.promotionId}">
                                                                <i class="icon--arrow-right is--large"></i>
                                                            </button>
                                                        {/block}
                                                    </div>
                                                {else}
                                                    <input type="hidden" name="addFreeGood{$article.articleID}" value="{$article.ordernumber}"/>
                                                    {block name='promotion_checkout_free_goods_info_button'}
                                                        <button class="free_goods-product--button hundred-percent btn is--primary is--align-center is--icon-right" data-type="hidden" data-name="addFreeGood{$article.articleID}" data-promotionId="{$article.promotionId}">
                                                            {s name="addToCart" namespace="frontend/swag_promotion/main"}Select{/s}
                                                            <i class="icon--arrow-right"></i>
                                                        </button>
                                                    {/block}
                                                {/if}
                                            {/block}
                                        </div>

                                    </div>
                                {/block}
                            {/foreach}
                        </div>
                    {/block}
                </div>
            {/block}
            <script type="text/javascript">
                var asyncConf = ~~('{$theme.asyncJavascriptLoading}');
                var freeGoodsFn = function() {
                    jQuery('.free_goods-product').promotionFreeGoodsSlider();
                    jQuery('.free_goods-product--selection').swSelectboxReplacement();
                    jQuery('body').swagPromotionHandleFreeGoods();
                };

                if (asyncConf === 1) {
                    document.asyncReady(freeGoodsFn);
                } else {
                    freeGoodsFn();
                }
            </script>
            <button class="free_goods-product--toLeft nav btn"><i class="icon--arrow-left"></i></button>
            <button class="free_goods-product--toRight nav btn"><i class="icon--arrow-right"></i></button>
        </div>
    {/if}
{/block}
