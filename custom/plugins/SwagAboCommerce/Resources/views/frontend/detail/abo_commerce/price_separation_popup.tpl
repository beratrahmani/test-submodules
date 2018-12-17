{namespace name="frontend/detail/abo_commerce_detail"}

{* price separation popup arrow  *}
{block name='abo_commerce_price_separation_popup_arrow'}
    <i class="abo--price-separation-popup-arrow"></i>
{/block}

{* price separation popup content *}
{block name='abo_commerce_price_separation_popup_inner'}
    <div class="abo--price-separation-inner-popup panel--table has--border">
        {block name='abo_commerce_price_separation_popup_content'}
            <div class="abo--price-separation-popup panel--table">

                {block name='abo_commerce_price_separation_popup_table_head'}
                    <div class="abo--separation-popup-table-head panel--tr">

                        {block name='abo_commerce_price_separation_popup_table_head_durationl'}
                            <div class="abo--table-head-duration panel--th">
                                {s name="AboCommercePriceSeparationDuration"}{/s}
                            </div>
                        {/block}

                        {block name='abo_commerce_price_separation_popup_table_head_discount'}
                            <div class="abo--table-head-discount panel--th">
                                {s name="AboCommercePriceSeparationRebate"}{/s}
                            </div>
                        {/block}
                    </div>
                {/block}

                {block name='abo_commerce_price_separation_popup_table_content'}
                    {foreach $aboCommerce.prices as $price}

                        {block name='abo_commerce_price_separation_popup_table_content_exclusion'}
                            {if $price@first === true && $price.discountAbsolute == 0}
                                {continue}
                            {/if}
                        {/block}

                        {block name='abo_commerce_price_separation_popup_table_content_data_row'}
                            <div class="abo--table-content-row panel--tr">

                                {block name='abo_commerce_price_separation_popup_table_content_data_row_duration'}
                                    <div class="abo--table-content-row-duration panel--td">
                                        {s name="AboCommercePriceSeparationFrom"}{/s} {$price.duration}
                                        {if $aboCommerce.durationUnit == 'months'}
                                            {s name="AboCommercePriceSeparationMonths"}{/s}
                                        {else}
                                            {s name="AboCommercePriceSeparationWeeks"}{/s}
                                        {/if}
                                    </div>
                                {/block}

                                {block name='abo_commerce_price_separation_popup_table_content_data_row_discount'}
                                    <div class="abo--table-content-row-discount panel--td">
                                        <strong>{$price.discountAbsolute|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</strong>
                                    </div>
                                {/block}
                            </div>
                        {/block}
                    {/foreach}
                {/block}
            </div>
        {/block}
    </div>
{/block}
