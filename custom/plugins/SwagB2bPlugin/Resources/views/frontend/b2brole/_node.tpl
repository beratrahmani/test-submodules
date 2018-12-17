{namespace name=frontend/plugins/b2b_debtor_plugin}
<li {if !$node->isForbidden}draggable="true"{/if}
    class="{if $node->hasChildren}has--children{/if} {if $node->id == $selectedId}is--selected{/if} {if $node->children}is--opened{else}is--closed{/if} {if $node->isForbidden}is--disabled{/if}"
    {if !$node->isForbidden}data-id="{$node->id}"{/if}
>
    <span class="node-content-container">
        <span class="b2b-tree-node-inner">
            {if !$node->isForbidden}
                <a href="{url action=children parentId=$node->id}">
                    <span class="tree-handle">
                        <i class="tree-icon closed icon--arrow-right5"></i>
                        <i class="tree-icon opened icon--arrow-down5"></i>
                        <i class="tree-icon leaf icon--dot"></i>
                        <i class="tree-icon loading icon--loading-indicator"></i>
                    </span>
                </a>
            {else}
                <span class="tree-handle">
                    <i class="tree-icon closed icon--arrow-right5"></i>
                    <i class="tree-icon opened icon--arrow-down5"></i>
                    <i class="tree-icon leaf icon--dot"></i>
                    <i class="tree-icon loading icon--loading-indicator"></i>
                </span>
            {/if}
            <span class="tree-label">
                {$node->name}
                {if !$node->isForbidden}
                    <span class="actions">
                        <form action="{url action=detail}" method="get" class="form--inline ajax-panel-link {b2b_acl controller=b2brole action=detail}" data-target="role-detail">
                            <input type="hidden" name="id" value="{$node->id}">
                            <button title="{s name="EditRole"}Edit a role{/s}" type="submit"
                                    class="btn is--small component-action-detail"
                                    data-confirm="true">
                                <i class="icon--pencil"></i>
                            </button>
                        </form>
                        <form action="{url action=remove}" method="post" class="form--inline">
                            <input type="hidden" class="roleId" name="id" value="{$node->id}">
                            <input type="hidden" name="confirmName" value="{$node->name}">
                            <button title="{s name="DeleteRole"}Delete a role{/s}" type="submit"
                                    class="btn is--small component-action-delete {b2b_acl controller=b2brole action=remove}"
                                    data-confirm="true"
                                    data-confirm-url="{url controller="b2bconfirm" action="remove"}">
                                <i class="icon--trash"></i>

                            </button>
                        </form>
                    </span>
                {/if}
            </span>

            {if !$node->isForbidden}
                <span class="drop-area">
                    {if !$node->isAllowedRoot}
                        <span class="drop-before"></span>
                    {/if}
                    <span class="drop-as-child"></span>
                    {if !$node->isAllowedRoot}
                        <span class="drop-after"></span>
                    {/if}
                </span>
            {/if}
        </span>
    </span>
    <ul class="is--b2b-tree-select list--unordered">
        {foreach $node->children as $child}
            {include file="frontend/b2brole/_node.tpl" node=$child}
        {/foreach}
    </ul>
</li>
