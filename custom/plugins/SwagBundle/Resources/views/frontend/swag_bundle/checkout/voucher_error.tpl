{$message = "{s name='CheckoutBundleVoucherMinimumCharge' namespace='frontend/checkout/bundle'}{/s}"|cat:$sVoucherError[0]}
{include file="frontend/_includes/messages.tpl" type="error" content=$message}
