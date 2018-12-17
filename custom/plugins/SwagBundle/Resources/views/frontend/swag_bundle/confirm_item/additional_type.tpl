{block name='swag_bundle_cart_item'}
    {if $sBasketItem.modus == constant('\SwagBundle\Components\BundleBasketInterface::BUNDLE_DISCOUNT_BASKET_MODE')}
        {include file='frontend/checkout/confirm_item_rebate.tpl'}
    {/if}
{/block}
