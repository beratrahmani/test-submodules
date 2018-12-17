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

namespace SwagPromotion\Components;

use Enlight_Components_Db_Adapter_Pdo_Mysql as PDOConnection;
use SwagPromotion\Components\DataProvider\BasketDataProvider;
use SwagPromotion\Components\DataProvider\CustomerDataProvider;
use SwagPromotion\Components\DataProvider\ProductDataProvider;
use SwagPromotion\Components\Services\DependencyProviderInterface;

class BasketContextBuilder
{
    /**
     * @var PDOConnection
     */
    private $database;

    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @var ProductDataProvider
     */
    private $productDataProvider;

    /**
     * BasketContextBuilder constructor.
     *
     * @param PDOConnection               $database
     * @param DependencyProviderInterface $dependencyProvider
     * @param ProductDataProvider         $productDataProvider
     */
    public function __construct(
        PDOConnection $database,
        DependencyProviderInterface $dependencyProvider,
        ProductDataProvider $productDataProvider
    ) {
        $this->database = $database;
        $this->dependencyProvider = $dependencyProvider;
        $this->productDataProvider = $productDataProvider;
    }

    /**
     * @param array $basket
     * @param array $products
     * @param array $customer
     *
     * @return Rules\RuleBuilder
     */
    public function getBasketRuleBuilder(array $basket, array $products, array $customer)
    {
        $ruleBuilder = new BasketRuleBuilder();

        return $ruleBuilder->create($basket, $products, $customer);
    }

    /**
     * @param array $basket
     *
     * @return array
     */
    public function getProductData(array $basket = [])
    {
        if ($basket) {
            $orderNumbers = [];
            foreach ($basket['content'] as $product) {
                if ($product['articleID']) {
                    if (isset($orderNumbers[$product['ordernumber']])) {
                        $orderNumbers[$product['ordernumber']] += $product['quantity'];
                    } else {
                        $orderNumbers[$product['ordernumber']] = $product['quantity'];
                    }
                }
            }

            return $this->productDataProvider->get($orderNumbers);
        }

        return $this->productDataProvider->get($this->getOrderNumbers());
    }

    /**
     * @return array
     */
    public function getBasketData()
    {
        $basketDataProvider = new BasketDataProvider($this->dependencyProvider->getSession(), $this->database);

        return $basketDataProvider->get();
    }

    /**
     * @param string $customerId
     *
     * @return array
     */
    public function getCustomerData($customerId)
    {
        $customerDataProvider = new CustomerDataProvider($this->database);

        return $customerDataProvider->get($customerId);
    }

    /**
     * @return array
     */
    private function getOrderNumbers()
    {
        $sql = 'SELECT ordernumber, quantity FROM s_order_basket WHERE sessionID = :sessionId AND modus = 0';

        return $this->database->fetchPairs($sql, ['sessionId' => $this->dependencyProvider->getSession()->get('sessionId')]);
    }
}
