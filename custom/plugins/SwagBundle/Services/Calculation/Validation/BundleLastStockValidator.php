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

namespace SwagBundle\Services\Calculation\Validation;

use Shopware\Models\Article\Detail;
use SwagBundle\Components\BundleBasketInterface;
use SwagBundle\Models\Bundle;

class BundleLastStockValidator implements BundleLastStockValidatorInterface
{
    /**
     * @var BundleBasketInterface
     */
    private $bundleBasketComponent;

    /**
     * @param BundleBasketInterface $bundleBasketComponent
     */
    public function __construct(BundleBasketInterface $bundleBasketComponent)
    {
        $this->bundleBasketComponent = $bundleBasketComponent;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Detail $selectedDetail, Bundle $bundle)
    {
        $basketQuantity = $this->bundleBasketComponent->getSummarizedQuantityOfVariant($selectedDetail);

        //check if the article last stock flag is set to true an no more stock exist.
        if ($this->notEnoughInStock($selectedDetail, $basketQuantity)
            || $this->notEnoughBundleInStock($basketQuantity, $bundle)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Validates if the selected detail has enough in stock.
     *
     * @param Detail $selectedDetail
     * @param int    $basketQuantity
     *
     * @return bool
     */
    private function notEnoughInStock(Detail $selectedDetail, $basketQuantity)
    {
        if (!$selectedDetail->getLastStock()) {
            return false;
        }

        if ($basketQuantity === 0 && $selectedDetail->getInStock() === 0) {
            return true;
        }

        return ($selectedDetail->getInStock() - $basketQuantity) <= 0;
    }

    /**
     * Validates that the quantity of the bundle + the basket quantity is higher than the quantity of the given selected variant
     *
     * @param int    $basketQuantity
     * @param Bundle $bundle
     *
     * @return bool
     */
    private function notEnoughBundleInStock(
        $basketQuantity,
        Bundle $bundle
    ) {
        if (!$bundle->getLimited()) {
            return false;
        }

        if ($basketQuantity === 0 && $bundle->getQuantity() === 0) {
            return true;
        }

        return $bundle->getQuantity() - $basketQuantity <= 0;
    }
}
