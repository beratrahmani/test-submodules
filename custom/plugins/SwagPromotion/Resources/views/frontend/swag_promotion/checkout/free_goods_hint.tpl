{namespace name="frontend/swag_promotion/main"}

<div class="promotion--free-goods-block block-group">
    {block name="checkout_ajax_add_information_promotion_image"}
        <div class="promotion--free-goods-image">
            <div class="promotion--badge">
                <i class="icon--percent2"></i>
            </div>
        </div>
    {/block}

    {block name="checkout_ajax_add_information_promotion_hint_box"}
        <div class="promotion--free-goods-hint-box">
            {block name="checkout_ajax_add_information_promotion_hint"}
                <div class="promotion--free-goods-hint">
                    <div class="promotion--free-goods-hint-inner">
                        {s name="freeGoodsHint"}{/s}
                    </div>
                </div>
            {/block}

            {block name="checkout_ajax_add_information_promotion_link"}
                <div class="promotion--link-to-free-goods">
                    <div class="promotion--link-to-free-goods-inner">
                        <a href="{url action=cart}#promotion-free-goods"
                           title="{s name="freeGoodsLink"}{/s}"
                           class="is--icon-right">
                            {s name="freeGoodsLink"}{/s} <i class="icon--arrow-right"></i>
                        </a>
                    </div>
                </div>
            {/block}
        </div>
    {/block}
</div>
