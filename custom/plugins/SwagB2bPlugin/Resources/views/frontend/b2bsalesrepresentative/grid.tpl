{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="panel has--border is--rounded">
    <div class="panel--title is--underline">

        <div class="block-group b2b--block-panel">
            <div class="block block--title">
                <h3>{s name="ClientOverview"}Client Overview{/s}</h3>
            </div>
        </div>
    </div>

    <div class="panel--body is--wide">
        {include file="frontend/_grid/salesrepresentative-grid.tpl" gridState=$salesRepresentativeGrid}
    </div>
</div>