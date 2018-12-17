{extends file='parent:frontend/index/shop-navigation.tpl'}

{block name="frontend_index_checkout_actions_include"}
    {if $b2bSuite}
        <li class="navigation--entry entry--fastorder {b2b_acl controller=b2bfastorder action=index}" role="menuitem">
            <a href="{url controller='b2bfastorder'}" title="{"{s name="FastOrder" namespace="frontend/plugins/b2b_debtor_plugin"}Fast order{/s}"|escape}" class="btn">
                <i class="icon--upload"></i>
            </a>
        </li>
    {/if}
    {$smarty.block.parent}
{/block}