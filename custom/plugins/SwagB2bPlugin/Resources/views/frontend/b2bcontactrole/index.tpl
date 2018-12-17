{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.navigation = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="RoleAssignment"}Role assignment{/s}
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    <div data-id="error-list"></div>

    <div class="panel--tr row--default-selection">
        <div class="panel--th panel--spacer"></div>
        <div class="panel--th panel--icon panel--tree-col-assign"><i class="icon--check" title="{s name="GrantRight"}Grant right{/s}"></i></div>
        <div class="panel--th panel--label-company panel--tree-col-label">{s name="Role"}Role{/s}</div>
    </div>

    <div class="b2b--ajax-panel" data-url="{url action=tree contactId=$contactId}" data-plugins="b2bTree,b2bAssignmentGrid"></div>
{/block}