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

namespace SwagAboCommerce\Tests\Functional\Mocks;

use Shopware\Models\Article\Detail;
use SwagAboCommerce\Services\AboCommerceServiceInterface;

class AboCommerceServiceMock implements AboCommerceServiceInterface
{
    /**
     * @var array
     */
    private $aboCommerceData;

    /**
     * @param array $aboCommerceData
     */
    public function __construct(array $aboCommerceData)
    {
        $this->aboCommerceData = $aboCommerceData;
    }

    /**
     * {@inheritdoc}
     */
    public function getAboCommerceDataSelectedProduct(Detail $detail)
    {
        return $this->aboCommerceData;
    }

    /**
     * {@inheritdoc}
     */
    public function insertDiscountForProducts(Detail $variant, array $aboProduct, array $basketItem)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantByOrderNumber($orderNumber)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function updateBasketDiscount()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isAboCommerceProductInBasket()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isAboCommerceConfigurationInBasket($orderNumber)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerShop($shopId, $currency)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerOrder(array $aboOrder, array $order)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getUserData($orderId, array $aboOrder = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setAboCommerceFlagForProducts(array $products = [])
    {
    }

    /**
     * {@inheritdoc}
     */
    public function orderExists($orderId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isAboOrder($orderId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isInitialOrder($orderId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getIsAboExclusive($productId)
    {
        return false;
    }
}
