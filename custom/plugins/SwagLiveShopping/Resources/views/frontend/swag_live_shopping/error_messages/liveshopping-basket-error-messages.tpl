{* only display errors when validation fail *}
{if $sLiveShoppingValidation|@count > 0}
    {* Iterrate the errors und put them into an array *}
    {$errors=[]}
    {foreach $sLiveShoppingValidation as $validation}

        {* no liveshopping *}
        {if $validation.noLiveShoppingDetected == 1}
            {$errors[]="{s name="CheckoutLiveShoppingError"}Liveshopping Aktion{/s} {if $validation.article} - {$validation.article}: {/if}<br>{s name="CheckoutLiveShoppingDetected"}Aktion konnte nicht ermittelt werden.{/s} <a href='{url controller="checkout" action='deleteArticle' sDelete=$validation.basketId sTargetAction=$sTargetAction}'> {s name="CheckoutLiveShoppingRemove"}Klicken Sie hier um den Artikel zu entfernen.{/s}</a>"}
        {/if}

        {* liveshoping not active anymore *}
        {if $validation.noMoreActive == 1}
            {$errors[]="{s name="CheckoutLiveShoppingError"}Live shopping aktion{/s} - {$validation.article}:<br>{s name="CheckoutLiveShoppingEnded"}Aktion ist ausgelaufen.{/s} <a href='{url controller="checkout" action='deleteArticle' sDelete=$validation.basketId sTargetAction=$sTargetAction}'> {s name="CheckoutLiveShoppingRemove"}Klicken Sie hier um den Artikel zu entfernen.{/s}</a>"}
        {/if}

        {* liveshopping product not for current customer group *}
        {if $validation.notForCurrentCustomerGroup == 1}
            {$errors[]="{s name="CheckoutLiveShoppingError"}Live shopping aktion{/s} - {$validation.article}:<br>{s name="CheckoutLiveShoppingCustomerGroup"}Aktion ist für Ihre Kundengruppe nicht freigeschaltet.{/s} <a href='{url controller="checkout" action='deleteArticle' sDelete=$validation.basketId sTargetAction=$sTargetAction}'> {s name="CheckoutLiveShoppingRemove"}Klicken Sie hier um den Artikel zu entfernen.{/s}</a>"}
        {/if}

        {* liveshopping product not in stock *}
        {if $validation.noStock == 1}
            {$errors[]="{s name="CheckoutLiveShoppingError"}Live shopping aktion{/s} - {$validation.article}:<br>{s name="CheckoutLiveShoppingStock"}Aktion ist nicht mehr auf Lager.{/s}<a href='{url controller="checkout" action='deleteArticle' sDelete=$validation.basketId sTargetAction=$sTargetAction}'> {s name="CheckoutLiveShoppingRemove"}Klicken Sie hier um den Artikel zu entfernen.{/s}</a>"}
        {/if}

        {* liveshopping product not for shop *}
        {if $validation.notForShop == 1}
            {$errors[]="{s name="CheckoutLiveShoppingError"}Live shopping aktion{/s} - {$validation.article}:<br>{s name="CheckoutLiveShoppingShop"}Aktion nicht für den sub shop frei gegeben.{/s}<a href='{url controller="checkout" action='deleteArticle' sDelete=$validation.basketId sTargetAction=$sTargetAction}'> {s name="CheckoutLiveShoppingRemove"}Klicken Sie hier um den Artikel zu entfernen.{/s}</a>"}
        {/if}

        {* liveshopping product out of date *}
        {if $validation.outOfDate == 1}
            {$errors[]="{s name="CheckoutLiveShoppingError"}Live shopping aktion{/s} - {$validation.article}:<br>{s name="CheckoutLiveShoppingEnded"}Aktion ist ausgelaufen.{/s}<a href='{url controller="checkout" action='deleteArticle' sDelete=$validation.basketId sTargetAction=$sTargetAction}'> {s name="CheckoutLiveShoppingRemove"}Klicken Sie hier um den Artikel zu entfernen.{/s}</a>"}
        {/if}

        {* liveshopping product stock overflow  *}
        {if $validation.stockOverFlow == 1}
            {$errors[]="{s name="CheckoutLiveShoppingError"}Live shopping aktion{/s} - {$validation.article}:<br>{s name="CheckoutLiveShoppingStockOverFlow"}Die sich im Warenkorb befindene Artikel Menge übersteigt den aktuellen Lagerbestand.{/s}<a href='{url controller="checkout" action='deleteArticle' sDelete=$validation.basketId sTargetAction=$sTargetAction}'> {s namespace="frontend/checkout/live_shopping" name="CheckoutLiveShoppingRemove"}Klicken Sie hier um den Artikel zu entfernen.{/s}</a>"}
        {/if}
    {/foreach}

    {* we include the error messages *}
    {include file='frontend/_includes/messages.tpl' type='error' list=$errors}
{/if}
