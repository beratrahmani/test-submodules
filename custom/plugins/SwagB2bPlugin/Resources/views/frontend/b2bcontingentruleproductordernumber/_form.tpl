{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="block-group b2b--form">
    <div class="block box--label  is--full">
        {s name="Productnumber"}Productnumber{/s}: *
    </div>
    <div class="block box--input  is--full">
        <div class="b2b--search-container">
            <input type="text"
                   name="productOrderNumber"
                   id="productOrderNumber"
                   value="{$rule->productOrderNumber}"
                   data-product-search="{url controller=b2bproductsearch action=searchProduct}"
                   class="input-ordernumber"
                   placeholder="{s name="ProductOrderNumber"}Product Ordernumber{/s}">
        </div>
    </div>
</div>
