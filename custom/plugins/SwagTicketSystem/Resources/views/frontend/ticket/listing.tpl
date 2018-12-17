{extends file='parent:frontend/account/index.tpl'}

{block name="frontend_index_body_classes"}{$smarty.block.parent} is--ticket-system{/block}

{* Breadcrumb *}
{block name='frontend_index_start'}
	{$smarty.block.parent}
	{block name="frontend_index_swag_ticket_system_breadcrumb"}
		{$sBreadcrumb[] = ['name' => "{s namespace="frontend/account/sidebar" name="TicketsystemAccountLinkListing"}{/s}", 'link' => {url}]}
	{/block}
{/block}

{* Main content *}
{block name='frontend_index_content'}
	{block name="frontend_index_swag_ticket_system_content"}
	<div class="content block ticketsystem--overview-content account--content">
		{block name='frontend_index_content_ticketsystem'}
			<div class='ticketsytem'>
				{block name='frontend_index_content_ticketsystem_headline'}
					<div class="account--welcome">
						<h1 class="panel--title">{s name='TicketHeadline'}{/s}</h1>
					</div>
				{/block}
				{block name='frontend_index_content_ticketsystem_panel'}
					<div class="ticketsystem panel">

						{block name='frontend_ticketsystem_table'}
							<div class="panel--table">

								{block name='frontend_ticket_table_head'}
									<div class="ticketsystem--table-header panel--tr">
										{include file='frontend/ticket/listing_header.tpl'}
									</div>
								{/block}

								{block name='frontend_ticketsystem_table_content'}
									{foreach $entries as $ticketItem}
										{include file='frontend/ticket/listing_content.tpl'}
									{/foreach}
								{/block}
							</div>
						{/block}

						{block name="frontend_ticketsystem_actions_paging"}
							{if $sNumberPages > 1}
								<div class="ticketsystem--pagination">
									<div class="panel--paging">
										{if $sPage != 1}
											{block name="frontend_ticketsystem_actions_paging_previous"}
												{* link first page *}
												<a href="{url controller='ticket' action='listing' sPage="1"}" class="paging--link"><i class="icon--arrow-left"></i><i class="icon--arrow-left"></i></a>

												{* link page back *}
												<a href="{url controller='ticket' action='listing' sPage="{$sPage - 1}"}" class="paging--link"><i class="icon--arrow-left"></i></a>
											{/block}
										{/if}

                                        {* link current page *}
										<a href="{url controller='ticket' action='listing' sPage="{$sPage}"}" class="paging--link is--active">{$sPage}</a >

										{if $sPage != $sNumberPages}
											{block name="frontend_ticketsystem_actions_paging_next"}
												{* link page forward *}
												<a href="{url controller='ticket' action='listing' sPage="{$sPage + 1}"}" class="paging--link paging--next"><i class="icon--arrow-right"></i></a>

												{* link last page *}
												<a href="{url controller='ticket' action='listing' sPage="{$sNumberPages}"}" class="paging--link paging--next"><i class="icon--arrow-right"></i><i class="icon--arrow-right"></i></a>
                                            {/block}
                                        {/if}

										{block name="frontend_ticketsystem_actions_paging_text"}
											{* page entries *}
											<span class="paging--display">{s name="TicketPagingFrom" namespace="frontend/ticket/listing_content"}from{/s} <strong>{$sNumberPages}</strong></span>
                                        {/block}
									</div>
								</div>
							{/if}
						{/block}
					</div>
				{/block}
			</div>
		{/block}
	<div>
	{/block}
{/block}