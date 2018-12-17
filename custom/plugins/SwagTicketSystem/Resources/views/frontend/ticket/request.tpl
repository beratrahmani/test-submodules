{extends file='parent:frontend/forms/index.tpl'}

{block name="frontend_index_body_classes"}{$smarty.block.parent} is--ticket-system{/block}
{* Breadcrumb *}
{block name='frontend_index_start'}
    {assign var='sBreadcrumb' value=[['name'=>"{s namespace="frontend/account/index" name='AccountTitle'}{/s}", 'link' => {url controller='account' action='index'}]]}
    {$sBreadcrumb[] = ['name' => "{s namespace="frontend/account/sidebar" name="TicketsystemAccountLinkRequest"}{/s}", 'link' => {url controller='ticket' action='request'}]}
{/block}

{block name='frontend_index_content'}
    {block name="frontend_index_swag_ticket_system_content"}
        {if $email}
            <div class="ticketsystem--form-content">
                <input type="hidden" name="username" value="{$userName}"/>
                <input type="hidden" name="firstname" value="{$firstName}"/>
                <input type="hidden" name="lastname" value="{$lastName}"/>
                <input type="hidden" name="email" value="{$email}"/>
                {$smarty.block.parent}
            </div>
        {/if}
    {/block}
{/block}

{block name='frontend_forms_elements_form_content'}
    {block name="frontend_forms_swag_ticket_system_element_content"}
        <input type="hidden" name="ticketRequest" value="1"/>
    {/block}
    {$smarty.block.parent}
{/block}

{* Forms headline *}
{block name='frontend_forms_index_headline'}
    <div class="account--welcome">
        {if $sSupport.sElements}
            <h1 class="panel--title">{$sSupport.name}</h1>
            <div class="forms--text">{eval var=$sSupport.text}</div>
        {elseif $sSupport.text2}
            {include file="frontend/_includes/messages.tpl" type="success" content=$sSupport.text2}
        {/if}
    </div>
{/block}
