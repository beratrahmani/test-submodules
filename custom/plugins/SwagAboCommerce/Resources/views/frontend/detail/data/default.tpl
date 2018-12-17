{* Cache the aboCommerce array *}
{block name="frontend_abo_commerce_detail_data"}
    {include file='frontend/detail/abo_commerce/cache.tpl'}
{/block}

{* AboCommerce data *}
{block name="frontend_detail_data_abo_commerce_panel"}
    <div class="abo--commerce-container-panel panel">

        {block name="frontend_detail_data_abo_commerce_panel_body"}
            <div class="abo--commerce-container-panel-body panel--body"
                 data-swAboReferencePrice='true'
                 data-url='{url module=widgets controller=referencePrice action=index}'
                 data-referencePrice='{$sArticle.referenceprice}'
                 data-prices = '{$aboCommerce.discount_prices}'
                 data-isExclusive = '{$aboCommerce.isExclusive}'
            >

                {* Normal single purchase *}
                {block name="frontend_detail_data_abo_commerce_single"}
                    {if !$aboCommerce.isExclusive}
                        <div class="abo--single-delivery">
                            {include file='frontend/detail/abo_commerce/single_selection.tpl'}
                        </div>
                    {/if}
                {/block}

                {* Abo purchase *}
                {block name="frontend_detail_data_abo_commerce_abo"}
                    {if $aboCommerce.selectedDuration}
                        {assign var="aboPrice" value=$aboCommerce.prices.{$aboCommerce.selectedDuration}}
                    {else}
                        {assign var="aboPrice" value=$aboCommerce.prices.0}
                    {/if}
                    <div class="abo--delivery">

                        {* abo selection *}
                        {block name='abo_commerce_abo_selection'}
                            <div class="abo--selection">
                                {include file='frontend/detail/abo_commerce/abo_selection.tpl'}
                            </div>
                        {/block}

                        {* abo delivery label *}
                        {block name='abo_commerce_abo_selection_label'}
                            <div class="abo--delivery-label">
                                <label class="abo--discount-label" for="abo-delivery">{if $aboCommerce.hasDiscount}{s name="AboCommerceDetailSavingSubscription" namespace="frontend/plugins/abo_commerce"}{/s}{else}{s name="AboCommerceDetailSubscription" namespace="frontend/plugins/abo_commerce"}{/s}{/if}</label>
                                {if $aboCommerce.hasDiscount}
                                    <i class="abo--delivery-info-icon icon--info2"></i>
                                {/if}
                            </div>
                        {/block}

                        {* price separation popup *}
                        {block name='abo_commerce_price_separation_popup'}
                            <div class="abo--price-separation-popup panel is--hidden">
                                {include file='frontend/detail/abo_commerce/price_separation_popup.tpl'}
                            </div>
                        {/block}

                        {* abo description *}
                        {block name='abo_commerce_abo_selection_description'}
                            <div class="abo--info-description">
                                {if $aboCommerce.description}
                                    <div class="abo--description">{$aboCommerce.description|strip_tags|truncate:100}</div>
                                {/if}
                            </div>
                        {/block}

                        {* delivery interval *}
                        {block name='abo_commerce_abo_selection_delivery_interval'}
                            <div class="abo--delivery-interval-container{if !$aboCommerce.isExclusive} is--hidden{/if}">
                                {block name='abo_commerce_abo_selection_delivery_interval'}
                                    {include file='frontend/detail/abo_commerce/abo_delivery_interval.tpl'}
                                {/block}

                                {block name='abo_commerce_abo_selection_delivery_duration'}
                                    {include file='frontend/detail/abo_commerce/abo_delivery_duration.tpl'}
                                {/block}
                            </div>
                        {/block}

                        {block name='frontend_detail_abo_commerce_buy_button_container'}
                            {include file="frontend/detail/buy/container.tpl"}
                        {/block}
                    </div>
                {/block}
            </div>
        {/block}
    </div>
{/block}
