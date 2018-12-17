{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_grid/grid.tpl"}

{block name="b2b_grid_message_grid_empty"}
    <div class="b2b--component-pagination is--b2b-component-pagination">
        <div class="block-group">
            <div class="block box--page-info">
                {s name="Page"}Page{/s} 1 / 1
            </div>
            <div class="block box--page-select">
                <div class="select-field">
                    <select name="page">
                        <option value="1" selected="selected">1</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <table class="{block name="b2b_grid_table_class"}table--contacts{/block} component-table b2b--component-grid" data-row-count="0">
        <thead>
            {block name="b2b_grid_table_head"}{/block}
        </thead>
        <tbody>
            {block name="b2b_grid_table_after_rows"}{/block}
        </tbody>
    </table>
{/block}

{block name="b2b_grid_table_class"}table--ordernumber{/block}

{block name="b2b_grid_form"}
    <div class="custom-order-number-messages">
        {foreach $errors as $error}
            <div class="modal--errors error--list">
                {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
            </div>
        {/foreach}
        {if $message}
            <div class="modal--errors error--list">
                {include file="frontend/_includes/messages.tpl" type="success" b2bcontent=$message}
            </div>
        {/if}
    </div>
{/block}

{block name="b2b_grid_col_sort"}
    <option value="ordernumber::asc"{if $gridState.sortBy == 'ordernumber::asc'} selected="selected"{/if}>{s name="ProductnumberAsc"}Productnumber Ascending{/s}</option>
    <option value="ordernumber::desc"{if $gridState.sortBy == 'ordernumber::desc'} selected="selected"{/if}>{s name="ProductnumberDesc"}Productnumber Descending{/s}</option>
    <option value="custom_ordernumber::asc"{if $gridState.sortBy == 'custom_ordernumber::asc'} selected="selected"{/if}>{s name="CustomProductOrderNumberAsc"}Custom Productnumber Ascending{/s}</option>
    <option value="custom_ordernumber::desc"{if $gridState.sortBy == 'custom_ordernumber::desc'} selected="selected"{/if}>{s name="CustomProductOrderNumberDesc"}Custom Productnumber Descending{/s}</option>
{/block}

{block name="b2b_grid_table_head"}
    <tr>
        <th class="col-name">{s name="Product"}Product{/s}</th>
        <th class="col-ordernumber">{s name="ProductOrderNumber"}Productnumber{/s}</th>
        <th class="col-custom-ordernumber">{s name="CustomProductOrderNumber"}Custom productnumber{/s}</th>
        <th class="col-actions">{s name="Actions"}Actions{/s}</th>
    </tr>
{/block}

{block name="b2b_grid_table_row"}
    {include file="frontend/b2bordernumber/gridrow.tpl" row=$row}
{/block}

{block name="b2b_grid_table_after_rows"}
    {include file="frontend/b2bordernumber/gridrow.tpl" row=null}
{/block}
