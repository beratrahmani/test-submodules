{extends file='parent:frontend/note/index.tpl'}

{block name='frontend_note_index_overview'}
    {block name='frontend_note_index_overview_quick_view'}
        {if $additionalQuickViewMode === 2 && $sNotes}
            {block name='frontend_note_index_overview_quick_view_all_products'}
                <div class="quick-view--note-wrapper"
                     data-quickview="true"
                     data-ajaxUrl="{url module='Widgets' controller='SwagEmotionAdvanced' action='index'}"
                     data-productLinkSelector=".note--item a">
                    {$smarty.block.parent}
                </div>
            {/block}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}
