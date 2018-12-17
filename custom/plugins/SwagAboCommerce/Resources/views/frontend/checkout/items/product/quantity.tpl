{if $sBasketItem.aboCommerce.deliveryIntervalUnit eq 'months'}
    {$deliveryIntervalWeeks = $sBasketItem.abo_attributes.swagAboCommerceDeliveryInterval * 4}
{else}
    {$deliveryIntervalWeeks = $sBasketItem.abo_attributes.swagAboCommerceDeliveryInterval}
{/if}

{assign var="maxUnits" value=$deliveryIntervalWeeks * $sBasketItem.aboCommerce.maxQuantityPerWeek}

{block name="frontend_checkout_abocommerce_quantity"}
    <div class="panel--td column--quantity is--align-right">
        {if $sBasketItem.modus == 0}
            <div class="column--label quantity--label">
                {s name="CartColumnQuantity" namespace="frontend/checkout/cart_header"}{/s}
            </div>
            <form name="basket_change_quantity{$sBasketItem.id}" class="select-field" method="post" action="{url action='changeQuantity' sTargetAction=$sTargetAction}">
                {block name="frontend_checkout_abocommerce_quantity_selection"}
                    <select name="sQuantity" data-auto-submit="true">
                        {section name="i" start=$sBasketItem.minpurchase loop=$maxUnits+1 step=$sBasketItem.purchasesteps}
                            <option value="{$smarty.section.i.index}" {if $smarty.section.i.index==$sBasketItem.quantity}selected="selected"{/if}>
                                {$smarty.section.i.index}
                            </option>
                        {/section}
                    </select>
                {/block}
                <input type="hidden" name="sArticle" value="{$sBasketItem.id}"/>
            </form>
        {else}
            &nbsp;
        {/if}
    </div>
{/block}
