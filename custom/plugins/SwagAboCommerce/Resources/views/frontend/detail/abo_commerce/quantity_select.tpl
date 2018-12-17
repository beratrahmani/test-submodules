{if $sArticle.laststock && !$sArticle.sVariants && $sArticle.instock < $sArticle.maxpurchase}
    {assign var=maxQuantity value=$sArticle.instock}
{else}
    {assign var=maxQuantity value=$sArticle.maxpurchase}
{/if}

{* Normal quantity select box *}
{block name='frontend_abo_commerce_single_quantity_select'}
    <div class="select-field">
        <select class="abo--quantity-select quantity--select"
                id="sQuantity"
                name="sQuantity"
                data-maxQuantity="{$maxQuantity}"
                data-isLimited="{if $aboCommerce.isLimited}true{else}false{/if}"
                data-maxQuantityPerWeek="{$aboCommerce.maxQuantityPerWeek}"
                data-packUnit="{$sArticle.packunit}"
                data-durationUnit="{$aboCommerce.durationUnit}">
            {block name='frontend_abo_commerce_single_quantity_select_condition'}
                {if $aboCommerce.isLimited}
                    {section name="i" start=1 max=$aboCommerce.maxQuantityPerWeek loop=$maxQuantity step=1}
                        <option value="{$smarty.section.i.index}">{$smarty.section.i.index}{if $sArticle.packunit} {$sArticle.packunit}{/if}</option>
                    {/section}
                {else}
                    {section name="i" start=$sArticle.minpurchase loop=$maxQuantity step=$sArticle.purchasesteps}
                        <option value="{$smarty.section.i.index}">{$smarty.section.i.index}{if $sArticle.packunit} {$sArticle.packunit}{/if}</option>
                    {/section}
                {/if}
            {/block}
        </select>
    </div>
{/block}
