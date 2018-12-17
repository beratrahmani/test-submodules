{block name="frontend_abo_commerce_delivery_total_text_info"}
    <div class="abo--info">
        <strong class="abo--info-delivery-duration">
            {block name="frontend_abo_commerce_endless_subscription_info"}
                {s namespace="frontend/detail/abo_commerce_detail" name="AboCommerceEndlessSubscriptionInfo"}{/s}
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
            {s namespace="frontend/detail/abo_commerce_detail" name="aboCommerceDeliveryAdditionalFollowing"}{/s}
        {/block}
    </div>
{/block}