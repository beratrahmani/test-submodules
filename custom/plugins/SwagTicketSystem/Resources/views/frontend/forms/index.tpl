{extends file='parent:frontend/forms/index.tpl'}

{* Account Sidebar *}
{block name="frontend_index_left_categories"}
	{block name="frontend_account_sidebar"}
		{block name="frontend_account_swag_ticket_system_sidebar"}
			{include file="frontend/swag_ticket_system/account/sidebar.tpl"}
		{/block}
	{/block}
	{$smarty.block.parent}
{/block}

{* hide or show sidebar categories *}
{block name='frontend_index_left_categories_inner'}
	{block name="frontend_index_swag_ticket_system_left_categories_inner"}
		{include file="frontend/swag_ticket_system/index/left_categories_inner.tpl"}
	{/block}
{/block}

{* hide or show site navigation *}
{block name='frontend_index_left_menu'}
	{block name="frontend_index_swag_ticket_system_left_menu"}
		{include file="frontend/swag_ticket_system/index/left_menu.tpl"}
	{/block}
{/block}