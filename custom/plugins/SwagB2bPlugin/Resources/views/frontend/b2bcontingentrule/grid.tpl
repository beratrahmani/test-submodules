{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.navigation = true}

    {$modalSettings.actions = true}
    {$modalSettings.content.padding = false}
    {$modalSettings.bottom = false}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="ContingentRules"}Contingent rules{/s}
{/block}

{* Modal Content: Grid Component: Actions *}
{block name="b2b_modal_base_content_inner_scrollable_inner_actions_inner"}
    <button data-target="contingent-tab-content" data-href="{url action=new contingentGroupId=$additionalFormValues['id']}" class="btn component-action-create ajax-panel-link {b2b_acl controller=b2bcontingentrule action=new}">
        {s name="CreateContingentRule"}Create contingent rule{/s}
    </button>
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    {if $gridState.data|count}
    <table class="table--contingents b2b--component-grid" data-row-count="{$gridState.data|count}">
        <thead>
            <tr>
                <th>{s name="ContingentRule"}Contingent rule{/s}</th>
                <th width="10%">{s name="Actions"}Actions{/s}</th>
            </tr>
        </thead>
        <tbody>
        {foreach $gridState.data as $row}
            <tr class="ajax-panel-link {b2b_acl controller=b2bcontingentrule action=detail}" data-target="contingent-tab-content" data-row-id="{$row->id}" data-href="{url controller=b2bcontingentrule action=detail id=$row->id}">
                {include file="frontend/_grid/contingentrule-grid-{$row->templateTypeName}.tpl" row=$row}

                <td class="col-actions">
                    <form action="{url controller=b2bcontingentrule action=remove}" method="post">
                        <input type="hidden" name="id" value="{$row->id}">
                        <input type="hidden" name="contingentGroupId" value="{$row->contingentGroupId}">

                        <button title="{s name="DeleteContingentRule"}Delete contingent rule{/s}"
                                type="submit"
                                class="btn is--small component-action-delete {b2b_acl controller=b2bcontingentrule action=remove}"
                                data-confirm="true"
                                data-confirm-url="{url controller="b2bconfirm" action="remove"}">
                            <i class="icon--trash"></i>

                        </button>
                    </form>
                </td>
            </tr>
            {/foreach}
        </tbody>
        </table>
    {/if}
{/block}