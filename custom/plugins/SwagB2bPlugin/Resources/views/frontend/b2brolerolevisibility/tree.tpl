<ul class="is--b2b-tree list--unordered">
    {foreach $roles as $role}
        <li class="{if $role->hasChildren}has--children{/if} is--closed">
            <span class="node-content-container">
                <span class="b2b-tree-node-inner">

                    {if $role->hasChildren}<a href="{url parentId=$role->id baseRoleId=$baseRoleId}">{/if}
                        <i class="tree-icon closed icon--arrow-right5"></i>
                        <i class="tree-icon opened icon--arrow-down5"></i>
                        <i class="tree-icon leaf icon--dot"></i>
                        <i class="tree-icon loading icon--loading-indicator"></i>
                        {if $role->hasChildren}</a>{/if}

                    <span class="tree-label">
                        <table class="table-unstyled">
                            <tr>
                                <td class="col-checkbox">
                                    <form action="{url action=assign}" method="post" class="b2b--assignment-form {b2b_acl controller=b2brolerolevisibility action=assign}">
                                        <input type="hidden" name="baseRoleId" value="{$baseRoleId}"/>
                                        <input type="hidden" name="roleId" value="{$role->id}"/>
                                        <span class="checkbox without--margin">
                                            <input type="checkbox"
                                                   name="allow"
                                                   value="1"
                                                   {if $role->foreignAllowed}checked="checked"{/if}
                                                    {if !$role->ownerGrantable}disabled{/if}
                                                   class="assign--allow is--auto-submit"
                                            />
                                            <span class="checkbox--state without--margin"></span>
                                        </span>

                                        <span class="checkbox without--margin">
                                            <input type="checkbox"
                                                   name="grantable"
                                                   value="1"
                                                   {if $role->foreignGrantable}checked="checked"{/if}
                                                    {if !$role->ownerGrantable}disabled{/if}
                                                   class="assign--grantable is--auto-submit"
                                            />
                                            <span class="checkbox--state without--margin"></span>
                                        </span>
                                    </form>
                                </td>
                                <td>
                                    {if $role->hasChildren}
                                        <a href="{url parentId=$role->id baseRoleId=$baseRoleId}" title="{s name="Open"}Open{/s}">
                                    {/if}

                                        {$role->name}

                                        {if $role->hasChildren}
                                        </a>
                                    {/if}
                                </td>
                            </tr>
                        </table>
                    </span>
                </span>
            </span>
        </li>
    {/foreach}
</ul>