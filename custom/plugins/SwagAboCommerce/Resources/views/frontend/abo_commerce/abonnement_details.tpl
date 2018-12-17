{namespace name="frontend/abo_commerce/orders"}

<div id="order{$order.lastOrder.number}{$order.id}" class="order--details panel--table">
    {block name="frontend_account_abonnement_item_detail_table"}
        {block name="frontend_account_abonnement_item_detail_id"}
            <input type="hidden" name="sAddAccessories" value="{$order.lastOrder.number|escape}"/>
        {/block}

        {block name="frontend_account_abonnement_item_detail_table_head"}
            <div class="orders--table-header panel--tr is--secondary">

                {block name="frontend_account_abonnement_item_detail_table_head_name"}
                    <div class="panel--th column--name">{s name="AboCommerceOrdersArticle"}{/s}</div>
                {/block}

                {block name="frontend_account_abonnement_item_detail_table_head_article_number"}
                    <div class="panel--th column--article_number">{s name="AboCommerceOrdersArticleNumber"}{/s}</div>
                {/block}

                {block name="frontend_account_abonnement_item_detail_table_head_quantity"}
                    <div class="panel--th column--quantity is--align-center">{s name="AboCommerceOrdersQuantity"}{/s}</div>
                {/block}

                {block name="frontend_account_abonnement_item_detail_table_head_total"}
                    <div class="panel--th column--total is--align-right">{s name="AboCommerceOrdersSum"}{/s}</div>
                {/block}
            </div>
        {/block}

        {block name="frontend_account_abonnement_item_detail_table_rows"}

            {block name="frontend_account_abonnement_item_detail_table_row_main"}
                <div class="panel--tr">
                    {block name="frontend_account_abonnement_item_info"}
                        <div class="panel--td order--info column--name">

                            {* Name *}
                            {block name="frontend_account_abonnement_item_name"}
                                <p class="order--name is--strong">
                                    <strong>{$order.articleOrderDetail.articleName}</strong>
                                </p>
                            {/block}
                        </div>
                    {/block}

                    {* Abonnement item product number *}
                    {block name='frontend_account_abonnement_item_article_number'}
                        <div class="panel--td order--article_number column--article_number">

                            {block name='frontend_account_abonnement_item_article_number_label'}
                                <div class="column--label">{s name="AboCommerceOrdersArticleNumber"}{/s}</div>
                            {/block}

                            {block name='frontend_account_abonnement_item_article_number_value'}
                                <div class="column--value">{$order.articleOrderDetail.articleNumber}</div>
                            {/block}
                        </div>
                    {/block}

                    {* Abonnement item quantity *}
                    {block name='frontend_account_abonnement_item_quantity'}
                        <div class="panel--td abo--quantity column--quantity">

                            {block name='frontend_account_abonnement_item_quantity_label'}
                                <div class="column--label">{s name="AboCommerceOrdersQuantity"}{/s}</div>
                            {/block}

                            {block name='frontend_account_abonnement_item_quantity_value'}
                                <div class="column--value">{$order.articleOrderDetail.quantity}x</div>
                            {/block}
                        </div>
                    {/block}

                    {* Abonnement items total amount *}
                    {block name='frontend_account_abonnement_item_amount'}
                        <div class="panel--td order--amount column--total">

                            {block name='frontend_account_abonnement_item_amount_label'}
                                <div class="column--label">{s name="AboCommerceOrdersSum"}{/s}</div>
                            {/block}

                            {block name='frontend_account_abonnement_item_amount_value'}
                                <div class="column--value">
                                    <strong>{"{$order.articleOrderDetail.price * $order.articleOrderDetail.quantity}"|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</strong>
                                </div>
                            {/block}
                        </div>
                    {/block}
                </div>
            {/block}

            {if $order.discountOrderDetail !== null}
                {block name="frontend_account_abonnement_item_detail_table_row_discount"}
                    <div class="panel--tr">
                        {block name="frontend_account_abonnement_item_info"}
                            <div class="panel--td order--info column--name">

                                {* Name *}
                                {block name="frontend_account_abonnement_item_name"}
                                    <p class="order--name is--strong">
                                        {s name="AboCommerceOrdersAboRebate"}{/s}
                                    </p>
                                {/block}
                            </div>
                        {/block}

                        {* Abonnement item product number *}
                        {block name='frontend_account_abonnement_item_article_number'}
                            <div class="panel--td order--article_number column--article_number">

                                {block name='frontend_account_abonnement_item_article_number_label'}
                                    <div class="column--label">{s name="AboCommerceOrdersArticleNumber"}{/s}</div>
                                {/block}

                                {if $order.discountOrderDetail != null}
                                    {block name='frontend_account_abonnement_item_article_number_value'}
                                        <div class="column--value">{$order.discountOrderDetail.articleNumber}</div>
                                    {/block}
                                {/if}

                            </div>
                        {/block}

                        {* Abonnement item quantity *}
                        {block name='frontend_account_abonnement_item_quantity'}
                            <div class="panel--td abo--quantity column--quantity">

                                {block name='frontend_account_abonnement_item_quantity_label'}
                                    <div class="column--label">{s name="AboCommerceOrdersQuantity"}{/s}</div>
                                {/block}

                                {block name='frontend_account_abonnement_item_quantity_value'}
                                    <div class="column--value">{$order.discountOrderDetail.quantity}x</div>
                                {/block}

                            </div>
                        {/block}

                        {* Abonnement item total amount *}
                        {block name='frontend_account_abonnement_item_amount'}
                            <div class="panel--td order--amount column--total">

                                {block name='frontend_account_abonnement_item_amount_label'}
                                    <div class="column--label">{s name="AboCommerceOrdersSum"}{/s}</div>
                                {/block}

                                {block name='frontend_account_abonnement_item_amount_value'}
                                    <div class="column--value">
                                        <strong>{$order.discountOrderDetail.price|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}</strong>
                                    </div>
                                {/block}

                            </div>
                        {/block}
                    </div>
                {/block}
            {/if}
        {/block}
        <div class="panel--tr is--odd">
            {include file="frontend/abo_commerce/abonnement_footer.tpl"}
        </div>
        <div class="panel--tr is--odd">
            {if $allowPaymentChange eq true }
                {include file="frontend/abo_commerce/abonnement_payment.tpl"}
            {/if}

            {include file="frontend/abo_commerce/abonnement_shipping.tpl"}

            {include file="frontend/abo_commerce/abonnement_billing.tpl"}
        </div>
    {/block}
</div>
