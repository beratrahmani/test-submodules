{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="frontend/_base/index.tpl"}

{* B2b Account Main Content *}
{block name="frontend_index_content_b2b"}
    <div class="b2b--plugin content--wrapper">
        {* Chart graph *}
        <canvas id="b2b-canvas" class="b2b-statistics-chart" width="100%" height="40"></canvas>

        {* Filter & grid table *}
        <div class="b2b--ajax-panel" data-id="statistic-grid" data-url="{url action=grid}" data-plugins="b2bAjaxPanelChart,b2bGridComponent" data-chart-url="{url action=chartData}"></div>
        <div class="is--b2b-ajax-panel b2b--ajax-panel b2b-modal-panel" data-id="order-detail"></div>
    </div>
{/block}