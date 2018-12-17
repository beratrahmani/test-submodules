{if $sBasketItem.abo_attributes.isAboArticle}
    {if $sTargetAction == 'cart'}
        {block name="frontend_abocommerce_cart_details"}
            <div class="abo--table-content table--content">
                <h3>{s namespace='frontend/checkout/abo_commerce_cart_item' name='AboCommerceCartItemMessageHeadline'}{/s}</h3>
                <span class="content--abo-details">{s namespace='frontend/checkout/abo_commerce_cart_item' name='AboCommerceCartItemMessage'}{/s}</span>
            </div>
        {/block}

    {elseif $sTargetAction == 'confirm'}
        {block name="frontend_abocommerce_confirm_details"}
            <div class="abo--table-content table--content">
                {block name="frontend_abocommerce_confirm_headline"}
                    <h3>{s namespace='frontend/checkout/abo_commerce_cart_item' name='AboCommerceCartItemMessageHeadline'}{/s}</h3>
                {/block}
                {block name="frontend_abocommerce_confirm_details_container"}
                    <div class="content--abo-information">
                        {block name="frontend_abocommerce_confirm_details_runtime"}
                            <div class="abo-information--runtime">
                                {s namespace='frontend/checkout/abo_commerce_confirm_item' name='AboCommerceConfirmItemRunTime'}{/s}<span class="is--bold"> {if $sBasketItem.aboCommerce.endlessSubscription}{s namespace="frontend/detail/abo_commerce_detail" name="AboCommerceEndlessSubscriptionInfo"}{/s}{else}{$sBasketItem.abo_attributes.swagAboCommerceDuration} {if $sBasketItem.aboCommerce.durationUnit eq 'months'}{s namespace='frontend/checkout/abo_commerce_confirm_item' name='AboCommerceConfirmItemMonths'}{/s}{else}{s namespace='frontend/checkout/abo_commerce_confirm_item' name='AboCommerceConfirmItemWeeks'}{/s}{/if}{/if}</span>
                            </div>
                        {/block}
                        {if $sBasketItem.aboCommerce.endlessSubscription}
                            {block name='frontend_account_abonnement_item_detail_period_of_notice'}
                                <div class="abo-information--subscription-termination">
                                    {s namespace='frontend/checkout/abo_commerce_confirm_item' name='AboCommerceConfirmItemPeriodOfNotice'}{/s}<span class="is--bold"> {if $sBasketItem.aboCommerce.directTermination eq true}{s namespace='frontend/checkout/abo_commerce_confirm_item' name="AboCommerceConfirmItemTerminateAnytime"}{/s}{else}{$sBasketItem.aboCommerce.periodOfNoticeInterval} {if $sBasketItem.aboCommerce.periodOfNoticeUnit eq 'months'}{s namespace='frontend/checkout/abo_commerce_confirm_item' name="AboCommerceConfirmItemMonths"}{/s}{else}{s namespace='frontend/checkout/abo_commerce_confirm_item' name="AboCommerceConfirmItemWeeks"}{/s}{/if}{/if}</span>
                                </div>
                            {/block}
                        {/if}
                        {block name="frontend_abocommerce_confirm_details_delivery_interval"}
                            <div class="abo-information--delivery-interval">
                                {s namespace='frontend/checkout/abo_commerce_confirm_item' name='AboCommerceConfirmItemDeliveryInterval'}{/s}<span class="is--bold"> {$sBasketItem.abo_attributes.swagAboCommerceDeliveryInterval} {if $sBasketItem.aboCommerce.deliveryIntervalUnit eq 'months'}{s namespace='frontend/checkout/abo_commerce_confirm_item' name='AboCommerceConfirmItemMonths'}{/s}{else}{s namespace='frontend/checkout/abo_commerce_confirm_item' name='AboCommerceConfirmItemWeeks'}{/s}{/if}</span>
                            </div>
                        {/block}
                        {if !$sBasketItem.aboCommerce.endlessSubscription}
                            {block name="frontend_abocommerce_confirm_details_delivery_count"}
                                <div class="abo-information--delivery-count">
                                    {s namespace='frontend/checkout/abo_commerce_confirm_item' name='AboCommerceConfirmItemDeliveryCount'}{/s}<span class="is--bold"> {$sBasketItem.abo_attributes.deliveryCount}</span>
                                </div>
                            {/block}
                        {/if}
                        {block name="frontend_abocommerce_confirm_details_amount_delivery"}
                            <div class="abo-information--amount-per-delivery">
                                {s namespace='frontend/checkout/abo_commerce_confirm_item' name='AboCommerceConfirmItemAmountPerDelivery'}{/s}<span class="is--bold"> {$sBasketItem.abo_attributes.amountPerDelivery|currency}</span>
                            </div>
                        {/block}
                        {if !$sBasketItem.aboCommerce.endlessSubscription}
                            {block name="frontend_abocommerce_confirm_details_total_amount"}
                                <div class="abo-information--total-amount">
                                    {s namespace='frontend/checkout/abo_commerce_confirm_item' name='AboCommerceConfirmItemAmount'}{/s}<span class="is--bold"> {$sBasketItem.abo_attributes.amount|currency}</span>
                                </div>
                            {/block}
                        {/if}
                    </div>
                {/block}
            </div>
        {/block}
    {/if}
{/if}
