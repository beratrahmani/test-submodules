{extends file='parent:widgets/checkout/info.tpl'}

{* Notepad entry *}
{block name="frontend_index_checkout_actions_notepad"}
    {if $b2bSuite && {b2b_acl_check controller=b2borderlist action=index}}
        <li class="navigation--entry entry--notepad" role="menuitem">
            <a title="{s name="OrderLists" namespace="frontend/plugins/b2b_debtor_plugin"}Order lists{/s}" href="{url controller=b2borderlist}" class="btn">
                <i class="icon--heart"></i>
            </a>
        </li>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}