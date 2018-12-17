{namespace name='frontend/checkout/bundle'}
{block name='swag_bundle_basket_error'}
    {* only display errors when validation fail *}
    {if $sBundleValidation|@count > 0}

        {* Iterate the errors und put them into an array *}
        {$errors=[]}
        {foreach $sBundleValidation as $validation}

            {block name='swag_bundle_basket_error_deleted_bundle'}
                {* bundle was deleted *}
                {if $validation.deletedBundle == 1}
                    {$errors[]="{s name='CheckoutBundleError'}{/s} - {$validation.bundle} {s name='CheckoutDeletedBundleMessage'}{/s}"}
                {/if}
            {/block}

            {block name='swag_bundle_basket_error_no_bundle_stock'}
                {* no bundle stock *}
                {if $validation.noStock == 1}
                    {$errors[]="{s name='CheckoutBundleError'}{/s} - {$validation.bundle} {s name='CheckoutBundleErrorNoStock'}{/s}"}
                {/if}
            {/block}

            {block name='swag_bundle_basket_error_bundle_customergroup'}
                {* not for customer group *}
                {if $validation.notForCustomerGroup == 1}
                    {$errors[]="{s name='CheckoutBundleError'}{/s} - {$validation.bundle} {s name='CheckoutBundleErrorCustomerGroup'}{/s}"}
                {/if}
            {/block}
        {/foreach}

        {* we include the error messages *}
        {include file='frontend/_includes/messages.tpl' type='error' list=$errors}
    {/if}
{/block}
