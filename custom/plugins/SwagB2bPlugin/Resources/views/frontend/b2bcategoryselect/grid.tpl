<ul class="is--b2b-tree list--unordered">
    {foreach $nodes as $node}
        <li class="{if $node->hasChildren}has--children{/if} {if $node->id == $selectedId}is--selected{/if} is--closed" data-id="{$node->id}">
            <span class="node-content-container">
                <span class="b2b-tree-node-inner">
                    {if $node->hasChildren}<a href="{url parentId=$node->id selectedId=$selectedId}">{/if}
                        <i class="tree-icon closed icon--arrow-right5"></i>
                        <i class="tree-icon opened icon--arrow-down5"></i>
                        <i class="tree-icon leaf icon--dot"></i>
                    {if $node->hasChildren}</a>{/if}
                    <input type="radio" name="selectedCategoryId" value="{$node->id}" {if $node->id == $selectedId}checked{/if}>
                    {if $node->hasChildren}
                        <a href="{url parentId=$node->id selectedId=$selectedId}" title="{s name="Open"}Open{/s}">
                            <span class="tree-label">
                                {$node->name}
                            </span>
                        </a>
                    {else}
                    <span class="tree-label">
                        {$node->name}
                    </span>
                    {/if}
                </span>
            </span>
        </li>
    {/foreach}
</ul>