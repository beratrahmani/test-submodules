{extends file="parent:frontend/address/ajax_selection.tpl"}

{namespace name="frontend/address/ajax_selection"}

{block name="frontend_address_selection_modal_create_text"}
    <div><p>{s name="AboCommerceAddressInfo" namespace="frontend/abo_commerce/orders"}{/s}</p></div>
{/block}

{block name='frontend_address_selection_modal_container_item_actions'}
    <div class="panel--actions">
        <form class="address-manager--selection-form" action="{url controller=AboCommerce action=handleAddressSelection}" method="post">
            <input type="hidden" name="id" value="{$address.id}" />

            <input type="hidden" name="subscriptionId" value="{$subscriptionId}" />
            <input type="hidden" name="subscriptionAddressType" value="{$subscriptionAddressType}" />

            {block name="frontend_address_selection_modal_container_item_select_button"}
                <button class="btn is--block is--primary is--icon-right"
                        type="submit"
                        data-checkFormIsValid="false"
                        data-preloader-button="true">
                    {s name="SelectAddressButton"}Use this address{/s}
                    <span class="icon--arrow-right"></span>
                </button>
            {/block}
        </form>
    </div>
{/block}