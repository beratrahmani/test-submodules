{extends file='parent:frontend/account/sidebar.tpl'}

{block name='frontend_account_menu_link_downloads'}
	{block name='frontend_account_swag_ticket_system_menu_link_notes'}
		{include file="frontend/swag_ticket_system/account/menu_link_notes.tpl"}
	{/block}
	{$smarty.block.parent}
{/block}

{* sidebar *}
{block name="frontend_index_left_categories"}
	{block name="frontend_account_sidebar"}
		{include file="frontend/account/sidebar.tpl"}
	{/block}
	{$smarty.block.parent}
{/block}