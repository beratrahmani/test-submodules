<?php
/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

namespace SwagPromotion\Components\Promotion\DiscountHandler\BasketHandler;

use SwagPromotion\Components\Promotion\DiscountCommand\Command\ShippingFreeCommand;
use SwagPromotion\Components\Promotion\DiscountHandler\DiscountHandler;
use SwagPromotion\Components\Services\DependencyProvider;
use SwagPromotion\Struct\Promotion;

class ShippingFreeHandler implements DiscountHandler
{
    const SHIPPING_FREE_HANDLER_NAME = 'basket.shippingfree';

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @param DependencyProvider $dependencyProvider
     */
    public function __construct(DependencyProvider $dependencyProvider)
    {
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountCommand($basket, $stackedProducts, Promotion $promotion)
    {
        return new ShippingFreeCommand();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($name)
    {
        return $name === self::SHIPPING_FREE_HANDLER_NAME;
    }
}
