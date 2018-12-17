{namespace name=frontend/plugins/b2b_debtor_plugin}

{math equation="100 / division" division=$actions|count + 3 assign="componentPercentage"}

<div class="b2b--grid-container b2b--assignment-grid">
    <div class="panel--tr">
        <div class="panel--th"
             style="width: {$componentPercentage}%;">{s name="All"}All{/s}
        </div>
        <div class="panel--th"
             style="width: {$componentPercentage * 2}%;">{s name="Component"}Component{/s}
        </div>
        {foreach $actions as $action}
            <div class="panel--th border--right" style="width: {$componentPercentage}%; text-align: center;">{$action|snippet:$action:"frontend/plugins/b2b_debtor_plugin"}</div>
        {/foreach}
    </div>
    <div class="panel--tr">
        <div class="panel--th" style="width: {$componentPercentage / 2}%; text-align: center;" title="{s name="IsAllowedToDo"}Is allowed to do{/s}"><i class="icon--check"></i></div>
        <div class="panel--th border--right" style="width: {$componentPercentage / 2}%;text-align: center;" title="{s name="CanGrantToOther"}Can grant to other{/s}"><i class="icon--forward"></i></div>
        <div class="panel--th" style="width: {$componentPercentage * 2}%;"></div>
        {foreach $actions as $action}
            <div class="panel--th" style="width: {$componentPercentage / 2}%; text-align: center;" title="{s name="IsAllowedToDo"}Is allowed to do{/s}"><i class="icon--check"></i></div>
            <div class="panel--th border--right" style="width: {$componentPercentage / 2}%;text-align: center;" title="{s name="CanGrantToOther"}Can grant to other{/s}"><i class="icon--forward"></i></div>
        {/foreach}
    </div>

    {foreach $privilegeGrid as $component => $route}
        <div class="panel--tr">
            {$prefixedComponentName="_acl_"|cat:$component}
            <form action="{url controller=$assignController action=assignComponent}" method="post" class="b2b--assignment-form b2b--assignment-row-form {b2b_acl controller=$assignController action=$assignAction}">
                <div class="panel--td" style="width: {$componentPercentage / 2}%;">
                    <input type="hidden" name="{$propertyName}" value="{$propertyValue}"/>
                    <input type="hidden" name="component" value="{$component}"/>

                    <span class="checkbox">
                        <input
                                name="allow"
                                type="checkbox"
                                class="assign--allow is--auto-submit"
                                title="{s name="IsAllowedToDo"}Is allowed to do{/s}"

                        />
                        <span class="checkbox--state"></span>
                    </span>
                </div>
                <div class="panel--td" style="width: {$componentPercentage  / 2}%;">
                    <span class="checkbox">
                        <input
                                name="grantable"
                                type="checkbox"
                                class="assign--grantable is--auto-submit"
                                title="{s name="CanGrantToOther"}Can grant to other{/s}"
                        />
                        <span class="checkbox--state"></span>
                    </span>
                </div>
            </form>
            <div class="panel--td" style="width: {$componentPercentage * 2}%;">{$prefixedComponentName|snippet:$prefixedComponentName:"frontend/plugins/b2b_debtor_plugin"}</div>
            {foreach $actions as $action}
                {if array_key_exists($action, $route)}
                    <form action="{url controller=$assignController action=$assignAction}" method="post" class="b2b--assignment-form {b2b_acl controller=$assignController action=$assignAction}">
                        <div class="panel--td" style="width: {$componentPercentage / 2}%;text-align: center;">
                            <input type="hidden" name="{$propertyName}" value="{$propertyValue}"/>
                            <input type="hidden" name="routeId" value="{$route.$action->id}"/>
                            <span class="checkbox">
                                <input type="checkbox"
                                       name="allow"
                                       value="1"
                                       id="allow_{$route.$action->id}"
                                       {if $route.$action->foreignAllowed}checked="checked"{/if}
                                        {if !$route.$action->ownerGrantable}disabled{/if}
                                       class="assign--allow is--auto-submit"
                                       title="{s name="IsAllowedToDo"}Is allowed to do{/s}"/>
                                <span class="checkbox--state"></span>
                            </span>
                        </div>
                        <div class="panel--td border--right" style="width: {$componentPercentage / 2}%;text-align: center;">
                            <span class="checkbox">
                                <input type="checkbox"
                                       name="grantable"
                                       value="1"
                                       {if $route.$action->foreignGrantable}checked="checked"{/if}
                                        {if !$route.$action->ownerGrantable}disabled{/if}
                                       class="assign--grantable is--auto-submit"
                                       title="{s name="CanGrantToOther"}Can grant to other{/s}"/>
                                <span class="checkbox--state"></span>
                            </span>
                        </div>
                    </form>
                {else}
                    <div class="panel--td border--right" style="width: {$componentPercentage}%; height: 100%;">
                        &nbsp;
                    </div>
                {/if}
            {/foreach}
        </div>
    {/foreach}
</div>