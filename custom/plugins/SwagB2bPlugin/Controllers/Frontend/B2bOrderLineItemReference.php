<?php declare(strict_types=1);

use Shopware\B2B\Common\Frontend\ControllerProxy;

class Shopware_Controllers_Frontend_B2bOrderLineItemReference extends ControllerProxy
{
    /**
     * @see \Shopware\B2B\Order\Frontend\OrderLineItemReferenceController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_order.line_item_controller';
    }
}
