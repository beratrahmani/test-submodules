{extends file='parent:frontend/account/index.tpl'}

{block name="frontend_index_body_classes"}{$smarty.block.parent} is--ticket-system{/block}

{* Breadcrumb *}
{block name='frontend_index_start'}
    {block name="frontend_index_swag_ticket_system_breadcrumb"}
        {assign var='sBreadcrumb' value=[['name'=>"{s namespace="frontend/account/index" name='AccountTitle'}{/s}", 'link' => {url controller='account' action='index'}]]}
        {$sBreadcrumb[] = ['name' => "{s namespace="frontend/account/sidebar" name="TicketsystemAccountLinkListing"}{/s}", 'link' => {url controller='ticket' action='listing'}]}
        {$sBreadcrumb[] = ['name'=>"{s name='TicketDetailInfoTicket'}{/s} #{$ticketDetails.id}", 'link' => {url controller='ticket' action='detail' tid=$ticketDetails.uniqueId}]}
    {/block}
{/block}

{* Main content *}
{block name='frontend_index_content'}
    {block name="frontend_index_swag_ticket_system_content"}
        <div class="content block account--content ticketsystem--detail-content">

            {* ticket not found *}
            {if !$ticketDetails.id}
                {block name='ticketsystem_alert'}
                    {block name='ticketsyste_alert_message'}
                        {include file="frontend/_includes/messages.tpl" type="error" content="{s name='TicketDetailInfoEmpty'}{/s}"}
                    {/block}
                {/block}
            {else}
                {* ticket headline *}
                {block name='ticketsystem_headline'}
                    <div class="account--welcome">
                        <h1 class="panel--title">{s name='TicketDetailInfoTicket'}{/s} #{$ticketDetails.id}</h1>
                    </div>
                {/block}

                {if $userAttachments}
                    <div class="account--attachments-overview panel">
                        {block name="ticketsystem_attachments_table"}
                            <div class="panel--table">
                                {block name="ticketsystem_attachments_table_header"}
                                    <div class="attachments--table-header panel--tr">
                                        <div class="panel--th column--filename">{s namespace='frontend/ticket/detail' name='TicketAttachment'}Attachment{/s}</div>
                                        <div class="panel--th column--date">{s namespace='frontend/ticket/detail' name='TicketAttachmentDate'}Attachment date{/s}</div>
                                        <div class="panel--th column--actions is--align-center">{s namespace='frontend/ticket/detail' name='TicketAttachmentAction'}Action{/s}</div>
                                    </div>
                                {/block}
                                {foreach from=$userAttachments item=userAttachment}
                                    {block name="ticketsystem_attachment_item"}
                                        <div class="attachment--item panel--tr">
                                            <div class="attachment--date panel--td column--filename">
                                                <div class="column--label">
                                                    {s namespace='frontend/ticket/detail' name='TicketAttachment'}Attachment{/s}
                                                </div>
                                                <div class="column--value">
                                                    {$userAttachment.filename}
                                                </div>
                                            </div>
                                            <div class="attachment--date panel--td column--date">
                                                <div class="column--label">
                                                    {s namespace='frontend/ticket/detail' name='TicketAttachmentDate'}Attachment date{/s}
                                                </div>
                                                <div class="column--value">
                                                    {$userAttachment.date|date_format:"%d.%m.%Y %H:%M:%S"}
                                                </div>
                                            </div>
                                            <div class="attachment--actions panel--td column--actions">
                                                <a href="{url controller=ticket action=download attachment=$userAttachment.hash}" title="" class="btn is--small">{s namespace='frontend/ticket/detail' name="TicketDetailAttachmentDownload"}Download{/s}</a>
                                                <a href="{url controller=ticket action=delete attachment=$userAttachment.hash}" title="" class="btn is--small">{s namespace='frontend/ticket/detail' name="TicketDetailAttachmentDelete"}Delete{/s}</a>
                                            </div>
                                        </div>
                                    {/block}
                                {/foreach}
                            </div>
                        {/block}
                    </div>
                {/if}

                {* Ticket info box *}
                {if !$ticketDetails.responsible}
                    {block name="frontend_ticketsystem_alert_ct"}
                        <div class="ticketsystem--alert-ct">
                            {block name='ticketsystem_panel_info_box'}

                                {* Ticket in process *}
                                {block name='ticketsystem_panel_body_ticket_progress'}
                                    {if !$ticketDetails.responsible && !$ticketDetails.closed}
                                        {include file="frontend/_includes/messages.tpl" type="info" content="{s name='TicketDetailInfoStatusProgress'}{/s}"}
                                    {/if}
                                {/block}

                                {* Ticket closed *}
                                {block name='ticketsystem_panel_body_ticket_closed'}
                                    {if $ticketDetails.closed}
                                        {include file="frontend/_includes/messages.tpl" type="success" content="{s name='TicketDetailInfoStatusClose'}{/s}"}
                                    {/if}
                                {/block}
                            {/block}
                        </div>
                    {/block}
                {/if}

                {* Ticket answer *}
                {if $ticketDetails.responsible || $ticketDetails.showAnswer}
                    {block name='frontend_index_content_ticketsystem_answer_panel'}
                        <div class="ticketsystem--answer panel has--border">
                            {block name='frontend_index_content_ticketsystem_answer_panel_headline'}
                                <h2 class="panel--title">{s name='TicketDetailInfoAnswer'}{/s}:</h2>
                            {/block}
                            {block name='frontend_index_content_ticketsystem_answer_panel_body'}
                                <div class="panel--body is--wide">
                                    {block name='frontend_index_content_ticketsystem_answer_panel_body_form'}
                                        <form action="{url controller=ticket action=sendAnswer forceSecure}" method="POST">
                                            {block name='frontend_index_content_ticketsystem_answer_panel_body_form_textarea'}
                                                <textarea class="ticketsystem--detail-textarea" rows="5" cols="5" name="sAnswer" placeholder="{s namespace='frontend/ticket/detail' name='TicketTextareaPlaceholder'}{/s}"></textarea>
                                            {/block}
                                            {block name='frontend_index_content_ticketsystem_answer_panel_body_form_ticket_id'}
                                                <input type="hidden" name="ticketId" value="{$ticketDetails.id}">
                                            {/block}
                                            {block name='frontend_index_content_ticketsystem_answer_panel_body_form_panel_action'}
                                                <div class="panel--actions is--wide ticketsystem--detail-submit-panel">
                                                    {block name='frontend_index_content_ticketsystem_answer_panel_body_form_panel_action_submit'}
                                                        <input class="btn is--primary ticketsystem--detail-submit-button" type="submit" value="{s namespace='frontend/ticket/detail' name='TicketDetailSubmitButton'}{/s}"/>
                                                    {/block}
                                                </div>
                                            {/block}
                                        </form>
                                        {block name='frontend_ticket_answer_upload'}
                                            {if {config name=allowUploads}}
                                                <form name="fileuploadOption" method="post" action="{url controller=ticket action=upload forceSecure}" class="answerUpload">
                                                    <div class="option_values option_values_upload_file">
                                                        <input type="hidden" name="ticket" value="{$ticketDetails.id}"/>
                                                        <input type="hidden" name="answer" value="-1"/>
                                                        <input type="file" id="option" class="fileupload-input" name="fileupload" multiple="multiple"/>
                                                    </div>
                                                </form>
                                            {/if}
                                        {/block}
                                    {/block}
                                </div>
                            {/block}
                        </div>
                    {/block}
                {/if}

                {* ticket history *}
                {block name='ticketsystem_panel_history_box'}
                    {foreach $ticketHistoryDetails as $historyItem}
                        <div class="panel has--border ticketsystem--history-panel">
                            {block name='ticketsystem_panel_history_box_label'}
                                <span class="panel--header">{$historyItem.receipt|date:DATETIME_MEDIUM} | {if $historyItem.direction == "OUT"} {s name='TicketDetailInfoShopAnswer'} {/s} {else} {s name='TicketDetailInfoAnswer'}{/s}{/if}:</span>
                            {/block}

                            {* Your message *}
                            {block name='ticketsystem_panel_history_box_message'}
                                <div class="panel--body">{$historyItem.message}</div>
                                {block name='frontend_ticket_history_attachments'}
                                    {if $historyItem.attachment}
                                        {foreach $historyItem.attachment as $key => $attachment}
                                            <a href="{url controller=ticket action=download attachment=$attachment.hash}" class="btn is--primary is--small ticketsystem--attachment-btn" target="_blank">{s namespace='frontend/ticket/detail' name="TicketDetailAttachment"}Download attachment{/s}</a>
                                        {/foreach}
                                    {/if}
                                {/block}
                            {/block}
                        </div>
                    {/foreach}

                    {* Ticket meta data *}
                    {block name='ticketsystem_panel_history_box_meta_data'}
                        <div class="panel has--border ticketsystem--meta-panel">
                            {block name='ticketsystem_panel_history_box_meta_data_label'}
                                <span class="panel--header">{$ticketDetails.receipt|date:DATETIME_MEDIUM} | {s name='TicketDetailInfoQuestion'}{/s}</span>
                            {/block}

                            {block name='ticketsystem_panel_history_box_meta_data_message'}
                                <div class="panel--body">{$ticketDetails.message}</div>
                            {/block}
                        </div>
                        {block name='meta_data_additional_info_header'}
                            <h2 class="ticketsystem--additional-info-headline">{s namespace='frontend/ticket/detail' name='TicketDetailAdditionalData'}{/s}</h2>
                        {/block}

                        {block name="meta_data_additional_ingo_content"}
                            {foreach $ticketDetails.additional as $additionalData}
                                {if $additionalData.value}
                                    <div class="panel has--border ticketsystem-additional-info">
                                        {if $additionalData.value}
                                            <span class="panel--header">{$additionalData.label}:</span>
                                            <div class="panel--body">
                                                {$additionalData.value}
                                            </div>
                                        {/if}
                                    </div>
                                {/if}
                            {/foreach}
                        {/block}
                    {/block}
                {/block}
            {/if}

            {block name='ticketsystem_back_button'}
                <a href="{url controller='ticket' action='listing'}" class="btn is--primary is--small ticketsystem--detail-back-button">{s name='TicketDetailLinkBack'}{/s}</a>
            {/block}
        </div>
    {/block}
{/block}
