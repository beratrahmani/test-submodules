{namespace name=frontend/plugins/b2b_debtor_plugin}

{block name="b2b_modal_base"}

{* Enables Sidebar navigation inside the modal box *}
{if !isset($modalSettings.navigation)}
    {$modalSettings.navigation = true}
{/if}

<div class="b2b--modal">

    {block name="b2b_modal_base_navigation"}
        {if $modalSettings.navigation}
            <div class="block-navigation modal--tabs">
                <ul>
                    <li class="tab--header">
                        {block name="b2b_modal_base_navigation_header"}
                            {* Title *}
                        {/block}
                    </li>
                    {block name="b2b_modal_base_navigation_entries"}
                        {* Navigation Items *}
                    {/block}
                </ul>
            </div>
        {/if}
    {/block}

    {block name="b2b_modal_base_content"}
    <div class="block-content{if $modalSettings.navigation} has--navigation{/if}">

        <div title="{s name="Loading"}Loading{/s}" class="content--loading is--hidden">
            <i class="icon--loading-indicator"></i>
        </div>

        {block name="b2b_modal_base_content_inner"}
            {* Ajax Panel *}
        {/block}

    </div>
    {/block}
</div>
{/block}