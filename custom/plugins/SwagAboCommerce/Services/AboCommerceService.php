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

namespace SwagAboCommerce\Services;

use ArrayObject;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Order\Basket;
use Shopware\Models\Shop\Shop;
use SwagAboCommerce\Models\Price;
use SwagAboCommerce\Models\Product;

class AboCommerceService implements AboCommerceServiceInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var AboCommerceBasketServiceInterface
     */
    private $aboCommerceBasketService;

    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param ModelManager                      $modelManager
     * @param Connection                        $connection
     * @param AboCommerceBasketServiceInterface $aboCommerceBasketService
     * @param DependencyProviderInterface       $dependencyProvider
     */
    public function __construct(
        ModelManager $modelManager,
        Connection $connection,
        AboCommerceBasketServiceInterface $aboCommerceBasketService,
        DependencyProviderInterface $dependencyProvider
    ) {
        $this->modelManager = $modelManager;
        $this->connection = $connection;
        $this->aboCommerceBasketService = $aboCommerceBasketService;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getAboCommerceDataSelectedProduct(Detail $detail)
    {
        $productData = $this->modelManager->getRepository(Product::class)
            ->getActiveAboProductByProductIdQuery($detail->getArticle()->getId())
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        if (empty($productData)) {
            return [];
        }

        $prices = $this->aboCommerceBasketService->getVariantPrices($detail);

        $aboCommerceData = [
            'maxQuantityPerWeek' => $productData['maxUnitsPerWeek'],
            'isLimited' => $productData['limited'],
            'isExclusive' => $productData['exclusive'],
            'isActive' => $productData['active'],
            'prices' => [],
            'deliveryIntervalUnit' => $productData['deliveryIntervalUnit'],
            'minDeliveryInterval' => $productData['minDeliveryInterval'],
            'maxDeliveryInterval' => $productData['maxDeliveryInterval'],
            'durationUnit' => $productData['durationUnit'],
            'endlessSubscription' => $productData['endlessSubscription'],
            'periodOfNoticeInterval' => $productData['periodOfNoticeInterval'],
            'periodOfNoticeUnit' => $productData['periodOfNoticeUnit'],
            'directTermination' => $productData['directTermination'],
            'minDuration' => $productData['minDuration'],
            'maxDuration' => $productData['maxDuration'],
            'description' => $productData['description'],
        ];

        $tax = $detail->getArticle()->getTax();

        $discounts = $this->getDiscounts($productData['id']);

        foreach ($discounts as $discount) {
            foreach ($prices as $basePrice) {
                if (!$productData['endlessSubscription'] && $discount['durationFrom'] > $productData['maxDuration']) {
                    continue;
                }

                $discountAbsolute = 0;
                $discountPercent = 0;

                if (!$this->aboCommerceBasketService->displayNetPrices()) {
                    $discount['discountAbsolute'] = $discount['discountAbsolute'] / 100 * (100 + $tax->getTax());
                }

                if (!empty($discount['discountPercent'])) {
                    $discountPrice = $basePrice['gross'] / 100 * (100 - $discount['discountPercent']);
                    $discountAbsolute = $basePrice['gross'] / 100 * $discount['discountPercent'];
                    $discountPercent = $discount['discountPercent'];
                } elseif (!empty($discount['discountAbsolute'])) {
                    $discountPrice = $basePrice['gross'] - $discount['discountAbsolute'];
                    $discountAbsolute = $discount['discountAbsolute'];
                    $discountPercent = $discount['discountAbsolute'] * 100 / $basePrice['gross'];
                } else {
                    $discountPrice = $basePrice['gross'];
                }

                $aboCommerceData['prices'][] = [
                    'duration' => $discount['durationFrom'],
                    'discountPrice' => $discountPrice,
                    'discountAbsolute' => $discountAbsolute,
                    'discountPercentage' => $discountPercent,
                    'fromQuantity' => $basePrice['from'],
                    'toQuantity' => $basePrice['to'] ?: 'beliebig',
                ];
            }
        }

        return $aboCommerceData;
    }

    /**
     * {@inheritdoc}
     */
    public function insertDiscountForProducts(Detail $variant, array $aboProduct, array $basketItem)
    {
        $sessionId = $this->dependencyProvider->getSession()->get('sessionId');

        $aboDiscountBasketId = $this->connection->createQueryBuilder()
            ->select('orderBasket.id')
            ->from('s_order_basket', 'orderBasket')
            ->innerJoin(
                'orderBasket',
                's_order_basket_attributes',
                'orderBasketAttributes',
                'orderBasket.id = orderBasketAttributes.basketID'
            )
            ->andWhere('orderBasketAttributes.swag_abo_commerce_id = :aboId')
            ->andWhere('orderBasket.sessionID = :sessionId')
            ->setParameter('aboId', $basketItem['id'])
            ->setParameter('sessionId', $sessionId)
            ->execute()->fetchColumn();

        // if discount already exists, don't insert a new one
        if ($aboDiscountBasketId) {
            return;
        }

        $this->connection->createQueryBuilder()
            ->insert('s_order_basket')
            ->setValue('sessionID', ':sessionId')
            ->setValue('articlename', ':articlename')
            ->setValue('articleID', '0')
            ->setValue('ordernumber', ':ordernumber')
            ->setValue('shippingfree', '0')
            ->setValue('quantity', '1')
            ->setValue('price', 5 * -1)
            ->setValue('netprice', 5 * -1)
            ->setValue('datum', 'NOW()')
            ->setValue('modus', '10')
            ->setValue('tax_rate', '0')
            ->setValue('currencyFactor', '5')
            ->setParameter('sessionId', $sessionId)
            ->setParameter('articlename', $variant->getNumber() . ' ABO_DISCOUNT')
            ->setParameter('ordernumber', $aboProduct['ordernumber'])
            ->execute();

        $this->connection->createQueryBuilder()
            ->insert('s_order_basket_attributes')
            ->setValue('basketID', ':basketId')
            ->setValue('swag_abo_commerce_id', ':aboId')
            ->setParameter('basketId', $this->connection->lastInsertId('s_order_basket'))
            ->setParameter('aboId', $basketItem['id'])
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantByOrderNumber($orderNumber)
    {
        return $this->modelManager->createQueryBuilder()
            ->select(['variant', 'article'])
            ->from(Detail::class, 'variant')
            ->innerJoin('variant.article', 'article')
            ->where('variant.number = :orderNumber')
            ->andWhere('variant.active = 1')
            ->andWhere('article.active = 1')
            ->setParameter('orderNumber', $orderNumber)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
    }

    /**
     * {@inheritdoc}
     */
    public function updateBasketDiscount()
    {
        $discountBasketItems = $this->getAboDiscountBasketItems();

        if (empty($discountBasketItems)) {
            return;
        }

        /** @var Basket $discountBasketItem */
        foreach ($discountBasketItems as $discountBasketItem) {
            $this->updateBasketDiscountItem($discountBasketItem);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isAboCommerceProductInBasket()
    {
        $sessionId = $this->dependencyProvider->getSession()->get('sessionId');

        $builder = $this->modelManager->createQueryBuilder();
        $builder->select($this->modelManager->getExpressionBuilder()->count('basket.id'))
            ->from(Basket::class, 'basket')
            ->innerJoin('basket.attribute', 'attribute')
            ->where('basket.sessionId = :sessionId')
            ->andWhere('attribute.swagAboCommerceDeliveryInterval IS NOT NULL')
            ->setParameter('sessionId', $sessionId);

        $count = $builder->getQuery()->getSingleScalarResult();

        return (bool) $count;
    }

    /**
     * {@inheritdoc}
     */
    public function isAboCommerceConfigurationInBasket($orderNumber)
    {
        $sessionId = $this->dependencyProvider->getSession()->get('sessionId');

        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['basket, attribute'])
            ->from(Basket::class, 'basket')
            ->innerJoin('basket.attribute', 'attribute')
            ->where('basket.orderNumber LIKE :ordernumber')
            ->andWhere('basket.sessionId = :sessionId')
            ->andWhere('attribute.swagAboCommerceId IS NOT NULL')
            ->setFirstResult(0)->setMaxResults(1)
            ->setParameter('ordernumber', $orderNumber)
            ->setParameter('sessionId', $sessionId);

        return $builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * {@inheritdoc}
     */
    public function registerShop($shopId, $currency)
    {
        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = $this->modelManager->getRepository(Shop::class);
        $shop = $repository->getActiveById($shopId);
        foreach ($shop->getCurrencies() as $currencyModel) {
            if ($currency === $currencyModel->getCurrency()) {
                $shop->setCurrency($currencyModel);
                break;
            }
        }

        $shop->registerResources();

        $this->dependencyProvider->resetSession();
    }

    /**
     * {@inheritdoc}
     */
    public function registerOrder(array $aboOrder, array $order)
    {
        $orderId = $order['id'];

        if (isset($order['orderId'])) {
            $orderId = $order['orderId'];
        }

        $userData = $this->getUserData($orderId, $aboOrder);
        $session = $this->dependencyProvider->getSession();

        // Fake user details so the sUserId won't get reset
        $session->offsetSet('sUserMail', $userData['additional']['user']['email']);
        $session->offsetSet('sUserPassword', $userData['additional']['user']['password']);
        $session->offsetSet('sUserId', $order['userID']);
        $session->offsetSet('sUserGroup', $userData['additional']['user']['customergroup']);

        $dispatch = $this->dependencyProvider->getModules()->Admin()->sGetPremiumDispatch($order['dispatchID']);
        $userData['additional']['user']['paymentID'] = $userData['additional']['payment']['id'];

        $vars = new ArrayObject(
            [
                'sUserData' => $userData,
                'sBasket' => $this->getBasketData($aboOrder, $order),
                'sDispatch' => $dispatch,
            ],
            ArrayObject::ARRAY_AS_PROPS
        );

        $vars->sAmount = $vars->sBasket['sAmount'];
        $vars->sAmountWithTax = $vars->sBasket['AmountWithTaxNumeric'];
        $vars->sAmountNet = $vars->sBasket['AmountNetNumeric'];
        $vars->sShippingcosts = $vars->sBasket['sShippingcosts'];
        $vars->sShippingcostsNumeric = $vars->sBasket['sShippingcostsWithTax'];
        $vars->sShippingcostsNumericNet = $vars->sBasket['sShippingcostsNet'];
        $vars->sInternalComment = $order['internalcomment'];

        $session->offsetSet('sOrderVariables', $vars);
        $session->offsetSet('sDispatch', $dispatch['id']);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserData($orderId, array $aboOrder = null)
    {
        $order = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('s_order')
            ->where('id = :orderId')
            ->setParameter('orderId', $orderId)
            ->execute()
            ->fetch();

        $user = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('s_user')
            ->where('id = :userId')
            ->setParameter('userId', $order['userID'])
            ->execute()
            ->fetch();

        if (is_array($aboOrder)) {
            $payment = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('s_core_paymentmeans')
                ->where('id = :paymentId')
                ->setParameter('paymentId', $aboOrder['payment_id'])
                ->execute()
                ->fetch();

            $billingAddress = $this->connection->createQueryBuilder()
                ->select('*, user_id as userID, country_id as countryID, state_id as stateID')
                ->from('s_user_addresses')
                ->where('id = :billingAddressId')
                ->setParameter('billingAddressId', $aboOrder['billing_address_id'])
                ->execute()
                ->fetch();

            $shippingAddress = $this->connection->createQueryBuilder()
                ->select('*, user_id as userID, country_id as countryID, state_id as stateID')
                ->from('s_user_addresses')
                ->where('id = :shippingAddressId')
                ->setParameter('shippingAddressId', $aboOrder['shipping_address_id'])
                ->execute()
                ->fetch();

            $shippingCountry = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('s_core_countries')
                ->where('id = :countryId')
                ->setParameter('countryId', $shippingAddress['countryID'])
                ->execute()
                ->fetch();

            $shippingState = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('s_core_countries_states')
                ->where('id = :stateId')
                ->setParameter('stateId', $shippingAddress['stateID'])
                ->execute()
                ->fetch();
        }

        return [
            'billingaddress' => isset($billingAddress) ? $billingAddress : null,
            'shippingaddress' => isset($shippingAddress) ? $shippingAddress : null,
            'additional' => [
                'user' => $user,
                'payment' => $payment,
                'countryShipping' => isset($shippingCountry) ? $shippingCountry : null,
                'stateShipping' => isset($shippingState) ? $shippingState : null,
                'charge_vat' => empty($order['taxfree']),
                'show_net' => !empty($order['net']),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setAboCommerceFlagForProducts(array $products = [])
    {
        if (empty($products)) {
            return null;
        }

        $productIds = [];
        foreach ($products as $item) {
            $productIds[] = $item['articleID'];
        }

        $aboProducts = $this->modelManager->getRepository(Product::class)
            ->getActiveAboProductByProductIdQuery($productIds)
            ->getArrayResult();

        foreach ($aboProducts as $aboProduct) {
            foreach ($products as &$product) {
                if ($product['articleID'] === $aboProduct['articleId']) {
                    $product['aboCommerce'] = true;
                    $product['aboCommerceExclusive'] = !empty($aboProduct['exclusive']);
                }
            }
        }

        return $products;
    }

    /**
     * {@inheritdoc}
     */
    public function orderExists($orderId)
    {
        if (!$orderId) {
            return false;
        }

        $result = $this->connection->createQueryBuilder()
            ->select('ordernumber')
            ->from('s_order')
            ->where('id = :orderId')
            ->setParameter('orderId', $orderId)
            ->execute()
            ->fetch();

        return $result !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function isAboOrder($orderId)
    {
        // Checks for the initial and the last order
        $result = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('s_plugin_swag_abo_commerce_orders')
            ->where('last_order_id = :orderId')
            ->orWhere('order_id = :orderId')
            ->setParameter('orderId', $orderId)
            ->execute()
            ->fetchColumn();

        if ($result) {
            return true;
        }

        // Checks for orders between last and first order
        $result = $this->connection->createQueryBuilder()
            ->select('swag_abo_commerce_id')
            ->from('s_order_attributes')
            ->where('orderID = :orderId')
            ->setParameter('orderId', $orderId)
            ->execute()
            ->fetchColumn();

        return !empty($result);
    }

    /**
     * {@inheritdoc}
     */
    public function isInitialOrder($orderId)
    {
        $sql = 'SELECT delivered, order_id FROM s_plugin_swag_abo_commerce_orders WHERE order_id=?';

        $result = $this->connection->fetchAssoc($sql, [$orderId]);

        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsAboExclusive($productId)
    {
        return $this->connection->createQueryBuilder()
            ->select('exclusive')
            ->from('s_plugin_swag_abo_commerce_articles')
            ->where('article_id = :productId')
            ->andWhere('exclusive = 1')
            ->setParameter('productId', $productId)
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Returns array of all discount items in the basket
     *
     * @return array
     */
    private function getAboDiscountBasketItems()
    {
        $sessionId = $this->dependencyProvider->getSession()->get('sessionId');

        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['basket', 'attribute'])
            ->from(Basket::class, 'basket')
            ->innerJoin('basket.attribute', 'attribute')
            ->where('basket.mode = 10')
            ->andWhere('basket.sessionId = :sessionId')
            ->andWhere('attribute.swagAboCommerceId IS NOT NULL')
            ->setParameter('sessionId', $sessionId);

        return $builder->getQuery()->getResult();
    }

    /**
     * Returns array of all discounts determined by the abo id.
     *
     * @param int $aboId
     *
     * @return array
     */
    private function getDiscounts($aboId)
    {
        $customerGroupId = $this->aboCommerceBasketService->getCurrentCustomerGroup()->getId();

        if (!$customerGroupId) {
            // get the default shop customer group
            $customerGroupId = $this->dependencyProvider->getShop()->getCustomerGroup()->getId();
        }

        $discount = $this->getDiscountsForCustomerGroup($aboId, $customerGroupId);

        // if discount empty set the default
        if (empty($discount)) {
            $discount = [
                'customer_group_id' => $customerGroupId,
                'duration_from' => 1,
                'discount_absolute' => 0,
                'discount_percent' => 0,
            ];
        }

        return $discount;
    }

    /**
     * Returns array of all discounts for the according customer group and abo id.
     *
     * @param int      $aboId
     * @param int      $customerGroupId
     * @param int|null $fallbackId
     *
     * @return array
     */
    private function getDiscountsForCustomerGroup($aboId, $customerGroupId)
    {
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select('price')
            ->from(Price::class, 'price')
            ->where('price.aboArticleId = :aboId')
            ->andWhere('price.customerGroupId = :customerGroupId')
            ->addOrderBy('price.durationFrom')
            ->setParameter('aboId', $aboId)
            ->setParameter('customerGroupId', $customerGroupId);

        return $builder->getQuery()->getArrayResult();
    }

    /**
     * Checks if basket contains discounts and updates the basket price.
     *
     * @param Basket $discountBasketItem
     */
    private function updateBasketDiscountItem(Basket $discountBasketItem)
    {
        $aboBasketItemId = $discountBasketItem->getAttribute()->getSwagAboCommerceId();

        /** @var Basket $aboBasketItem */
        $aboBasketItem = $this->aboCommerceBasketService->getItem($aboBasketItemId, AbstractQuery::HYDRATE_OBJECT);

        if ($aboBasketItem === null) {
            $this->aboCommerceBasketService->removeItem($discountBasketItem);

            return;
        }

        $duration = (int) $aboBasketItem->getAttribute()->getSwagAboCommerceDuration();
        $quantity = $aboBasketItem->getQuantity();
        $netBasePrice = $aboBasketItem->getNetPrice();

        $productData = $this->modelManager->getRepository(Product::class)
            ->getActiveAboProductByProductIdQuery($aboBasketItem->getArticleId())
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        $discounts = $this->getDiscounts($productData['id']);

        // get matching discount
        $activeDiscount = null;
        $endlessSubscription = $productData['endlessSubscription'];
        foreach ($discounts as $discount) {
            if ((int) $discount['durationFrom'] <= $duration || ($endlessSubscription && (int) $discount['durationFrom'] === 1)) {
                $activeDiscount = $discount;
            }
        }

        // no discount found? Remove discount!
        if ($activeDiscount === null) {
            $this->aboCommerceBasketService->removeItem($discountBasketItem);

            return;
        }

        $taxRate = $this->getTaxRate($aboBasketItemId);
        $currencyFactor = $aboBasketItem->getCurrencyFactor();

        $discountAbsoluteNet = 0;
        $discountAbsoluteGross = 0;
        if (!empty($activeDiscount['discountAbsolute'])) {
            $discountAbsoluteNet = $activeDiscount['discountAbsolute'] * -1 * $quantity;
            $discountAbsoluteGross = $discountAbsoluteNet / 100 * (100 + $taxRate);
        } elseif (!empty($activeDiscount['discountPercent'])) {
            $discountAbsoluteNet = $netBasePrice / 100 * $activeDiscount['discountPercent'] * -1 * $quantity;
            $discountAbsoluteGross = $discountAbsoluteNet / 100 * (100 + $taxRate);
        }

        $discountBasketItem->setCurrencyFactor($currencyFactor);
        $discountBasketItem->setTaxRate($taxRate);

        if ($this->aboCommerceBasketService->useNetPriceInBasket()) {
            $discountBasketItem->setPrice($discountAbsoluteNet);
        } else {
            $discountBasketItem->setPrice($discountAbsoluteGross);
        }

        $discountBasketItem->setNetPrice($discountAbsoluteNet);

        // Remove discount row is no discount is given
        if ($discountAbsoluteNet === 0) {
            $this->modelManager->remove($discountBasketItem);
        }

        $this->modelManager->flush();
    }

    /**
     * Returns the QueryBuilder object which selects the order details for the subscription
     *
     * @param int  $aboProductOrderId
     * @param int  $aboDiscountOrderId
     * @param bool $isCustomProductsActive
     *
     * @return QueryBuilder
     */
    private function getOrderQueryBuilder($aboProductOrderId, $aboDiscountOrderId, $isCustomProductsActive = false)
    {
        $orderDetailQueryBuilder = $this->connection->createQueryBuilder();
        $orderDetailQueryBuilder->select([
            'orderDetails.id',
            'orderDetails.articleID',
            'orderDetails.articleordernumber AS ordernumber',
            'orderDetails.name AS articlename',
            'orderDetails.price',
            'orderDetails.quantity',
            'orderDetails.modus',
            'orderDetails.esdarticle',
            'orderDetails.taxID',
            'orderDetails.tax_rate',
        ])
            ->from('s_order_details', 'orderDetails')
            ->where('orderDetails.id IN (:aboProductOrderId, :aboDiscountOrderId)')
            ->setParameter('aboProductOrderId', $aboProductOrderId)
            ->setParameter('aboDiscountOrderId', $aboDiscountOrderId);

        if (!$isCustomProductsActive) {
            return $orderDetailQueryBuilder;
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $customProductsHash = $queryBuilder->select('swag_custom_products_configuration_hash')
            ->from('s_order_details_attributes', 'orderDetailsAttributes')
            ->innerJoin(
                'orderDetailsAttributes',
                's_order_details',
                'orderDetails',
                'orderDetails.id = orderDetailsAttributes.detailID'
            )
            ->where('detailID = :aboProductOrderId')
            ->setParameter('aboProductOrderId', $aboProductOrderId)
            ->execute()->fetchColumn();

        if ($customProductsHash === null) {
            return $orderDetailQueryBuilder;
        }

        $queryBuilder->resetQueryParts();
        $orderIdSelectQuery = $queryBuilder->select('orderID')
            ->from('s_order_details')
            ->where('id = :aboProductOrderId')
            ->getSQL();

        $orderDetailQueryBuilder
            ->addSelect('swag_custom_products_configuration_hash AS customProductsHash')
            ->leftJoin(
                'orderDetails',
                's_order_details_attributes',
                'orderDetailsAttributes',
                'orderDetails.id = orderDetailsAttributes.detailID'
            )
            ->orWhere('orderDetailsAttributes.swag_custom_products_configuration_hash = :customProductsHash')
            ->andWhere('orderID = (' . $orderIdSelectQuery . ')')
            ->setParameter('customProductsHash', $customProductsHash);

        return $orderDetailQueryBuilder;
    }

    /**
     * Returns array of calculated basket prices and other details.
     *
     * @param array $abo
     * @param array $order
     *
     * @return array
     */
    private function getBasketData(array $abo, array $order)
    {
        $isCustomProductsActive = $this->checkForCustomProducts();
        $queryBuilder = $this->getOrderQueryBuilder(
            (int) $abo['article_order_detail_id'],
            (int) $abo['discount_order_detail_id'],
            $isCustomProductsActive
        );
        $orderDetails = $queryBuilder->execute()->fetchAll();

        $basketModule = $this->dependencyProvider->getModules()->Basket();
        $admin = $this->dependencyProvider->getModules()->Admin();
        $session = $this->dependencyProvider->getSession();
        // Create a dummy request. This is required if the console executes this method.
        // If we don't do this, the sBasket-class will not be able to access front->Request()
        $this->dependencyProvider->setFrontRequest();

        // set some data
        $basketModule->sSYSTEM->sBotSession = $session->get('Bot');
        $basketModule->sSYSTEM->sSESSION_ID = $session->get('sessionId');
        $admin->sSYSTEM->_SESSION['sUserId'] = $order['userID'];
        $admin->sSYSTEM->_SESSION['sDispatch'] = $order['dispatchID'];

        // delete the basket if there is one to have a clean start
        $basketModule->sDeleteBasket();

        $isNet = !empty($order['net']);

        // if net order set additional data
        if ($isNet) {
            $basketModule->sSYSTEM->sUSERGROUPDATA['tax'] = false;
        }

        $aboCommerceSettings = $this->modelManager->getRepository(Product::class)
            ->getAboCommerceSettingsQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
        $useActualProductPrice = $aboCommerceSettings['useActualProductPrice'];

        // add the product
        foreach ($orderDetails as $detail) {
            // the abo product
            if ((int) $detail['modus'] === 0) {
                // add the product to the basket
                if ($useActualProductPrice) {
                    if ($isCustomProductsActive && isset($detail['customProductsHash'])) {
                        $this->dependencyProvider->getFrontRequest()->setParam('customProductsHash', $detail['customProductsHash']);
                    }
                    $productBasketId = $basketModule->sAddArticle($detail['ordernumber'], $detail['quantity']);
                } else {
                    $productBasketId = $this->aboCommerceBasketService->addProductOnRecurringOrder(
                        (int) $detail['id'],
                        $detail['ordernumber'],
                        $detail['modus']
                    );
                }

                $session->offsetSet('aboIsRecurringOrderProductBasketId', $productBasketId);

                // get the AboCommerce data for the product
                $selectedVariant = $this->modelManager->getRepository(Detail::class)
                    ->findOneBy(['number' => $detail['ordernumber']]);
                if ($selectedVariant) {
                    $aboCommerceData = $this->getAboCommerceDataSelectedProduct($selectedVariant);
                }
                $quantity = $detail['quantity'];
                continue;
            }

            // the abo discount
            if ((int) $detail['modus'] === 10) {
                if (!$useActualProductPrice) {
                    $this->aboCommerceBasketService->addProductOnRecurringOrder(
                        (int) $detail['id'],
                        $detail['ordernumber'],
                        $detail['modus']
                    );
                } else {
                    $aboDiscountPrice = 0;
                    $pricesForQuantity = [];
                    foreach ($aboCommerceData['prices'] as $price) {
                        if ($quantity >= $price['fromQuantity']) {
                            $pricesForQuantity[] = $price;
                        }
                    }
                    $aboDiscountArrayMaxKey = count($pricesForQuantity) - 1;

                    // have no scale of prices
                    if ($aboDiscountArrayMaxKey === 0
                        && $pricesForQuantity[0]['discountAbsolute'] !== 0
                        && !empty($quantity)
                    ) {
                        $aboDiscountPrice = $pricesForQuantity[0]['discountAbsolute'] * -1;
                    } else {
                        // have scale of prices
                        foreach ($pricesForQuantity as $key => $price) {
                            if ($key === $aboDiscountArrayMaxKey) {
                                $durationTo = $price['duration'];
                            } else {
                                $durationTo = $pricesForQuantity[$key + 1]['duration'] - 1;
                            }

                            if ($abo['duration'] > $durationTo) {
                                continue;
                            }

                            if ($abo['duration'] <= $durationTo) {
                                if ($pricesForQuantity[$key]['discountAbsolute'] !== 0 && !empty($quantity)) {
                                    $aboDiscountPrice = $pricesForQuantity[$key]['discountAbsolute'] * -1;
                                }
                                break;
                            }
                        }

                        if (empty($aboDiscountPrice)
                            && $pricesForQuantity[$aboDiscountArrayMaxKey]['discountAbsolute'] !== 0
                            && !empty($quantity)
                        ) {
                            $aboDiscountPrice = $pricesForQuantity[$aboDiscountArrayMaxKey]['discountAbsolute'] * -1;
                        }
                    }

                    if (!empty($aboDiscountPrice)) {
                        $this->connection->createQueryBuilder()
                            ->insert('s_order_basket')
                            ->setValue('sessionID', ':sessionID')
                            ->setValue('articlename', ':articlename')
                            ->setValue('articleID', ':articleID')
                            ->setValue('ordernumber', ':ordernumber')
                            ->setValue('quantity', ':quantity')
                            ->setValue('price', ':price')
                            ->setValue('netprice', ':netprice')
                            ->setValue('tax_rate', ':tax_rate')
                            ->setValue('datum', ':datum')
                            ->setValue('modus', ':modus')
                            ->setValue('esdarticle', ':esdarticle')
                            ->setValue('currencyFactor', ':currencyFactor')
                            ->setParameter('sessionID', $session->get('sessionId'))
                            ->setParameter('articlename', $detail['articlename'])
                            ->setParameter('articleID', $detail['articleID'])
                            ->setParameter('ordernumber', $detail['ordernumber'])
                            ->setParameter('quantity', $quantity)
                            ->setParameter('price', $aboDiscountPrice)
                            ->setParameter('netprice', empty($order['net']) ? $aboDiscountPrice : $aboDiscountPrice / ($detail['tax_rate'] / 100 + 1))
                            ->setParameter('tax_rate', $detail['tax_rate'])
                            ->setParameter('datum', date('Y-mm-dd H:i:s'))
                            ->setParameter('modus', $detail['modus'])
                            ->setParameter('esdarticle', $detail['esdarticle'])
                            ->setParameter('currencyFactor', $this->dependencyProvider->getShop()->getCurrency()->getId())
                            ->execute();
                    }
                }

                continue;
            }

            // if Custom Products is active, the additional Custom Products basket positions have to be added
            if (!$useActualProductPrice && $isCustomProductsActive && (int) $detail['modus'] === 4) {
                $this->aboCommerceBasketService->addProductOnRecurringOrder(
                    (int) $detail['id'],
                    $detail['ordernumber'],
                    $detail['modus']
                );
            }
        }

        // to make sure that nothing is missing init the basket
        $basketModule->sGetBasket();

        // calculate the shipping costs
        $shippingCosts = $this->dependencyProvider->getModules()->Admin()->sGetPremiumShippingcosts();

        // get the basket after the calculation
        $basketData = $basketModule->sGetBasket();

        if (!empty($shippingCosts['brutto'])) {
            $basketData['AmountNetNumeric'] += $shippingCosts['netto'];
            $basketData['AmountNumeric'] += $shippingCosts['brutto'];
            $basketData['sShippingcostsDifference'] = $shippingCosts['difference']['float'];
        }

        if (!empty($basketData['AmountWithTaxNumeric'])) {
            $basketData['AmountWithTaxNumeric'] += $shippingCosts['brutto'];
        }

        return [
            'content' => $basketData['content'],
            'sAmount' => round($basketData['AmountNetNumeric'], 2),
            'AmountNumeric' => $basketData['AmountNumeric'],
            'AmountWithTaxNumeric' => $basketData['AmountWithTaxNumeric'],
            'AmountNetNumeric' => round($basketData['AmountNetNumeric'], 2),
            'sShippingcostsWithTax' => $shippingCosts['brutto'],
            'sShippingcostsNet' => $shippingCosts['netto'],
            'sShippingcosts' => $isNet ? $shippingCosts['netto'] : $shippingCosts['brutto'],
        ];
    }

    /**
     * @param int $aboBasketItemId
     *
     * @return bool|string
     */
    private function getTaxRate($aboBasketItemId)
    {
        return $this->connection->createQueryBuilder()->select('tax_rate')
            ->from('s_order_basket')
            ->where('id = :id')
            ->setParameter('id', $aboBasketItemId)
            ->execute()->fetchColumn();
    }

    /**
     * @return bool
     */
    private function checkForCustomProducts()
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        return (bool) $queryBuilder->select('active')
            ->from('s_core_plugins')
            ->where('name = :customProductsPluginName')
            ->setParameter('customProductsPluginName', 'SwagCustomProducts')
            ->execute()
            ->fetchColumn();
    }
}
