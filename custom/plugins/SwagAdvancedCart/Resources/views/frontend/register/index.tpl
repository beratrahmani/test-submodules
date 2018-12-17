{extends file="parent:frontend/register/index.tpl"}

{block name="frontend_index_header"}
    {* Set sTarget to 'account' to set $toAccount to true, so e.g. the order steps won't be loaded *}
    {if $sTarget === 'wishlist'}
        {$sTarget = 'account'}
        {$sTargetChanged = true}
    {/if}

    {$smarty.block.parent}
{/block}

{block name="frontend_register_index_form"}
    {* Change sTarget back for the register / login form, so the redirect works properly *}
    {if $sTargetChanged}
        {$sTarget = 'wishlist'}
    {/if}

    {$smarty.block.parent}
{/block}

{block name="frontend_index_footer"}
    {* 5.2 compatibility - prevent minimal footer *}
    {if $sTarget === 'wishlist'}
        {$sTarget = 'account'}
    {/if}
    {$smarty.block.parent}
{/block}
