{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="block-group b2b--form">
    <div class="block box--label  is--full">
        {s name="ProductPrice"}Product price{/s}: *
    </div>
    <div class="block box--input  is--full">
        <input type="number" step="any" min="0" name="productPrice" id="productPrice" value="{$rule->productPrice}"
               placeholder="{s name="ProductPrice"}Product price{/s}">
    </div>
</div>
