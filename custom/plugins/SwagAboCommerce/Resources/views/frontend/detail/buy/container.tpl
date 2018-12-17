{* delivery quantity and total deliveries *}
{$deliveryAmount = ($aboCommerce.minDuration / $aboCommerce.minDeliveryInterval) + 1}
{$path = ''}
{block name='frontend_abo_commerce_delivery_total_text'}
        <div class="abo--info-wrapper{if !$aboCommerce.isExclusive} is--hidden{/if}">
            {block name="frontend_abo_commerce_delivery_total_text_amount"}
                <strong class="abo--info-quantity-text">{s namespace="frontend/detail/abo_commerce_detail" name="aboCommerceAmountInfoEntireAmount"}{/s}</strong>
            {/block}

            {if $aboCommerce.endlessSubscription}
                {$path = 'frontend/detail/buy/endless.tpl'}
            {else}
                {$path = 'frontend/detail/buy/default.tpl'}
            {/if}

            {if $path != ''}
                {include file=$path}
            {/if}
        </div>
{/block}
