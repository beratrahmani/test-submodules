{extends file="parent:frontend/register/login.tpl"}

{block name='frontend_register_login_form'}
	{block name='frontend_register_login_form_swag_ticket_system_slt_target'}
		{if $sTargetAction === 'swagTicketSystemListing'}
			{$sTargetAction = 'listing'}
			{$sTarget = 'ticket'}
		{elseif $sTargetAction === 'swagTicketSystemRequest'}
			{$sTargetAction = 'request'}
			{$sTarget = 'ticket'}
		{/if}
    {/block}

	{$smarty.block.parent}
{/block}