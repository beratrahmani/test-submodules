{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.navigation = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="RoleVisibility"}Role visibility{/s}
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}

    <div class="panel--tr row--role-role-selection">
        <div class="panel--th panel--icon panel--tree-col-tree"></div>
        <div class="panel--th panel--icon panel--tree-col-checkbox"><i class="icon--check" title="{s name="GrantRight"}Grant right{/s}"></i></div>
        <div class="panel--th panel--icon panel--tree-col-checkbox"><i class="icon--forward" title="{s name="InheritRight"}Inherit right{/s}"></i></div>
        <div class="panel--th panel--label-company panel--tree-col-label">{s name="Role"}Role{/s}</div>
    </div>


    <div class="b2b--ajax-panel" data-url="{url action=tree baseRoleId=$baseRoleId}" data-plugins="b2bTree,b2bAssignmentGrid"></div>
{/block}
