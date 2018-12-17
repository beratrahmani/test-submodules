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

namespace SwagPromotion\Components\Promotion;

use SwagPromotion\Components\Promotion\DiscountCommand\DiscountCommandRegistry;
use SwagPromotion\Components\Promotion\DiscountHandler\DiscountHandlerRegistry;
use SwagPromotion\Components\Promotion\ProductStacker\ProductStackRegistry;
use SwagPromotion\Struct\Promotion;

class PromotionDiscount
{
    /**
     * @var DiscountHandlerRegistry
     */
    private $discountRegistry;

    /**
     * @var ProductStackRegistry
     */
    private $productStackerRegistry;

    /**
     * @var DiscountCommandRegistry
     */
    private $discountCommandRegistry;

    /**
     * @param DiscountHandlerRegistry $discountHandlerRegistry
     * @param ProductStackRegistry    $productStackRegistry
     * @param DiscountCommandRegistry $discountCommandRegistry
     */
    public function __construct(
        DiscountHandlerRegistry $discountHandlerRegistry,
        ProductStackRegistry $productStackRegistry,
        DiscountCommandRegistry $discountCommandRegistry
    ) {
        $this->discountRegistry = $discountHandlerRegistry;
        $this->productStackerRegistry = $productStackRegistry;
        $this->discountCommandRegistry = $discountCommandRegistry;
    }

    /**
     * @param Promotion $promotion
     * @param array     $basket
     * @param array     $matchingProducts
     *
     * @return bool
     */
    public function apply(Promotion $promotion, array $basket, array $matchingProducts)
    {
        $command = $this->getDiscountCommand($promotion, $basket, $matchingProducts);

        $handler = $this->discountCommandRegistry->get($command->getName());

        return $handler->handle($command, $promotion, $basket);
    }

    /**
     * @param Promotion $promotion
     * @param array     $basket
     * @param array     $matchingProducts
     *
     * @return DiscountCommand\Command\Command;
     */
    private function getDiscountCommand(Promotion $promotion, array $basket, array $matchingProducts)
    {
        $type = $promotion->type;
        $stackedProducts = $this->productStackerRegistry->getStacker($promotion->stackMode)->getStack(
            $matchingProducts,
            $promotion->step,
            $promotion->maxQuantity,
            $promotion->chunkMode
        );

        if ($promotion->maxQuantity && count($stackedProducts) > $promotion->maxQuantity) {
            $stackedProducts = array_slice($stackedProducts, 0, $promotion->maxQuantity);
        }

        $discountCommand = $this->discountRegistry->get($type)
            ->getDiscountCommand($basket, $stackedProducts, $promotion);

        return $discountCommand;
    }
}
