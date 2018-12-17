{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.navigation = true}

    {$modalSettings.actions = false}
    {$modalSettings.content.padding = false}
    {$modalSettings.bottom = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="History"}History{/s}
{/block}

{block name="b2b_modal_base_content_inner_scrollable_inner_actions_inner"}
    {$smarty.block.parent}
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    <div class="status-grid">
        <ul>
            {foreach $gridState.data as $item}
                {include file="frontend/b2border/logTypes/{$item->logValue->getTemplateName()}.tpl"}
            {foreachelse}
                {include file="frontend/_includes/messages.tpl" type="info" content="{s name="HistoryOfferEmptyInfo"}This offer has not any history{/s}"}
            {/foreach}
        </ul>
    </div>
{/block}

{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}

    {if $gridState.data|count}
        <form method="get" action="{url}">
            <input type="hidden" value="{$orderContextId}" name="orderContextId">
            <div class="is--b2b-component-pagination">
                <div class="bottom--page">
                    {s name="Page"}Page{/s} {$gridState.currentPage} / {$gridState.maxPage}
                </div>

                <div class="bottom--pagination--buttons">
                    <button class="btn is--large btn--next js--action-next" name="buttonNext" value="{$gridState.currentPage + 1}" {if $gridState.currentPage === $gridState.maxPage} disabled {/if}>
                        <i class="icon--arrow-right"></i>
                    </button>
                </div>

                <div class="bottom--pagination">

                    <div class="select-field">
                        <select name="page" class="is--auto-submit">
                            {for $i = 1 to $gridState.maxPage}
                                <option value="{$i}"{if $i == $gridState.currentPage } selected="selected"{/if}>{$i}</option>
                            {/for}
                        </select>
                    </div>
                </div>

                <div class="bottom--pagination--buttons">
                    <button class="btn is--large btn--previous js--action-previous" name="buttonPrevious" value="{$gridState.currentPage - 1}" {if $gridState.currentPage === 1} disabled {/if}>
                        <i class="icon--arrow-left"></i>
                    </button>
                </div>
            </div>
        </form>
    {/if}
{/block}