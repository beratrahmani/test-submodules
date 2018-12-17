<?php declare(strict_types=1);

use Shopware\B2B\Common\Frontend\ControllerProxy;

class Shopware_Controllers_Frontend_B2bCategorySelect extends ControllerProxy
{
    /**
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_shop.category_select_controller';
    }
}
