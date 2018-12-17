{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.actions = false}
    {$modalSettings.content.padding = false}
    {$modalSettings.bottom = true}
{/block}

{* Title Placeholder *}
{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="OrderDecline"}Decline Order{/s}
{/block}

{* Modal Content: Grid Component: Content *}
{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    <form action="{url action=declineOrder}" method="post"  data-ajax-panel-trigger-reload="order-grid,order-clearance-grid" id="form" class="form--inline {b2b_acl controller=b2borderclearance action=declineOrder}">
        <input type="hidden" name="orderContextId" value="{$order->id}"/>
        <div class="scrollable with--padding">
            <div class="block-group b2b--form">
                {if $order->orderReference}
                    <div class="block box--label">
                        <strong>{s name="Order"}Order{/s}:</strong>
                        {$order->orderReference}
                    </div>
                {/if}
            </div>

            <div class="block-group b2b--form">
                <div class="block box--label is--full">
                    {s name="Comment"}Comment{/s}:
                </div>

                <div class="block box--input is--full">
                    <textarea name="comment" cols="30" rows="10" class="textarea--full" placeholder="{s name="CommentPlaceholderDecline"}Compose a comment why you decline this order.{/s}"></textarea>
                </div>
            </div>
        </div>
    </form>
{/block}

{* Modal Content: Grid Component: Bottom *}
{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <div class="bottom--actions">
        <button type="submit" class="btn is--primary is--small component-action-delete {b2b_acl controller=b2borderclearance action=decline}" data-form-id="form" title="{s name="DeclineOrder"}Decline Order{/s}">
            {s name="OrderDecline"}Decline order{/s}
        </button>
    </div>
{/block}