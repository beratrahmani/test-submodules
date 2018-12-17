{namespace name=frontend/plugins/b2b_debtor_plugin}

<div class="panel is--rounded">
    <div class="panel--title is--underline">

        <div class="block-group b2b--block-panel">
            <div class="block block--title">
                <h3>{s name="ContactManagement"}Contact Management{/s}</h3>
            </div>
            <div class="block block--actions">
                {if $companyFilter.level}
                    <button type="button" data-target="contact-detail" data-href="{url action=new grantContext=$grantContext}"
                            class="btn component-action-create ajax-panel-link {b2b_acl controller=b2bcontact action=new}"
                            title="{s name="CreateContact"}Create Contact{/s}">
                        {s name="CreateContact"}Create Contact{/s}
                    </button>
                {/if}

            </div>
        </div>
    </div>
    <div class="panel--body is--wide">
        {include file="frontend/b2bcontact/_grid.tpl" gridState=$gridState}
    </div>
</div>

{* Contact Detail Modal *}
<div class="is--b2b-ajax-panel b2b--ajax-panel b2b-modal-panel" data-id="contact-detail" data-plugins="b2bContactPasswordActivation"></div>
