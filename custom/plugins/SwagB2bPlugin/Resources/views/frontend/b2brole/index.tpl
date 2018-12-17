{namespace name=frontend/plugins/b2b_debtor_plugin}

{* B2b Account Main Content *}
{block name="frontend_index_content_b2b"}
    <div class="panel is--rounded">
        <div class="panel--title is--underline">

            <div class="block-group b2b--block-panel">
                <div class="block block--title">
                    <h3>{s name="RoleManagement"}Role management{/s}</h3>
                </div>
                <div class="block block--actions">
                    <form
                        action="{url action=new}"
                        method="get"
                        class="ajax-panel-link"
                        data-target="role-detail"
                        data-href="{url action=new}"
                    >
                        <input
                            class="b2b--tree-selection-aware"
                            data-id="role-tree"
                            type="hidden"
                            name="parentId"
                            value="{$root->id}"
                        >
                        <button
                            type="submit"
                            class="btn component-action-create {b2b_acl controller=b2brole action=new}"
                        >
                            {s name="CreateRole"}Create role{/s}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="panel--body is--wide">
            <div class="is--b2b-tree-select is--b2b-tree-select-container" data-move-url="{url action=move}">
                <div class="b2b--ajax-panel"
                    data-url="{url controller=b2brole action=subtree}?openNodes[]={$root->id}"
                    data-id="role-tree">
                </div>
            </div>
        </div>

        <div class="is--b2b-ajax-panel b2b--ajax-panel b2b-modal-panel" data-id="role-detail"></div>
    </div>
{/block}