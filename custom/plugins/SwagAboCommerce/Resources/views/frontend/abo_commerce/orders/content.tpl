{namespace name='frontend/abo_commerce/orders'}

{* Orders overview *}
<div class="content block account--content">
    {if isset($changeSuccess)}
        {block name="frontend_account_abonnements_overview_message"}
            {if $changeSuccess === true}
                {include file="frontend/_includes/messages.tpl" type="success" content="{s name="SubscriptionChangeSuccess" namespace="frontend/abo_commerce/index"}{/s}"}
            {else}
                {include file="frontend/_includes/messages.tpl" type="error" content="{s name="SubscriptionChangeError" namespace="frontend/abo_commerce/index"}{/s}"}
            {/if}
        {/block}
    {/if}

    {* Welcome text *}
    {block name="frontend_account_orders_welcome"}
        <div class="account--welcome panel">
            {block name="frontend_account_orders_welcome_headline"}
                <h1 class="panel--title">{s name='AboCommerceOrdersHeadline'}{/s}</h1>
            {/block}
        </div>
    {/block}

    {if isset($sAboTerminationSuccess)}
        <div class="abo--termination">
            {if $sAboTerminationSuccess}
                {block name="frontend_account_abonnement_termination_success"}
                    {include file='frontend/_includes/messages.tpl' type='success' content="{s name='AboCommerceTerminationSuccess'}{/s}"}
                {/block}
                {if $sAboTerminationMailSent eq false}
                    {block name="frontend_account_abonnement_termination_mail_error"}
                        {include file='frontend/_includes/messages.tpl' type='error' content="{s name='AboCommerceTerminationMailError'}{/s}"}
                    {/block}
                {/if}
            {else}
                {block name="frontend_account_abonnement_termination_error"}
                    {include file='frontend/_includes/messages.tpl' type='error' content="{s name='AboCommerceTerminationError'}{/s}"}
                {/block}
            {/if}
        </div>
    {/if}

    {if !$orders}
        {block name="frontend_account_orders_info_empty"}
            <div class="account--no-orders-info">{s name="AboCommerceOrdersNoSubscrption"}{/s}</div>
        {/block}
    {else}
        {* Orders overview *}
        {block name="frontend_account_abonnements_overview"}
            <div class="account--orders-overview panel">

                {block name="frontend_account_abonnements_table"}
                    <div class="panel--table">
                        {block name="frontend_account_abonnements_table_head"}
                            <div class="orders--table-header panel--tr">

                                {block name="frontend_account_abonnements_table_head_date"}
                                    <div class="panel--th column--date">{s name="AboCommerceOrdersCreated"}{/s}</div>
                                {/block}

                                {block name="frontend_account_abonnements_table_head_id"}
                                    <div class="panel--th column--id">{s name="AboCommerceOrdersOrdernumber"}{/s}</div>
                                {/block}

                                {block name="frontend_account_abonnements_table_head_delivery_interval"}
                                    <div class="panel--th column--interval">{s name="AboCommerceOrdersDeliveryInterval"}{/s}</div>
                                {/block}

                                {block name="frontend_account_abonnements_table_head_expiry"}
                                    <div class="panel--th column--expiry">{s name="AboCommerceOrdersExpiry"}{/s}</div>
                                {/block}

                                {block name="frontend_account_abonnements_table_head_actions"}
                                    <div class="panel--th column--actions is--align-center">{s name="AboCommerceOrdersActions"}{/s}</div>
                                {/block}
                            </div>
                        {/block}

                        {block name="frontend_account_abonnement_item_overview"}
                            {foreach $orders as $order}
                                <div class="order--item panel--tr">
                                    {include file="frontend/abo_commerce/abonnement.tpl"}
                                </div>
                                {* Abonnement details *}
                                {block name="frontend_account_abonnement_item_detail"}
                                    {include file="frontend/abo_commerce/abonnement_details.tpl"}
                                {/block}
                            {/foreach}
                        {/block}
                    </div>
                {/block}
            </div>
        {/block}
    {/if}
</div>
