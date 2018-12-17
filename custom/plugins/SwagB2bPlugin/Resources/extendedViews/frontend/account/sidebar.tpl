{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/account/sidebar.tpl"}

{block name="frontend_account_menu_link_overview"}
    {if $sUserLoggedIn && $b2bSuite}
        {if !$isSalesRep}
            {include file="frontend/account/sidebar_client.tpl"}
        {else}
            {include file="frontend/account/sidebar_salesrep.tpl"}
        {/if}
    {elseif $sUserLoggedIn && !$b2bSuite}
        {$smarty.block.parent}
    {else}
        <li class="navigation--entry entry--signup">
            <span class="navigation--signin">
                <a href="{url module='frontend' controller='account'}"
                   class="blocked--link btn is--primary navigation--signin-btn"
                   title="{s name="AccountLogin"}Login{/s}"
                >
                    {s name="AccountLogin"}Login{/s}
                </a>
                <span class="navigation--register">
                    {s name="AccountOr"}or{/s}
                    <a href="{url module='frontend' controller='account'}"
                       class="blocked--link"
                       title="{s name="AccountRegister"}register{/s}"
                    >
                        {s name="AccountRegister"}register{/s}
                    </a>
                </span>
            </span>
        </li>
    {/if}
{/block}

{* Waiting for Core fix with ID SW-18945 *}
{block name="frontend_account_menu_link_profile"}{if $sUserLoggedIn && !$b2bSuite}{$smarty.block.parent}{/if}{/block}
{block name="frontend_account_menu_link_addresses"}{if $sUserLoggedIn && !$b2bSuite}{$smarty.block.parent}{/if}{/block}
{block name="frontend_account_menu_link_payment"}{if $sUserLoggedIn && !$b2bSuite}{$smarty.block.parent}{/if}{/block}
{block name="frontend_account_menu_link_orders"}{if $sUserLoggedIn && !$b2bSuite}{$smarty.block.parent}{/if}{/block}
{block name="frontend_account_menu_link_downloads"}{if $sUserLoggedIn && !$b2bSuite}{$smarty.block.parent}{/if}{/block}
{block name="frontend_account_menu_link_notes"}{if $sUserLoggedIn && !$b2bSuite}{$smarty.block.parent}{/if}{/block}
{block name="frontend_account_menu_link_partner_statistics"}{if $sUserLoggedIn && !$b2bSuite}{$smarty.block.parent}{/if}{/block}
{block name="frontend_account_menu_link_logout"}{if $sUserLoggedIn}{$smarty.block.parent}{/if}{/block}