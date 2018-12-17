<div class="advisor-grid--ct">
    {for $rowIndex=0 to $question['numberOfRows'] - 1}
        {block name="frontend_advisor_content_wizard_grid_column_config"}
            {* We need a logical count (starting at 0) and a real count (starting at 1) *}
            {$numberColumns = $question['numberOfColumns']}
            {$indexNumberColumns = $numberColumns - 1}
        {/block}

        {* A single row of the grid *}
        {block name="frontend_advisor_content_wizard_question_grid_row"}
            <div class="advisor-grid--row{$numberColumns}">
                {for $colIndex=0 to $indexNumberColumns}
                    {* A single column/cell of the grid *}
                    {block name="frontend_advisor_content_wizard_question_grid_cell"}
                        {include file="frontend/advisor/wizard/grid/cell.tpl"}
                    {/block}
                {/for}
            </div>
        {/block}
    {/for}
</div>