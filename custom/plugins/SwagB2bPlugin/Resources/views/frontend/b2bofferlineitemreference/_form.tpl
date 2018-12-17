{namespace name=frontend/plugins/b2b_debtor_plugin}

{foreach $errors as $error}
    <div class="modal--errors error--list">
        {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
    </div>
{/foreach}

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="Productnumber"}Productnumber{/s}: *
    </div>
    <div class="block box--input is--full">
        <div class="b2b--search-container">
            <input type="text"
                   name="referenceNumber"
                   placeholder="{s name="Productnumber"}Productnumber{/s}"
                   class="input-ordernumber"
                   autocomplete="off"
                   data-product-search="{url controller=b2bproductsearch action=searchProduct}">
        </div>
    </div>
</div>

<div class="block-group b2b--form">
    <div class="block box--label is--full">
        {s name="Quantity"}Quantity{/s}: *
    </div>
    <div class="block box--input is--full">
        <input type="number" name="quantity" value="{$lineItemReference->quantity}"
               class="search--quantity b2b--search-quantity"
               placeholder="{s name="Quantity"}Quantity{/s}"
               min="{$lineItemReference->minPurchase|default:1}"
               step="{$lineItemReference->purchaseStep|default:1}"
               {if $lineItemReference->maxPurchase}max="{$lineItemReference->maxPurchase}"{/if}
        >
    </div>
</div>