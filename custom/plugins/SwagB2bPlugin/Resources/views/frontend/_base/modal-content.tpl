{namespace name=frontend/plugins/b2b_debtor_plugin}

{block name="b2b_modal_base_settings"}
    {* Enables actions topbar inside the content area of a grid modal component *}
    {$modalSettings.actions = false}

    {* Enables content padding inside the inner content area of a grid modal component *}
    {$modalSettings.content.padding = true}

    {* Enables bottom actions inside the content area of a grid modal component *}
    {$modalSettings.bottom = false}
{/block}

{block name="b2b_modal_base_content_inner_topbar"}
    <div class="topbar">
        <h3 class="panel--title">
            {block name="b2b_modal_base_content_inner_topbar_headline"}{/block}
        </h3>
    </div>
{/block}

{block name="b2b_modal_base_content_inner_scrollable"}
    <div class="scrollable with--grid">
        {if $modalSettings.actions}
            {block name="b2b_modal_base_content_inner_scrollable_inner_actions"}
                <div class="inner--actions">
                    {block name="b2b_modal_base_content_inner_scrollable_inner_actions_inner"}{/block}
                </div>
            {/block}
        {/if}

        {block name="b2b_modal_base_content_inner_scrollable_inner_content"}
            <div class="inner--content{if !$modalSettings.actions} without--actions{/if}{if !$modalSettings.bottom} without--bottom{/if}{if $modalSettings.content.padding} with--padding{/if}">
                {block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}{/block}
            </div>
        {/block}

        {if $modalSettings.bottom}
            {block name="b2b_modal_base_content_inner_scrollable_inner_bottom"}
                <div class="inner--bottom">
                    {block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}{/block}
                </div>
            {/block}
        {/if}
    </div>
{/block}
