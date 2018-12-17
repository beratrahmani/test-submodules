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

namespace SwagPromotion\Components\Services;

use Doctrine\DBAL\Connection;
use Enlight_Components_Session_Namespace as Session;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Components\Cart\BasketHelper;
use Shopware\Components\Cart\BasketHelperInterface;
use Shopware\Components\Cart\Struct\DiscountContext;
use Shopware\Components\DependencyInjection\Bridge\Config;
use Shopware_Components_Config;
use SwagPromotion\Components\Cart\BasketQueryHelperDecorator;
use SwagPromotion\Components\Promotion\CurrencyConverter;
use SwagPromotion\Struct\Promotion;

class BasketService
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CurrencyConverter
     */
    private $currencyConverter;

    /**
     * @var BasketHelper
     */
    private $basketHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @param Connection                 $connection
     * @param Session                    $session
     * @param CurrencyConverter          $currencyConverter
     * @param BasketHelper               $basketHelper
     * @param Shopware_Components_Config $config
     * @param ContextServiceInterface    $contextService
     */
    public function __construct(
        Connection $connection,
        Session $session,
        CurrencyConverter $currencyConverter,
        BasketHelper $basketHelper,
        Shopware_Components_Config $config,
        ContextServiceInterface $contextService
    ) {
        $this->connection = $connection;
        $this->session = $session;
        $this->currencyConverter = $currencyConverter;
        $this->basketHelper = $basketHelper;
        $this->config = $config;
        $this->contextService = $contextService;
    }

    /**
     * @param Promotion $promotion
     * @param float     $discountGross
     * @param float     $discountNet
     * @param string    $taxRate
     */
    public function insertDiscount(Promotion $promotion, $discountGross, $discountNet, $taxRate)
    {
        if (!$promotion->number) {
            $promotion->number = 'promotion-' . $promotion->id;
        }

        if ($this->config->get('proportionalTaxCalculation') &&
            !$this->session->get('taxFree') &&
            $promotion->type !== 'product.freegoods'
        ) {
            $discountContext = $this->createDiscountContext($promotion, $discountGross);
            $discountContext->addAttribute(
                BasketQueryHelperDecorator::ATTRIBUTE_COLUMN_PROMOTION_ID,
                new Attribute(['id' => $promotion->id]));

            $this->basketHelper->addProportionalDiscount($discountContext);

            return;
        }

        if ($this->session->get('taxFree')) {
            $this->addDiscount($promotion, $discountNet, $discountNet, 0.0);

            return;
        }

        $this->addDiscount($promotion, $discountGross, $discountNet, $taxRate);
    }

    /**
     * @param Promotion $promotion
     * @param float     $discountGross
     * @param float     $discountNet
     * @param string    $taxRate
     */
    private function addDiscount(Promotion $promotion, $discountGross, $discountNet, $taxRate)
    {
        $basketQuery = $this->getBasketInsertQuery($promotion, $discountGross, $discountNet, $taxRate);
        $basketQuery->execute();

        $basketId = $this->connection->lastInsertId('s_order_basket');

        $attributeQuery = $this->getAttributeInsertQuery($basketId, $promotion->id);

        if ($promotion->type === 'basket.shippingfree') {
            $attributeQuery->setValue('swag_is_shipping_free_promotion', true);
        }

        $attributeQuery->execute();
    }

    /**
     * @param int $basketId
     * @param int $promotionId
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getAttributeInsertQuery($basketId, $promotionId)
    {
        return $this->connection->createQueryBuilder()
            ->insert('s_order_basket_attributes')
            ->setValue('basketID', ':basketId')
            ->setValue('swag_promotion_id', ':promotionId')
            ->setParameters([
                'basketId' => $basketId,
                'promotionId' => $promotionId,
            ]);
    }

    /**
     * @param Promotion $promotion
     * @param float     $discountGross
     * @param float     $discountNet
     * @param string    $taxRate
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getBasketInsertQuery(Promotion $promotion, $discountGross, $discountNet, $taxRate)
    {
        $userId = $this->session->get('sUserId') ?: 0;

        return $this->connection->createQueryBuilder()
            ->insert('s_order_basket')
            ->setValue('sessionID', ':sessionId')
            ->setValue('userID', ':userID')
            ->setValue('articlename', ':articlename')
            ->setValue('ordernumber', ':ordernumber')
            ->setValue('price', ':price')
            ->setValue('netprice', ':netprice')
            ->setValue('tax_rate', ':tax_rate')
            ->setValue('currencyfactor', ':currencyfactor')
            ->setValue('shippingfree', ':shippingfree')
            ->setValue('articleID', 0)
            ->setValue('quantity', 1)
            ->setValue('modus', 4)
            ->setParameters([
                'sessionId' => $this->session->get('sessionId'),
                'userID' => $userId,
                'articlename' => $promotion->name,
                'ordernumber' => $promotion->number,
                'price' => $discountGross,
                'netprice' => $discountNet,
                'tax_rate' => $taxRate,
                'currencyfactor' => $this->currencyConverter->getFactor(),
                'shippingfree' => (int) $promotion->shippingFree,
            ]);
    }

    /**
     * @param Promotion $promotion
     * @param float     $discountGross
     *
     * @return DiscountContext
     */
    private function createDiscountContext(Promotion $promotion, $discountGross)
    {
        $discountContext = new DiscountContext(
            $this->session->get('sessionId'),
            BasketHelperInterface::DISCOUNT_ABSOLUTE,
            $discountGross,
            $promotion->name,
            $promotion->number,
            4, // MODE: 4 is default for promotions
            $this->currencyConverter->getFactor(),
            !$this->contextService->getShopContext()->getCurrentCustomerGroup()->displayGrossPrices()
        );

        return $discountContext;
    }
}
