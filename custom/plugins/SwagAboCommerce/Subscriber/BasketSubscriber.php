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

namespace SwagAboCommerce\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Template_Manager;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Basket;
use Shopware_Components_Config;
use Shopware_Components_Snippet_Manager;
use SwagAboCommerce\Models\Settings;
use SwagAboCommerce\Services\AboCommerceServiceInterface;
use SwagAboCommerce\Services\DependencyProviderInterface;

class BasketSubscriber implements SubscriberInterface
{
    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var AboCommerceServiceInterface
     */
    private $aboCommerceService;

    /**
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var Enlight_Template_Manager
     */
    private $templateManager;

    /**
     * @param DependencyProviderInterface         $dependencyProvider
     * @param AboCommerceServiceInterface         $aboCommerceService
     * @param ModelManager                        $modelManager
     * @param Shopware_Components_Snippet_Manager $snippetManager
     * @param Connection                          $connection
     * @param Shopware_Components_Config          $config
     * @param Enlight_Template_Manager            $templateManager
     */
    public function __construct(
        DependencyProviderInterface $dependencyProvider,
        AboCommerceServiceInterface $aboCommerceService,
        ModelManager $modelManager,
        Shopware_Components_Snippet_Manager $snippetManager,
        Connection $connection,
        Shopware_Components_Config $config,
        Enlight_Template_Manager $templateManager
    ) {
        $this->dependencyProvider = $dependencyProvider;
        $this->aboCommerceService = $aboCommerceService;
        $this->modelManager = $modelManager;
        $this->snippetManager = $snippetManager;
        $this->connection = $connection;
        $this->config = $config;
        $this->templateManager = $templateManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Basket_UpdateArticle_Start' => 'onUpdateProduct',
            'Shopware_Controllers_Frontend_Checkout::getBasket::after' => 'onAfterGetBasket',
            'sBasket::sAddArticle::before' => 'onBeforeAddProduct',
            'sBasket::sAddArticle::after' => 'onAfterAddProduct',
            'Shopware_Modules_Basket_AddVoucher_Start' => 'onBasketAddVoucherStart',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     *
     * @return bool
     */
    public function onUpdateProduct(\Enlight_Event_EventArgs $args)
    {
        $skipProductUpdateBasketId = (int) $this->dependencyProvider->getSession()->get('aboIsRecurringOrderProductBasketId');
        $skipProductUpdate = (int) $args->get('id') === $skipProductUpdateBasketId;

        if ($skipProductUpdate) {
            return true;
        }
    }

    /**
     * Extends the default function "sBasket->sGetBasket" to consider abo products
     *
     * @param \Enlight_Hook_HookArgs $args
     */
    public function onAfterGetBasket(\Enlight_Hook_HookArgs $args)
    {
        /** @var array[] $basket */
        $basket = $args->getReturn();

        $discountQuery = $this->getDiscountQuery();
        foreach ($basket['content'] as &$contentItem) {
            $attributes = $discountQuery->setParameter('basketId', $contentItem['id'])
                ->execute()
                ->fetch(\PDO::FETCH_ASSOC);

            if (!$attributes) {
                continue;
            }

            $contentItem['abo_attributes']['isAboDiscount'] = true;
            $contentItem['abo_attributes']['swagAboCommerceId'] = $attributes['swag_abo_commerce_id'];
        }
        unset($contentItem);

        $aboQuery = $this->getAboQuery();
        $containsAboProduct = false;
        foreach ($basket['content'] as &$item) {
            $attributes = $aboQuery->setParameter('basketId', $item['id'])
                ->execute()
                ->fetch(\PDO::FETCH_ASSOC);

            if (!$attributes) {
                continue;
            }

            $variant = $this->aboCommerceService->getVariantByOrderNumber($item['ordernumber']);
            if (!$variant) {
                continue;
            }

            $aboCommerceData = $this->aboCommerceService->getAboCommerceDataSelectedProduct($variant);
            if (empty($aboCommerceData)) {
                continue;
            }

            $item['aboCommerce'] = $aboCommerceData;

            // calculate the basket for the item
            $aboBasket = $this->getBasketForAbo($item, $attributes['swag_abo_commerce_duration']);

            $item = $this->prepareAndCalculate($attributes, $aboBasket, $item);

            $containsAboProduct = true;
        }
        unset($item);

        $basket['containsAboArticle'] = $containsAboProduct;

        $args->setReturn($basket);
    }

    /**
     * If product is in basket as abo and you want to add the same
     * product as normal, the abo orderNumber is changed temporary to
     * prevent the standard sAddArticle function to count up the quantity
     * of the abo product
     *
     * @param \Enlight_Hook_HookArgs $args
     */
    public function onBeforeAddProduct(\Enlight_Hook_HookArgs $args)
    {
        $orderNumber = $args->get('id');

        $sessionId = $this->dependencyProvider->getSession()->get('sessionId');

        // look, if already an product with the same orderNumber is in the basket
        $basketItems = $this->modelManager->getRepository(Basket::class)
            ->findBy(['orderNumber' => $orderNumber, 'sessionId' => $sessionId]);

        // return, if not
        if (empty($basketItems)) {
            return;
        }

        /* @var Basket $item */
        foreach ($basketItems as $item) {
            $basketAttribute = $item->getAttribute();
            if (!$basketAttribute) {
                continue;
            }
            // if these attributes are set, it is an abo product, so we change the orderNumber
            if ($basketAttribute->getSwagAboCommerceId()) {
                $item->setOrderNumber('SWAG_ABO_TEMP');
                $this->modelManager->persist($item);
            }
        }

        $this->modelManager->flush();
    }

    /**
     * Revert the changes, which are done by the onBeforeAddProduct method
     *
     * @see onBeforeAddProduct()
     *
     * @param \Enlight_Hook_HookArgs $args
     */
    public function onAfterAddProduct(\Enlight_Hook_HookArgs $args)
    {
        $orderNumber = $args->get('id');

        $sessionId = $this->dependencyProvider->getSession()->get('sessionId');

        $basketItems = $this->modelManager->getRepository(Basket::class)
            ->findBy(['orderNumber' => 'SWAG_ABO_TEMP', 'sessionId' => $sessionId]);

        if (empty($basketItems)) {
            return;
        }

        /* @var Basket $item */
        foreach ($basketItems as $item) {
            $basketAttribute = $item->getAttribute();
            if (!$basketAttribute) {
                continue;
            }
            if ($basketAttribute->getSwagAboCommerceId()) {
                $item->setOrderNumber($orderNumber);
                $this->modelManager->persist($item);
            }
        }

        $this->modelManager->flush();
    }

    /**
     * show error if voucher usage is not allowed
     *
     * @return bool
     */
    public function onBasketAddVoucherStart()
    {
        /* @var Settings[] $settings */
        //there is column for specific shop, but is not in use.
        //if this plugin will support multiple shop settings change this find request
        $settings = $this->modelManager->getRepository(Settings::class)->findAll();

        $settings = $settings[0];
        $allowVoucherUsage = $settings->getAllowVoucherUsage();
        if (!$allowVoucherUsage) {
            $basket = $this->modelManager->getRepository(Basket::class)
                ->findBy(['sessionId' => $this->dependencyProvider->getSession()->get('sessionId')]);

            if (!$basket) {
                return;
            }
            /* @var Basket $item */
            foreach ($basket as $item) {
                $basketAttribute = $item->getAttribute();
                if (!$basketAttribute) {
                    continue;
                }
                if ($basketAttribute->getSwagAboCommerceDeliveryInterval()
                    || $basketAttribute->getSwagAboCommerceDuration()
                    || $basketAttribute->getSwagAboCommerceId()
                ) {
                    $errorMessage = $this->snippetManager->getNamespace('frontend/checkout/abo_commerce_cart_item')
                        ->get('AboCommerceVoucherError');
                    $this->templateManager->assign('sVoucherError', [$errorMessage]);

                    return true;
                }
            }
        }
    }

    /**
     * Calculate a basket for one abo item
     *
     * @param array    $item
     * @param int|null $duration
     *
     * @return array
     */
    private function getBasketForAbo(array $item, $duration)
    {
        $originalSession = $this->dependencyProvider->getSession()->get('sessionId');

        // Create a new, mock basket
        // To do so, we need to inject a fake session into it, so we can later safely delete everything
        $session = clone $this->dependencyProvider->getSession();

        // create a new basket
        $tmpBasketClass = new \sBasket(null, null, null, null, $session);

        $tmpBasketClass->sSYSTEM = $this->dependencyProvider->getModules()->Basket()->sSYSTEM;

        // set a unique session id
        $tmpSessionId = uniqid('tmp', true);
        $session->offsetSet('sessionId', $tmpSessionId);
        $tmpBasketClass->sSYSTEM->sSESSION_ID = $tmpSessionId;

        // add the product to the basket
        $tmpBasketClass->sAddArticle($item['ordernumber'], $item['quantity']);

        // check if basket is net or gross
        $isNet = !$tmpBasketClass->sSYSTEM->sUSERGROUPDATA['tax'];

        $pricesForDeliveryCount = [];
        foreach ($item['aboCommerce']['prices'] as $price) {
            if ($duration === null || $duration >= $price['duration']) {
                $pricesForDeliveryCount[] = $price;
            }
        }

        $priceForQuantity = $pricesForDeliveryCount[0];
        foreach ($pricesForDeliveryCount as $price) {
            if ($item['quantity'] >= $price['fromQuantity']) {
                $priceForQuantity = $price;
            }
        }

        // add the discounts
        if ($priceForQuantity['discountAbsolute'] !== 0) {
            $aboDiscountPrice = $priceForQuantity['discountAbsolute'] * -1;

            $this->insertDiscount($item, $tmpSessionId, $aboDiscountPrice, $isNet);
        }

        // add abo products to tmp basket
        $sql = 'SELECT *
                FROM s_order_basket
                WHERE sessionID = :sessionId
                    AND articleID = :productId';
        $aboProductData = $this->connection->fetchAll(
            $sql,
            [
                'sessionId' => $item['sessionID'],
                'productId' => $item['id'],
            ]
        );

        if (!empty($aboProductData)) {
            foreach ($aboProductData as &$entry) {
                // unset the id
                unset($entry['id']);

                // change the session Id
                $entry['sessionID'] = $tmpSessionId;

                // and insert the data
                $this->connection->insert('s_order_basket', $entry);
            }
            unset($entry);
        }

        // init the admin with the original admin data and calculate the shipping costs
        $tmpAdmin = $this->dependencyProvider->getModules()->Admin();
        $tmpShippingCosts = $tmpAdmin->sGetPremiumShippingcosts();

        // get the basket
        $tmpBasket = $tmpBasketClass->sGetBasket();

        // add the discount if there is one
        $sql = 'SELECT *
                FROM s_order_basket
                WHERE sessionID = :sessionId
                    AND ordernumber = :orderNumber';
        $discount = $this->connection->fetchAssoc(
            $sql,
            [
                'sessionId' => $item['sessionID'],
                'orderNumber' => $this->config->get('discountnumber'),
            ]
        );

        if (!empty($discount)) {
            $tmpBasket['content'][] = $discount;
        }

        // calculate some values
        foreach ($tmpBasket['content'] as &$detail) {
            $detail['priceNumeric'] = (float) $detail['priceNumeric'];
        }
        unset($detail);

        // add the shipping costs to the temp basket
        $tmpBasket['sShippingcostsWithTax'] = $tmpShippingCosts['brutto'];
        $tmpBasket['sShippingcostsNet'] = $tmpShippingCosts['netto'];
        $tmpBasket['sShippingcosts'] = $isNet ? $tmpShippingCosts['netto'] : $tmpShippingCosts['brutto'];
        $tmpBasket['isNet'] = $isNet;

        // delete the temp basket from db
        $tmpBasketClass->sDeleteBasket();

        $this->dependencyProvider->getSession()->offsetSet('sessionId', $originalSession);

        // return the tmpBasket
        return $tmpBasket;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getDiscountQuery()
    {
        return $this->connection->createQueryBuilder()
            ->select('attribute.*')
            ->from('s_order_basket_attributes', 'attribute')
            ->where('attribute.basketID = :basketId')
            ->andWhere('attribute.swag_abo_commerce_id > 0');
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getAboQuery()
    {
        return $this->connection->createQueryBuilder()
            ->select(['attribute.*'])
            ->from('s_order_basket_attributes', 'attribute')
            ->where('attribute.basketID = :basketId')
            ->andWhere('attribute.swag_abo_commerce_delivery_interval > 0');
    }

    /**
     * @param array $aboAttributes
     *
     * @return array
     */
    private function prepareArrayKeysForView(array $aboAttributes)
    {
        foreach ($aboAttributes as $key => $value) {
            if (strpos($key, 'swag_abo_') === 0) {
                $words = explode('_', $key);
                foreach ($words as $index => $word) {
                    if ($index === 0) {
                        continue;
                    }

                    $words[$index] = ucfirst($word);
                }

                $aboAttributes[implode($words)] = $value;
                unset($aboAttributes[$key]);
            }
        }

        return $aboAttributes;
    }

    /**
     * @param array $attributes
     * @param array $aboBasket
     * @param array $item
     *
     * @return array
     */
    private function prepareAndCalculate(array $attributes, array $aboBasket, array $item)
    {
        $attributes = $this->prepareArrayKeysForView($attributes);
        $duration = $attributes['swagAboCommerceDuration'];
        $deliveryInterval = $attributes['swagAboCommerceDeliveryInterval'];

        $deliveryCount = $duration / $deliveryInterval + 1;
        $netAmount = $aboBasket['AmountNetNumeric'] + $aboBasket['sShippingcosts'];
        $grossAmount = $aboBasket['AmountNumeric'] + $aboBasket['sShippingcosts'];

        $pricePerShipping = $aboBasket['isNet'] ? $netAmount : $grossAmount;
        if (isset($item['custom_product_prices'])) {
            $pricePerShipping += $item['custom_product_prices']['surchargesTotal'];
        }

        $amount = $pricePerShipping * $deliveryCount;

        $item['abo_attributes']['swagAboCommerceId'] = $attributes['swagAboCommerceId'];
        $item['abo_attributes']['swagAboCommerceDeliveryInterval'] = $deliveryInterval;
        $item['abo_attributes']['swagAboCommerceDuration'] = $duration;
        $item['abo_attributes']['isAboArticle'] = true;
        $item['abo_attributes']['sShippingcostsWithTax'] = $aboBasket['sShippingcostsWithTax'];
        $item['abo_attributes']['deliveryCount'] = $deliveryCount;
        $item['abo_attributes']['amountPerDelivery'] = $pricePerShipping;
        $item['abo_attributes']['amount'] = $amount;

        return $item;
    }

    /**
     * @param array  $item
     * @param string $tmpSessionId
     * @param float  $aboDiscountPrice
     * @param bool   $isNet
     */
    private function insertDiscount(array $item, $tmpSessionId, $aboDiscountPrice, $isNet)
    {
        // add the abo discount to the temp basket therefor some data is needed
        $aboDiscountData = [
            'sessionID' => $tmpSessionId,
            'articlename' => $item['ordernumber'] . ' ABO_DISCOUNT',
            'articleID' => 0,
            'ordernumber' => $item['ordernumber'] . '.ABO',
            'quantity' => $item['quantity'],
            'price' => $aboDiscountPrice,
            'netprice' => $isNet ? $aboDiscountPrice : $aboDiscountPrice / ($item['tax_rate'] / 100 + 1),
            'tax_rate' => $item['tax_rate'],
            'datum' => $item['datum'],
            'modus' => 10,
            'esdarticle' => $item['esdarticle'],
            'currencyFactor' => $item['currencyFactor'],
        ];

        // insert the data
        $this->connection->insert('s_order_basket', $aboDiscountData);
    }
}
