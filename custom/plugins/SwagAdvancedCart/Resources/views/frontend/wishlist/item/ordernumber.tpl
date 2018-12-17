{block name='frontend_wishlist_item_add_form_quantity_select'}
    {block name='frontend_wishlist_item_add_form_quantity_select_max_quantity'}
        {$maxQuantity=$item.article.maxpurchase+1}
        {if $item.article.laststock && $item.article.instock < $item.article.maxpurchase}
            {$maxQuantity=$item.article.instock+1}
        {/if}
    {/block}
    {block name='frontend_wishlist_item_add_form_quantity_select_div'}
        <div class="advanced-cart--quantity">
            {if !$hideDelete}
                {block name='frontend_wishlist_item_add_form_quantity_select_tag'}
                    <select class="advancedCartQuantity" name="sQuantity" data-item-id="{$item.id}" data-quantity-url="{url controller='wishlist' action='changeQuantity'}">
                        {section name="i" start=$item.article.minpurchase loop=$maxQuantity step=$item.article.purchasesteps}
                            <option value="{$smarty.section.i.index}"{if $smarty.section.i.index == $item.quantity} selected="selected"{/if}>{$smarty.section.i.index}{if $item.article.packunit} {$item.article.packunit}{/if}</option>
                        {/section}
                    </select>
                {/block}
            {else}
                {$item.quantity}
            {/if}
        </div>
    {/block}
{/block}
