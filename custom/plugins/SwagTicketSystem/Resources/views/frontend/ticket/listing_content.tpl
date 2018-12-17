{block name='frontend_ticket_entry'}
	<div class="ticketsystem--entry panel--tr {cycle values=',is--odd'}">

		{* date *}
		{block name='frontend_ticketsystem_entry_date'}
			<div class="ticketsystem--date panel--td column--date">

				{block name='frontend_ticketsystem_entry_date_label'}
					<div class="column--label is--bold">
						{s namespace='frontend/ticket/listing_content' name='TicketInfoDate'}{/s}
					</div>
				{/block}

				{block name='frontend_ticketsystem_entry_date_value'}
					<div class="column--value">
						{$ticketItem.receipt|date:DATETIME_MEDIUM}
					</div>
				{/block}
			</div>
		{/block}

		{* id *}
		{block name='frontend_ticketsystem_entry_id'}
			<div class="ticketsystem--id panel--td column--id">
				{block name='frontend_ticketsystem_entry_id_label'}
					<div class="column--label is--bold">
						{s namespace='frontend/ticket/listing_content' name='TicketInfoId'}{/s}
					</div>
				{/block}

				{block name='frontend_ticketsystem_entry_id_value'}
					<div class="column--value">
						#{$ticketItem.id}
					</div>
				{/block}
			</div>
		{/block}

		{* status *}
		{block name='frontend_ticketsystem_entry_status'}
			<div class="ticketsystem--status panel--td column--status">
				{block name='frontend_ticketsystem_entry_status_label'}
					<div class="column--label is--bold">
						{s namespace='frontend/ticket/listing_content' name='TicketInfoStatus'}{/s}
					</div>
				{/block}

				{block name='frontend_ticketsystem_entry_status_value'}
					<div class="column--value status--{$ticketItem.statusId}">
						{$ticketItem.status}
					</div>
				{/block}
			</div>
		{/block}

		{* actions *}
		{block name='frontend_ticketsystem_entry_actions'}
			<div class="ticketsystem--actions panel--td column--actions">
				<a href="{url controller='ticket' action='detail' tid=$ticketItem.uniqueId}" class="btn is--secondary is--small">
					{s namespace='frontend/ticket/listing_content' name='TicketLinkDetails'}{/s}
				</a>
			</div>
		{/block}
	</div>
{/block}