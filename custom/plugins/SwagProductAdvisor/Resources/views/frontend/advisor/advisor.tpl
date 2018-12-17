{block name="frontend_advisor_content"}
    <div class="advisor--content">
        {$isSideBarMode = $advisor['mode'] == 'sidebar_mode'}

        {* Intended to be double included *}
        {if $isSideBarMode}
            {block name="frontend_advisor_content_sidebar_upper"}
                {include file="frontend/advisor/sidebar.tpl" position='upper'}
            {/block}
        {/if}

        {* The main advisor content *}
        {block name="frontend_advisor_content_main"}
            <div class="advisor--content-inner advisor--content-padding
                {if $isSideBarMode} advisor--sidebar-content{/if}
                {if !$isSideBarMode && $advisorState === 'listing'} advisor--wizard-listing-content{/if}
                {if $advisorErrors} advisor--error-content{/if}">
                {if !$advisorErrors}
                    {block name="frontend_advisor_content_main_default"}
                        {$template = "frontend/advisor/{$advisorState}.tpl"}
                        {if $template|template_exists}
                            {include file=$template}
                        {/if}
                    {/block}
                {else}
                    {block name="frontend_advisor_content_main_error"}
                        {include file="frontend/advisor/error.tpl"}
                    {/block}
                {/if}
            </div>
        {/block}

        {* Intended to be double included *}
        {if $isSideBarMode && $advisorState == 'start'}
            {block name="frontend_advisor_content_sidebar_lower"}
                {include file="frontend/advisor/sidebar.tpl" position='lower'}
            {/block}
        {/if}
    </div>
{/block}
