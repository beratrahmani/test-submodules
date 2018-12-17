{namespace name=frontend/plugins/b2b_debtor_plugin}

{* Contingent Groups Container *}
<div class="panel is--rounded">
    <div class="panel--title is--underline">
        <div class="block-group b2b--block-panel">
            <div class="block block--title">
                <h3>{s name="ContingentManagement"}Contingent management{/s}</h3>
            </div>
            {if $companyFilter.level}
                <div class="block block--actions">
                    <button type="button" data-target="contingent-group-detail" data-href="{url action=new grantContext=$grantContext}"
                            class="btn component-action-create ajax-panel-link {b2b_acl controller=b2bcontingentgroup action=new}"
                            title="{s name="CreateContingentGroup"}Create contingent group{/s}">
                        {s name="CreateContingentGroup"}Create contingent group{/s}
                    </button>
                </div>
            {/if}
        </div>
    </div>
    <div class="panel--body is--wide">
        {include file="frontend/b2bcontingentgroup/_grid.tpl" gridState=$gridState grantContext=$grantContext}
    </div>
</div>

{* Contingent Group Detail Modal Container *}
<div class="b2b--ajax-panel b2b-modal-panel" data-id="contingent-group-detail"></div>
