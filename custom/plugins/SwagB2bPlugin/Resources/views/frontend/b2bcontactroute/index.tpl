{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.navigation = true}

    {$modalSettings.actions = false}
    {$modalSettings.bottom = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="PermissionManagement"}Permission management{/s}
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    {include file="frontend/_grid/acl-route-assignment-grid.tpl" privilegeGrid=$privilegeGrid actions=$actions propertyName="contactId" propertyValue=$contactId assignController=b2bcontactroute assignAction=assign}
{/block}

{* Modal Content: Grid Component: Bottom *}
{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <div class="bottom--actions">
        <form action="{url action=denyAll}" method="post" class="form--inline">
            <input type="hidden" name="contactId" value="{$contactId}">
            <button type="submit" class="btn is--primary">{s name="DenyAll"}Deny all{/s}</button>
        </form>
        <form action="{url action=allowAll}" method="post" class="form--inline">
            <input type="hidden" name="contactId" value="{$contactId}">
            <button type="submit" class="btn is--primary">{s name="AllowAll"}Allow all{/s}</button>
        </form>
    </div>
{/block}