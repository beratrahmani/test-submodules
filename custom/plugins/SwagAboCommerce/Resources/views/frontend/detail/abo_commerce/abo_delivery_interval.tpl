{namespace name="frontend/detail/abo_commerce_detail"}

{block name='abo_commerce_abo_selection_delivery_interval'}
    <div class="abo--delivery-interval-label">
        {block name='abo_commerce_abo_selection_delivery_interval_label'}
            <label class="delivery-interval--label" for="delivery-interval">{s name="AboCommerceIntervalSelectDeliveryInterval"}{/s}</label>
        {/block}
    </div>
{/block}

{block name='abo_commerce_abo_selection_delivery_interval_select'}
    <div class="abo--delivery-interval-select select-field">
        <select name="delivery-interval" class="abo--delivery-interval">
            {for $deliveryInterval=$aboCommerce.minDeliveryInterval to $aboCommerce.maxDeliveryInterval}
                {block name="abo_commerce_abo_selection_delivery_interval_select_option"}
                    {strip}<option value="{$deliveryInterval}">
                        {$deliveryInterval}&nbsp;
                        {if $aboCommerce.deliveryIntervalUnit == "weeks"}
                            {s name="AboCommerceIntervalSelectWeeks"}{/s}
                        {else}
                            {s name="AboCommerceIntervalSelectMonths"}{/s}
                        {/if}{/strip}
                    </option>
                {/block}
            {/for}
        </select>
    </div>
{/block}
