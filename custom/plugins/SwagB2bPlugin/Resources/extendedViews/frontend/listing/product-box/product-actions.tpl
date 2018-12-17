{extends file="parent:frontend/listing/product-box/product-actions.tpl"}

{* Note button *}
{block name='frontend_listing_box_article_actions_save'}
    {if !$b2bSuite || !{b2b_acl_check controller=b2borderlist action=index}}
        {$smarty.block.parent}
    {/if}
{/block}