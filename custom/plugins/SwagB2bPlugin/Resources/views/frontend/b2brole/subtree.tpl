{namespace name=frontend/plugins/b2b_debtor_plugin}
{foreach $errors as $error}
    <div class="modal--errors error--list">
        {include file="frontend/_includes/messages.tpl" type="error" b2bcontent=$error}
    </div>
{/foreach}
<ul class="is--b2b-tree-select list--unordered is--root-list">
    <li class="{if $rootNode->hasChildren}has--children{/if} {if $rootNode->id == $selectedId}is--selected{/if} {if $rootNode->children}is--opened{else}is--closed{/if}"
            data-id="{$rootNode->id}"
    >
        <span class="node-content-container">
            <span class="b2b-tree-node-inner">
                {if !$rootNode->isForbidden}
                    <a href="{url action=children parentId=$rootNode->id}">
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
                <span class="tree-label is--company">
                    {s name="RootNodeLabel"}Whole Company{/s}
                </span>
            </span>
        </span>
        <ul class="is--b2b-tree-select list--unordered">
            {foreach $rootNode->children as $child}
                {include file="frontend/b2brole/_node.tpl" node=$child}
            {/foreach}
        </ul>
    </li>
</ul>
