{extends file='parent:frontend/address/form.tpl'}

{block name='frontend_address_form_form_inner'}
    {$smarty.block.parent}

    {block name='frontend_address_form_abo_commerce'}
        {if $formData.aboOrderId}
            <input type="hidden" name="address[aboCommerce][orderId]" value="{$formData.aboOrderId}">
        {/if}
    {/block}
{/block}
