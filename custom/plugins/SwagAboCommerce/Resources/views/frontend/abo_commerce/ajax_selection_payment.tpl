{namespace name="frontend/abo_commerce/orders"}

{block name='frontend_abonnement_payment_selection_modal'}
    <div class="div--payment-group">
        {block name='frontend_abonnement_payment_selection_form'}
            {$form_data = $sFormData}
            <div class="account--change-payment">
                <form class="abo-commerce-payment--selection-form" action="{url controller=AboCommerce action=updateAboPayment}" method="post">
                <input type="hidden" name="subscriptionId" value="{$form_data.subscriptionId}" />
                <input type="hidden" name="selectedPaymentId" value="{$form_data.selectedPaymentId}" />
                {block name="frontend_register_payment"}
                    <div class="panel register--payment">

                        {block name="frontend_abonnement_payment_headline"}
                            <h2 class="panel--title is--underline">{s name="AboCommercePaymentSelectionModalTitle"}{/s}</h2>
                        {/block}

                        {* Error messages *}
                        {block name="frontend_account_error_messages"}
                            <div class="abo-payment-selection-error"></div>
                        {/block}

                        {block name="frontend_abonnement_payment_fieldset"}
                            <div class="panel--body is--wide">
                                {foreach $paymentMeans as $payment_mean}

                                    {block name="frontend_abonnement_payment_method"}
                                        <div class="payment--method panel--tr">

                                            {block name="frontend_abonnement_payment_fieldset_input"}
                                                <div class="payment--selection-input">
                                                    {block name="frontend_abonnement_payment_fieldset_input_radio"}
                                                        <input type="radio" name="payment" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}"{if $payment_mean.id eq $form_data.selectedPaymentId or (!$form_data.selectedPaymentId && !$payment_mean@index)} checked="checked"{/if} />
                                                    {/block}
                                                </div>
                                                <div class="payment--selection-label">
                                                    {block name="frontend_abonnement_payment_fieldset_input_label"}
                                                        <label for="payment_mean{$payment_mean.id}" class="is--strong">
                                                            {$payment_mean.description}
                                                        </label>
                                                    {/block}
                                                </div>
                                            {/block}

                                            {block name="frontend_abonnement_payment_fieldset_description"}
                                                <div class="payment--description panel--td">
                                                    {include file="string:{$payment_mean.additionaldescription}"}
                                                </div>
                                            {/block}

                                            {block name='frontend_abonnement_payment_fieldset_template'}
                                                <div class="payment_logo_{$payment_mean.name}"></div>
                                                {if "frontend/plugins/payment/`$payment_mean.template`"|template_exists}
                                                    <div class="payment--content{if $payment_mean.id != $form_data.selectedPaymentId} is--hidden{/if}">
                                                        {include file="frontend/plugins/payment/`$payment_mean.template`" checked = ($payment_mean.id == $form_data.selectedPaymentId)}
                                                    </div>
                                                {/if}
                                            {/block}
                                        </div>
                                    {/block}

                                {/foreach}
                            </div>
                        {/block}

                    </div>

                    {block name="frontend_abonnement_payment_selection_modal_container_item_select_button"}
                        <button class="btn is--primary right abo-payment-selection-button"
                                type="submit"
                                data-checkFormIsValid="true"
                                data-preloader-button="true"
                                data-handle-payment-selection-url="{url controller=AboCommerce action=handlePaymentSelection}">
                            {s name="AboCommerceSelectPaymentMethodButton"}Use this payment method{/s}
                            <span class="icon--arrow-right"></span>
                        </button>
                    {/block}
                {/block}
            </form>
            </div>
        {/block}
    </div>
{/block}