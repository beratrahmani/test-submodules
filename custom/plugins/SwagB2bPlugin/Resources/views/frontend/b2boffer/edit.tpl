{namespace name=frontend/plugins/b2b_debtor_plugin}

{extends file="parent:frontend/_base/modal-content.tpl"}

{block name="b2b_modal_base_settings"}
    {$modalSettings.actions = false}
    {$modalSettings.content.padding = true}
    {$modalSettings.bottom = true}
{/block}

{block name="b2b_modal_base_content_inner_topbar_headline"}
    {s name="MasterData"}Master data{/s}
{/block}

{block name="b2b_modal_base_content_inner_scrollable_inner_content_inner"}
    <form action="{url action=update}" method="post" data-ajax-panel-trigger-reload="role-grid" class="{b2b_acl controller=b2bofferlineitemreference action=update}">
        <input type="hidden" name="id" value="{$offer->id}">
        {include file="frontend/b2boffer/_form.tpl"}
    </form>
{/block}

{block name="b2b_modal_base_content_inner_scrollable_inner_bottom_inner"}
    <div class="bottom--actions">

        {if $offer->isEditableByUser()}
            <form action="{url controller=b2boffer action=sendOffer}"
                  class="{b2b_acl controller=b2boffer action=sendOffer} bottom-button"
                  data-close-success="true"
                  data-ajax-panel-trigger-reload="offer-grid"
            >
                <input type="hidden" name="offerId" value="{$offer->id}">
                <button class="btn is--primary component-action-create"{if !$itemCount} disabled="disabled" title="{s name="SubmitOfferRequestEmpty"}{/s}"{/if}>
                    {s name="SubmitOfferRequest"}Submit offer request{/s}</button>
            </form>
        {/if}

        {if $offer->status === 'offer_status_accepted_admin'}
            <form action="{url controller=b2boffer action=declineOffer}" class="{b2b_acl controller=b2boffer action=declineOffer} bottom-button">
                <input type="hidden" name="offerId" value="{$offer->id}">
                <button class="btn component-action-create">
                    {s name="DeclineOffer"}Decline Offer{/s}</button>
            </form>

            <form action="{url controller=b2boffer action=accept}" class="{b2b_acl controller=b2boffer action=accept} ignore--b2b-ajax-panel bottom-button">
                <input type="hidden" name="offerId" value="{$offer->id}">
                <button class="btn is--primary component-action-create">
                    {s name="AcceptOffer"}Accept Offer{/s}</button>
            </form>
        {/if}

        {if $offer->status === 'offer_status_accepted_both' || $offer->status === 'offer_status_accepted_user'}
            <form action="{url controller=b2boffer action=declineOffer}" class="{b2b_acl controller=b2boffer action=declineOffer} bottom-button">
                <input type="hidden" name="offerId" value="{$offer->id}">
                <button class="btn component-action-create">
                    {s name="DeclineOffer"}Decline Offer{/s}</button>
            </form>
        {/if}

        {if $offer->status === 'offer_status_accepted_both'}
            <form action="{url controller=b2boffer action=accept}" class="{b2b_acl controller=b2boffer action=accept} ignore--b2b-ajax-panel bottom-button">
                <input type="hidden" name="offerId" value="{$offer->id}">
                <button class="btn is--primary component-action-create">
                    {s name="AcceptOffer"}Accept Offer{/s}</button>
            </form>
        {/if}
    </div>
{/block}