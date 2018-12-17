{if $sBasketItem.swagLiveShoppingId > 0 && !$sBasketItem.attribute.bundleId}
    <div class="cart--badge">
        <span>{s name="cartBadgeText" namespace="frontend/checkout/main"}LIVE{/s}</span>
    </div>
{/if}
