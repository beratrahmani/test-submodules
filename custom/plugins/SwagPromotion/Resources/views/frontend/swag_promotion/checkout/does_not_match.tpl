{foreach $promotionsDoNotMatch as $promotionDoesNotMatch}
    <div class="promotion--free-goods-block">
        {$content = "{s namespace='frontend/swag_promotion/main' name='promotionDoesNotMatch'}The campaign '{$promotionDoesNotMatch->name}' is currently not available for you. Please note the campaign description.{/s}"}
        {include file="frontend/_includes/messages.tpl" type="info"}
    </div>
{/foreach}