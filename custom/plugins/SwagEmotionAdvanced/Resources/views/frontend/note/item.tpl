{extends file='parent:frontend/note/item.tpl'}

{* add order number of product to note template. is used by the quick view jquery plugin *}
{block name='frontend_note_item_info'}
    {block name='frontend_note_item_info_quick_view'}
        <div class="note--item--quick-view-wrapper" data-ordernumber="{$sBasketItem.ordernumber}">
            {$smarty.block.parent}
        </div>
    {/block}
{/block}
