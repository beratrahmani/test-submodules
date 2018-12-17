{namespace name=frontend/plugins/b2b_debtor_plugin}

{if $error === 'file'}
    {include file="frontend/_includes/messages.tpl" type="error" content="{s name="FileExtensionNotValid"}The file extension ist not supported. Supported file extensions are CSV, XLS, XLSX{/s}"}
{elseif $error === 'products'}
    {include file="frontend/_includes/messages.tpl" type="error" content="{s name="NoProductsFound"}No products found in file.{/s}"}
{elseif $notMatchingProducts}
    {include file="frontend/_includes/messages.tpl" type="error" content="{s name="OrderNumbersNotFound"}There are one ore more errors occured while importing the file. The following productnumbers could not been found:{/s} <br> <ul>{foreach $notMatchingProducts as $number}<li>&bull; {$number}</li>{/foreach}</ul>"}
{/if}

<div class="b2b--grid-container fast-order-inputs">
    <table class="table--fastorder component-table b2b--component-grid" data-new-auto-row="true">
        <thead>
        <tr>
            <th class="col-name">{s name="ProductName"}Product Name{/s}</th>
            <th class="col-number">{s name="Productnumber"}Productnumber{/s}</th>
            <th class="col-quantity">{s name="Quantity"}Quantity{/s}</th>
        </tr>
        </thead>
        <tbody>
        {foreach $matchingProducts as $product}
            <tr>
                <td class="col-headline"><h4 class="headline-product">{$product->name}</h4></td>
                <td><input type="text" class="input-ordernumber" name="products[{$product@index}][referenceNumber]" value="{$product->referenceNumber}" /></td>
                <td><input type="number" min="1" class="input-quantity" name="products[{$product@index}][quantity]" value="{$product->quantity}" /></td>
            </tr>
            {$rowIndex = $product@index+1}
        {/foreach}
        <tr data-index="{$rowIndex|default:0}">
            <td class="col-headline">
                {if $row->name}
                    <h4 class="headline-product">
                        {$row->name}
                    </h4>
                {else}
                    <p class="headline-product-placeholder">
                        {s name="ChooseProduct"}Choose a product{/s}
                    </p>
                {/if}
            </td>
            <td>
                <div class="b2b--search-container">
                    <input type="text" placeholder="{s name="Productnumber"}Productnumber{/s}" class="input-ordernumber" name="products[{$rowIndex|default:0}][referenceNumber]" data-product-search="{url controller=b2bproductsearch action=searchProduct}" value="" />
                </div>
            </td>
            <td><input type="number" placeholder="{s name="Quantity"}Quantity{/s}" min="1" class="input-quantity b2b--search-quantity" name="products[{$rowIndex|default:0}][quantity]" value="" /></td>
        </tr>
        </tbody>
    </table>
</div>
