{namespace name=frontend/plugins/b2b_debtor_plugin}

{block name="b2b_grid"}
    <div class="b2b--grid-container">
       {if $url}
            <form method="get" action="{$url}">
       {else}
            <form method="get" action="{url}">
       {/if}
            {block name="b2b_grid_form"}{/block}

            {block name="b2b_grid_table_actions"}
                <div class="block-group table--actions">

                    {block name="b2b_grid_sort"}
                        <div class="block col-sort">
                            <div class="select-field">
                                <select name="sort-by" class="is--auto-submit">
                                    <option value="" {if !$gridState.sortBy}selected="selected"{/if} disabled>
                                        {s name="SortBy"}Sort by{/s}
                                    </option>
                                    {block name="b2b_grid_col_sort"}{/block}
                                </select>
                            </div>
                        </div>
                    {/block}

                    {block name="b2b_grid_search"}
                        <div class="block col-search">
                            <div class="search--area">
                                <input type="hidden" name="filters[all][field-name]" value="_all_">
                                <input type="hidden" name="filters[all][type]" value="like">
                                {block name="b2b_grid_search_filters"}{/block}

                                <input type="text" name="filters[all][value]"
                                       value="{$gridState.searchTerm}" placeholder="{s name="Search"}Search{/s}...">

                                <button title="{s name="Search"}Search{/s}" type="submit" value="submit" class="button--submit btn is--primary is--center">
                                    <i class="icon--arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    {/block}

                </div>
            {/block}

            {if !$gridState.data|count && $gridState.searchTerm|strlen}
                {block name="b2b_grid_message_no_search_result"}
                    {include file="frontend/_includes/messages.tpl" type="info" content="{s name="EmptySearchList"}There are no search results found for:{/s} \"{$gridState.searchTerm}\""}
                {/block}
            {elseif !$gridState.data|count}
                {block name="b2b_grid_message_grid_empty"}
                    {include file="frontend/_includes/messages.tpl" type="info" content="{s name="EmptyList"}There are no results yet. You can create your first one by clicking the create button.{/s}"}
                {/block}
            {/if}

            {if $gridState.data|count}
                <div class="b2b--component-pagination is--b2b-component-pagination">
                    <div class="block-group">
                        <div class="block box--page-info">
                            {s name="Page"}Page{/s} {$gridState.currentPage} / {$gridState.maxPage}
                        </div>
                        <div class="block box--page-button">
                            <button class="btn is--large btn--next js--action-next" name="buttonNext" value="{$gridState.currentPage + 1}" {if $gridState.currentPage === $gridState.maxPage} disabled {/if}>
                                <i class="icon--arrow-right"></i>
                            </button>
                        </div>
                        <div class="block box--page-select">
                            <div class="select-field">
                                <select name="page" class="is--auto-submit">
                                    {for $i = 1 to $gridState.maxPage}
                                        <option value="{$i}"{if $i == $gridState.currentPage } selected="selected"{/if}>{$i}</option>
                                    {/for}
                                </select>
                            </div>
                        </div>
                        <div class="block box--page-button">
                            <button class="btn is--large btn--previous js--action-previous" name="buttonPrevious" value="{$gridState.currentPage - 1}" {if $gridState.currentPage === 1} disabled {/if}>
                                <i class="icon--arrow-left"></i>
                            </button>
                        </div>
                    </div>
                </div>
            {/if}
        </form>

        {if $gridState.data|count}
        <table class="{block name="b2b_grid_table_class"}table--contacts{/block} component-table b2b--component-grid" data-row-count="{$gridState.data|count}">
            <thead>
            {block name="b2b_grid_table_head"}{/block}
            </thead>
            <tbody>
            {foreach $gridState.data as $row}
                {block name="b2b_grid_table_row"}{/block}
            {/foreach}
            {block name="b2b_grid_table_after_rows"}{/block}
            </tbody>
        </table>
        {/if}

    </div>
{/block}
