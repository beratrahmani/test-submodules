{namespace name=frontend/plugins/b2b_debtor_plugin}
{strip}
    {if $productName}
        <h4 class="headline-product">{$productName}</h4>
    {else}
        <h5 class="headline-notfound">{s name="ProductNotFound"}Product not found{/s}</h5>
    {/if}
{/strip}