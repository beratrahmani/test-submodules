{extends file="parent:frontend/account/orders.tpl"}
{namespace name="frontend/abo_commerce/orders"}

{* Breadcrumb *}
{block name='frontend_index_start'}

    {$smarty.block.parent}
    {include file="frontend/abo_commerce/orders/start.tpl" scope="parent"}
{/block}

{* Main content *}
{block name='frontend_index_content'}
    {include file="frontend/abo_commerce/orders/content.tpl"}
{/block}
