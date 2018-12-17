{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.navigation = true}

    {$modalSettings.actions = false}
    {$modalSettings.content.padding = true}
    {$modalSettings.bottom = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="AddNewComment"}Add new comment{/s}
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    <div class="offer--comment-area">
        <textarea form="newComment" name="comment"></textarea>
    </div>
{/block}

{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <div class="bottom--actions bottom-button right">
        <form id="newComment" method="post" action="{url action=comment}">
            <div class="bottom--comment">
                <input type="hidden" value="{$orderContextId}" name="orderContextId">
                <button type="submit" class="button--submit btn is--primary is--center bottom--comment-button">
                    {s name="SendComment"}Send comment{/s}
                </button>
            </div>
        </form>
    </div>
{/block}