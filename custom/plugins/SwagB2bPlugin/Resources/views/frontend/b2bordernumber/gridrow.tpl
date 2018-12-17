{namespace name=frontend/plugins/b2b_debtor_plugin}
<tr {strip}
        data-save-url="
            {if $row->id}
                {if {b2b_acl_check controller=b2bordernumber action="update"}}
                    {url action="update"}
                {/if}
            {else}
                {if {b2b_acl_check controller=b2bordernumber action="create"}}
                    {url action="create"}
                {/if}
            {/if}"
    {/strip}
        data-id="{$row->id}">
    <td class="col-headline">
        {if $row->name}
            <h4 class="headline-product">{$row->name}</h4>
        {else}
            <p class="headline-product-placeholder">{s name="ChooseProduct"}Choose a product{/s} </p>
        {/if}
    </td>
    <td>
        <div class="b2b--search-container">
            <input type="text" class="input-ordernumber" placeholder="{s name="Productnumber"}Productnumber{/s}" name="productNumber" autocomplete="off"  data-product-search="{url controller=b2bproductsearch action=searchProduct}" value="{$row->orderNumber}" />
        </div>
    </td>
    <td><input type="text" class="input-customordernumber" placeholder="{s name="CustomProductOrderNumber"}Custom productnumber{/s}" name="customProductNumber" autocomplete="off" value="{$row->customOrderNumber}"/></td>
    <td data-label="{s name="Actions"}Actions{/s}" class="col-actions">
        <button name="saveButton" title="{s name="SaveCustomOrderNumber"}Save custom ordernumber{/s}" type="button" class="btn btn--edit is--small is--hidden {if $row->id}{b2b_acl controller=b2bordernumber action="update"}{else}{b2b_acl controller=b2bordernumber action="create"}{/if}"><i class="icon--disk"></i></button>

        <form action="{url action=remove}" method="post" class="form--inline">
            <input type="hidden" name="id" value="{$row->id}">

            <button title="{s name="DeleteCustomOrderNumber"}Delete custom ordernumber{/s}" type="submit" class="btn is--small component-action-delete {if !$row->id}is--hidden{/if} {b2b_acl controller=b2bordernumber action=remove}"
                    data-confirm="true"
                    data-confirm-url="{url controller="b2bconfirm" action="remove"}">
                <i class="icon--trash"></i>
            </button>
        </form>
    </td>
</tr>