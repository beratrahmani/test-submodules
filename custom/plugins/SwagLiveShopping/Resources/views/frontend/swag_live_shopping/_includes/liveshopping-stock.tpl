{block name='frontend_liveshopping_stock_inner'}
    <div class="counter--stock">
        <div class="stock--headline">
            {s name="sLiveStillOnly" namespace="frontend/live_shopping/main"}{/s}
        </div>
        <span class="stock--quantity">
            <span class="stock--quantity-number counter--number">{$liveShopping.quantity}</span>
            {if !$sArticle.packunit}
                {s name="sLivePiece" namespace="frontend/live_shopping/main"}{/s}
            {else}
                {$sArticle.packunit}
            {/if}
        </span>
    </div>
{/block}
