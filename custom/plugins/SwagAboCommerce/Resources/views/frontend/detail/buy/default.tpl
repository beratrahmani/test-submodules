{block name="frontend_abo_commerce_delivery_total_text_info"}
    <div class="abo--info">
        <strong class="abo--info-delivery-duration">
            {block name="frontend_abo_commerce_delivery_total_text_info_delivery"}
                <span class="abo--info-delivery">{$deliveryAmount * $sArticle.minpurchase}&nbsp;</span>
                {s namespace="frontend/detail/abo_commerce_detail" name="aboCommerceAmountInfoIn"}{/s}
            {/block}
            {block name="frontend_abo_commerce_delivery_total_text_info_duration"}
                <span class="abo--info-duration">&nbsp;{$deliveryAmount}&nbsp;</span>
                {s namespace="frontend/detail/abo_commerce_detail" name="aboCommerceAmountInfoDeliveries"}{/s}
            {/block}
        </strong>
    </div>
{/block}
{block name="frontend_abo_commerce_delivery_info_additional"}
    <div class="abo--info-additional">
        {block name="frontend_abo_commerce_delivery_direct"}
            {s namespace="frontend/detail/abo_commerce_detail" name="aboCommerceDeliveryAdditionalDirect"}{/s}
        {/block}
        {block name="frontend_abo_commerce_delivery_following"}
            <span class="abo--info-delivery-additional-following">{$deliveryAmount - 1}</span>
            {s namespace="frontend/detail/abo_commerce_detail" name="aboCommerceDeliveryAdditionalFollowing"}{/s}
        {/block}
    </div>
{/block}