{namespace name="frontend/abo_commerce/orders"}

{block name="frontend_account_abonnement_item_detail_info_labels"}
    <div class="panel--td column--info-labels">

        {* Duration label *}
        {block name="frontend_account_abonnement_item_detail_duration_label"}
            <p class="is--strong">{s name="AboCommerceOrdersDuration"}{/s}</p>
        {/block}

        {* Delivery interval label *}
        {block name="frontend_account_abonnement_item_detail_delivery_intervall_label"}
            <p class="is--strong">{s name="AboCommerceOrdersDeliveryInterval"}{/s}</p>
        {/block}

        {* Abonnement next delivery label *}
        {block name="frontend_account_abonnement_item_label_delivery"}
            <p class="is--strong">{s name="AboCommerceOrdersNextDelivery"}{/s}</p>
        {/block}

        {* Period if notice label if endless subscription *}
        {if $order.endlessSubscription}
            {block name='frontend_account_abonnement_item_label_period_of_notice'}
                <p class="is--strong">{s name="AboCommerceOrdersPeriodOfNotice"}{/s}</p>
            {/block}

            {if $order.lastRun != null}
                {* Last run of endless subscription *}
                {block name='frontend_account_abonnement_item_detail_endless_last_run_label'}
                    <p class="is--strong">{s name="AboCommerceOrdersExpiry"}{/s}</p>
                {/block}
            {/if}
        {/if}
    </div>
{/block}

{block name="frontend_account_abonnement_item_detail_info_data"}
    <div class="panel--td column--info-data">

        {* Duration data *}
        {block name="frontend_account_abonnement_item_detail_duration"}
            <p>{if $order.endlessSubscription}{s name="AboCommerceOrdersEndlessSubscription"}{/s}{else}{$order.duration} {if $order.durationUnit eq 'months'}{s name="AboCommerceOrdersMonths"}{/s}{else}{s name="AboCommerceOrdersWeeks"}{/s}{/if}{/if}</p>
        {/block}

        {* Delivery interval data *}
        {block name="frontend_account_abonnement_item_detail_delivery_intervall"}
            <p>{s name="AboCommerceOrdersEach"}{/s} {$order.deliveryInterval} {if $order.deliveryIntervalUnit eq 'months'}{s name="AboCommerceOrdersMonths"}{/s}{else}{s name="AboCommerceOrdersWeeks"}{/s}{/if}</p>
        {/block}

        {* Abonnement total *}
        {block name='frontend_account_abonnement_item_delivery'}
            <p>{$order.dueDate|date:DATE_MEDIUM}</p>
        {/block}

        {if $order.endlessSubscription}
            {block name='frontend_account_abonnement_item_detail_period_of_notice'}
                <p>{if $order.directTermination eq true}{s name="AboCommerceOrdersTerminateAnytime"}{/s}{else}{$order.periodOfNoticeInterval} {if $order.periodOfNoticeUnit eq 'months'}{s name="AboCommerceOrdersMonths"}{/s}{else}{s name="AboCommerceOrdersWeeks"}{/s}{/if}{/if}</p>
            {/block}

            {if $order.lastRun != null}
                {* Last run of endless subscription *}
                {block name='frontend_account_abonnement_item_detail_endless_last_run'}
                    <p>{$order.lastRun|date:DATE_MEDIUM}</p>
                    <p>{s name="AboCommerceOrdersTerminated"}{/s}</p>
                    <p>({$order.terminationDate|date:DATE_MEDIUM})</p>
                {/block}
            {/if}

        {/if}

    </div>
{/block}

{block name="frontend_account_abonnement_item_detail_summary_labels"}
    <div class="panel--td column--summary-labels">

        {* Abonnement dispatch-costs label *}
        {block name="frontend_account_abonnement_item_label_dispatch"}
            <p class="is--strong">{s name="AboCommerceOrdersShippingCosts"}{/s}</p>
        {/block}

        {* Abonnement total label *}
        {block name="frontend_account_abonnement_item_label_total"}
            <p class="is--strong">{s name="AboCommerceOrdersTotalAmount"}{/s}</p>
        {/block}

    </div>
{/block}

{block name="frontend_account_abonnement_item_detail_summary_data"}
    <div class="panel--td column--summary-data">

        {* Abonnement dispatch *}
        {block name='frontend_account_abonnement_item_dispatch'}
            <p>{$order.order.invoiceShipping|currency}</p>
        {/block}

        {* Abonnement total *}
        {block name='frontend_account_abonnement_item_total'}
            <p>{$order.order.invoiceAmount|currency}</p>
        {/block}

    </div>
{/block}

{if $order.endlessSubscription && $order.lastRun eq null}
    {block name="frontend_account_abonnement_item_detail_termination_button"}
        <div class="abo--termination-button"
             data-aboTerminationButton="true"
             data-modalTitle="{s name="AboCommerceTerminationModalTitle"}{/s}"
             data-content="{s name="AboCommerceTerminationModalContent"}{/s}"
             data-cancelButtonText="{s name="AboCommerceTerminationCancelButtonText"}{/s}"
             data-terminateButtonText="{s name="AboCommerceTerminationTerminateButtonText"}{/s}">
            <a class="btn is--secondary right"
              href="{url controller=AboCommerce action=terminate orderId=$order.id}">
                {s name="AboCommerceOrdersTerminate"}{/s}
            </a>
        </div>
    {/block}
{/if}

