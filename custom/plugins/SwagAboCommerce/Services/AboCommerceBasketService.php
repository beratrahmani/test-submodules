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

use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Enlight_Components_Snippet_Namespace;
use Enlight_Event_EventManager as EventManager;
use Enlight_Template_Manager as TemplateManager;
use Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Product;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Esd;
use Shopware\Models\Article\Price;
use Shopware\Models\Attribute\OrderBasket;
use Shopware\Models\Country\Country;
use Shopware\Models\Customer\Group;
use Shopware\Models\Order\Basket;
use Shopware\Models\Order\Detail as OrderDetails;
use Shopware_Components_Snippet_Manager as SnippetManager;

class AboCommerceBasketService implements AboCommerceBasketServiceInterface
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
     * @var Connection
     */
    private $connection;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var SnippetManager
     */
    private $snippetManager;

    /**
     * @var TemplateManager
     */
    private $templateManager;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var AdditionalTextServiceInterface
     */
    private $additionalTextService;

    /**
     * @param DependencyProviderInterface    $dependencyProvider
     * @param ModelManager                   $modelManager
     * @param Connection                     $connection
     * @param EventManager                   $eventManager
     * @param SnippetManager                 $snippetManager
     * @param TemplateManager                $templateManager
     * @param ContextServiceInterface        $contextService
     * @param AdditionalTextServiceInterface $additionalTextService
     */
    public function __construct(
        DependencyProviderInterface $dependencyProvider,
        ModelManager $modelManager,
        Connection $connection,
        EventManager $eventManager,
        SnippetManager $snippetManager,
        TemplateManager $templateManager,
        ContextServiceInterface $contextService,
        AdditionalTextServiceInterface $additionalTextService
    ) {
        $this->dependencyProvider = $dependencyProvider;
        $this->modelManager = $modelManager;
        $this->connection = $connection;
        $this->eventManager = $eventManager;
        $this->snippetManager = $snippetManager;
        $this->templateManager = $templateManager;
        $this->contextService = $contextService;
        $this->additionalTextService = $additionalTextService;
    }

    /**
     * Getter function of the newBasketItem property.
     * This property is only used for php unit tests.
     *
     * @return Basket
     */
    public function getNewBasketItem()
    {
        return new Basket();
    }

    /**
     * {@inheritdoc}
     */
    public function addProduct($orderNumber, $quantity = 1, array $parameter = [])
    {
        //make sure that the used quantity is an integer value.
        $quantity = (empty($quantity) || !is_numeric($quantity)) ? 1 : (int) $quantity;

        //first we have to get the \Shopware\Models\Article\Detail model for the passed order number
        $variant = $this->getVariantByOrderNumber($orderNumber);

        //if no \Shopware\Models\Article\Detail found return an failure result
        if (!$variant instanceof Detail) {
            return $this->getNoValidOrderNumberFailure();
        }

        //validate the order number and quantity.
        $validation = $this->validateProduct($variant, $quantity, $parameter);

        //not allowed to add the product?
        if ($validation['success'] === false) {
            return $validation;
        }

        //the shouldAddAsNewPosition is a helper function to validate if the passed variant has to be created
        //as new basket position.
        $id = $this->shouldAddAsNewPosition($variant, $parameter);

        $isCustomProduct = false;
        if (isset($validation['isCustomProduct']) && $validation['isCustomProduct'] === true) {
            $id = $validation['lastInsertedCustomProductBasketId'];
            $isCustomProduct = true;
        }

        $data = $this->getVariantCreateData($variant, $quantity, $parameter);

        if ($id === true) {
            //if the shouldAddAsNewPosition function returns true, the variant will be added as new position
            $id = $this->createItem($data);
        } else {
            //in the other case, the shouldAddAsNewPosition returns the id of the basket position which has to be updated

            if ($isCustomProduct) {
                // if custom products is active, the product is already put in the basket
                // so the `getSummarizedQuantityOfVariant` already considers the right quantity
                $quantity = 0;
            }

            $quantity += $this->getSummarizedQuantityOfVariant($variant, $id);
            $data['quantity'] = $quantity;

            $this->updateItem(
                $id,
                $data
            );
        }

        //we have to execute the sUpdateArticle function to update the basket prices.
        $this->dependencyProvider->getModules()->Basket()->sUpdateArticle($id, $quantity);

        return $this->getItem($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($id, $hydrationMode = AbstractQuery::HYDRATE_ARRAY)
    {
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['basket', 'attribute'])
            ->from(Basket::class, 'basket')
            ->leftJoin('basket.attribute', 'attribute')
            ->where('basket.id = :id')
            ->setParameter('id', $id);

        return $builder->getQuery()->getOneOrNullResult($hydrationMode);
    }

    /**
     * {@inheritdoc}
     */
    public function removeItem($item)
    {
        if (!$item instanceof Basket) {
            $item = $this->getItem($item, AbstractQuery::HYDRATE_OBJECT);
        }

        $this->modelManager->remove($item);
        $this->modelManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantPrices($variant)
    {
        $prices = $this->getPricesForCustomerGroup(
            $variant,
            $this->getCurrentCustomerGroup()->getKey(),
            $this->getCurrentCustomerGroup()->getId(),
            $this->dependencyProvider->getShop()->getCustomerGroup()->getKey(),
            $this->dependencyProvider->getShop()->getCustomerGroup()->getId()
        );

        if ($prices === null) {
            return false;
        }

        return $this->getNetAndGrossPriceForVariantPrice($prices, $variant);
    }

    /**
     * {@inheritdoc}
     */
    public function getNetAndGrossPriceForVariantPrice(array $prices, $variant)
    {
        $tax = 1 * $variant->getArticle()->getTax()->getTax();

        $calculatedPrices = [];
        if ($variant->getArticle()->getPriceGroupActive()) {
            foreach ($prices as &$groupPrice) {
                $groupPrice['price'] = str_replace(',', '.', $groupPrice['price']);
                $groupPrice['net'] = $groupPrice['price'] / ((100 + $tax) / 100);
                $groupPrice['gross'] = $groupPrice['price'];
                unset($groupPrice['price']);
            }
            unset($groupPrice);
            $calculatedPrices = $prices;
        } else {
            foreach ($prices as $price) {
                $gross = $this->dependencyProvider->getModules()->Articles()->sCalculatingPrice(
                    $price['price'],
                    $tax,
                    $variant->getArticle()->getTax()->getId()
                );

                $gross = str_replace(',', '.', $gross);

                $calculatedPrices[] = [
                    'gross' => 1 * $gross,
                    'net' => $price['price'],
                    'from' => $price['from'],
                    'to' => $price['to'],
                ];
            }
        }

        return $calculatedPrices;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentCustomerGroup()
    {
        $customerGroupId = $this->contextService->getShopContext()->getCurrentCustomerGroup()->getId();
        $customerGroup = null;

        $customerGroupRepository = $this->modelManager->getRepository(Group::class);

        /* @var Group $customerGroup */
        // check if the customer logged in and get the customer group model for the logged in customer
        if (!empty($customerGroupId)) {
            $customerGroup = $customerGroupRepository->find($customerGroupId);
        }

        $customerGroupKey = $this->dependencyProvider->getSession()->get('sUserGroup');
        if (!empty($customerGroupKey)) {
            $customerGroup = $customerGroupRepository->findOneBy(['key' => $customerGroupKey]);
        }

        // if no customer group given, get the default customer group.
        if (!$customerGroup instanceof Group) {
            $customerGroup = $this->dependencyProvider->getShop()->getCustomerGroup();
        }

        return $customerGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function addProductOnRecurringOrder($orderDetailId, $productOrderNumber, $modus)
    {
        $orderData = $this->getOrderData($orderDetailId);
        $isShippingFree = $this->isShippingFree($productOrderNumber);

        $session = $this->dependencyProvider->getSession();
        $sessionId = $session->get('sessionId');
        $userId = $session->get('sUserId', 0);
        $partner = $session->get('sPartner', 0);

        $orderAttributes = new OrderBasket();
        $orderAttributes->fromArray($orderData['attribute']);

        $basketData = [
            'sessionId' => $sessionId,
            'customerId' => $userId,
            'articleName' => $orderData['articleName'],
            'articleId' => $orderData['articleId'],
            'orderNumber' => $orderData['articleNumber'],
            'shippingFree' => $isShippingFree,
            'quantity' => $orderData['quantity'],
            'price' => $orderData['price'],
            'netPrice' => $this->useNetPriceInBasket() ? $orderData['price'] : $this->getNetPrice($orderData),
            'taxRate' => $orderData['taxRate'],
            'date' => 'now',
            'mode' => $modus,
            'esdArticle' => $orderData['esdArticle'],
            'partnerId' => $partner,
            'attribute' => $orderAttributes,
        ];

        return $this->createItem($basketData);
    }

    /**
     * {@inheritdoc}
     */
    public function displayNetPrices()
    {
        return $this->isCustomerGroupNet();
    }

    /**
     * {@inheritdoc}
     */
    public function useNetPriceInBasket()
    {
        return !(!$this->isCustomerGroupNet() && !$this->isShippingCountryNet());
    }

    /**
     * Helper function to get the variant data for the new
     * basket position. To add additional information you can hook
     * this function and change the return value by an after hook.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return array
     */
    private function getVariantCreateData(Detail $variant, $quantity, array $parameter = [])
    {
        $prices = $this->getVariantPrices($variant);
        $price = $prices[0];

        foreach ($prices as $tempPrice) {
            if ($quantity >= $tempPrice['from']) {
                $price = $tempPrice;
            }
        }

        $session = $this->dependencyProvider->getSession();
        $sessionId = $session->get('sessionId');
        $userId = $session->get('sUserId', 0);
        $partner = $session->get('sPartner', 0);

        $product = [
            'sessionId' => $sessionId,
            'customerId' => $userId,
            'articleName' => $variant->getArticle()->getName(),
            'articleId' => $variant->getArticle()->getId(),
            'variantId' => $variant->getId(),
            'orderNumber' => $variant->getNumber(),
            'shippingFree' => $variant->getShippingFree(),
            'quantity' => $quantity,
            'price' => $price['gross'],
            'netPrice' => $price['net'],
            'taxRate' => $variant->getArticle()->getTax()->getTax(),
            'date' => 'now',
            'mode' => 0,
            'esdArticle' => $this->getEsdFlag($variant),
            'partnerId' => $partner,
            'attribute' => $parameter,
        ];

        /** add translations and additional texts like in the core @see \sBasket::getArticleForAddArticle */
        $product = $this->dependencyProvider->getModules()->Articles()->sGetTranslation(
            $product,
            $product['articleId'],
            'article'
        );

        $product = $this->dependencyProvider->getModules()->Articles()->sGetTranslation(
            $product,
            $product['variantId'],
            'variant'
        );

        if (count($variant->getConfiguratorOptions()) !== 0) {
            $context = $this->contextService->getShopContext();
            $productStruct = new Product($product['articleId'], $product['variantId'], $product['orderNumber']);
            $productStruct = $this->additionalTextService->buildAdditionalText($productStruct, $context);
            $product['articleName'] = $product['articleName'] . ' ' . $productStruct->getAdditional();
        }

        return $product;
    }

    /**
     * Helper function to update an existing basket item.
     * The function expects an array with basket data.
     * All parameters of the addArticle function are also available here.
     *
     * @param int   $id
     * @param array $data
     */
    private function updateItem($id, array $data)
    {
        $this->modelManager->clear();

        $basket = $this->modelManager->find(Basket::class, $id);
        if (!$basket instanceof Basket) {
            $basket = $this->getNewBasketItem();
        }
        if (empty($data)) {
            return;
        }

        $basket->fromArray($data);

        $this->modelManager->persist($basket);
        $this->modelManager->flush();
    }

    /**
     * Helper function to select all prices for the passed variant and the passed
     * customer group. If the result set of the query is empty, the function
     * resume the query with the passed fallback customer group key.
     * To control the result data type you can use the $hydrationMode parameter.
     * The default is set to "\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY".
     * Set the parameter to "\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT" to get the result
     * set as \Shopware\Models\Article\Price instances.
     *
     * @param Detail $variant
     * @param string $customerGroupKey Contains the group key for the customer group
     * @param int    $customerGroupId
     * @param string $fallbackKey      Contains an fallback group key for the customer group
     * @param int    $fallbackId
     *
     * @return array
     */
    private function getPricesForCustomerGroup(
        Detail $variant,
        $customerGroupKey,
        $customerGroupId,
        $fallbackKey,
        $fallbackId
    ) {
        // If the product has an active price group, another path has to be taken to get the right prices data
        if ($variant->getArticle()->getPriceGroupActive()) {
            $priceGroupId = $variant->getArticle()->getPriceGroup()->getId();
            $sumDiscounts = $this->connection->createQueryBuilder()
                ->select('COUNT(id)')
                ->from('s_core_pricegroups_discounts')
                ->where('groupID = :groupId')
                ->andWhere('customergroupID = :customerGroupId')
                ->setParameter('groupId', $priceGroupId)
                ->setParameter('customerGroupId', $customerGroupId)
                ->execute()
                ->fetchColumn();

            $price = $variant->getPrices();
            /* @var \Shopware\Models\Article\Price $price */
            $price = $price[0];
            $productData = [];
            $productData['tax'] = $variant->getArticle()->getTax()->getTax();
            $productData['taxID'] = $variant->getArticle()->getTax()->getId();

            // If there is more than one discount per price group, a matrix calculation could be done
            if ($sumDiscounts > 1) {
                $prices = $this->dependencyProvider->getModules()->Articles()->sGetPricegroupDiscount(
                    $customerGroupKey,
                    $priceGroupId,
                    $price->getPrice(),
                    1,
                    true,
                    $productData
                );
                $hasFromOne = false;
                foreach ($prices as $priceTmp) {
                    if ((int) $priceTmp['from'] === 1) {
                        $hasFromOne = true;
                    }
                }
                // If there is no price with "from = 1",
                // it has to be pushed to the prices array manually to prevent wrong price calculations
                if (!$hasFromOne) {
                    $arr = [];
                    $arr['from'] = 1;
                    $arr['to'] = $prices[0]['from'] - 1;
                    $arr['price'] = $this->dependencyProvider->getModules()->Articles()->sCalculatingPrice(
                        $price->getPrice(),
                        $productData['tax'],
                        $productData['taxID']
                    );

                    array_unshift($prices, $arr);
                }
            } else {
                // If there is only one discount, the matrix calculation couldn't be done.
                // So the calculation has to be simulated and the prices array has to be built on our own
                $discountStart = $this->connection->createQueryBuilder()
                    ->select('discountstart')
                    ->from('s_core_pricegroups_discounts')
                    ->where('groupID = :groupId')
                    ->andWhere('customergroupID = :customerGroupId')
                    ->setParameter('groupId', $priceGroupId)
                    ->setParameter('customerGroupId', $customerGroupId)
                    ->execute()
                    ->fetchColumn();

                $discountFromPrice = $this->dependencyProvider->getModules()->Articles()->sGetPricegroupDiscount(
                    $customerGroupKey,
                    $priceGroupId,
                    $price->getPrice(),
                    $discountStart,
                    false,
                    $productData
                );

                $prices[0]['price'] = $price->getPrice() * ((100 + $productData['tax']) / 100);
                $prices[0]['from'] = 1;
                $prices[0]['to'] = $discountStart - 1;
                $prices[1]['price'] = $discountFromPrice * ((100 + $productData['tax']) / 100);
                $prices[1]['from'] = $discountStart;
                $prices[1]['to'] = 'beliebig';
            }
        } else {
            $builder = $this->getPriceQueryBuilder();
            $builder->setParameter('articleDetailId', $variant->getId())
                ->setParameter('customerGroupKey', $customerGroupKey);

            $prices = $builder->getQuery()->getArrayResult();
        }

        if (empty($prices) && $customerGroupKey !== $fallbackKey) {
            return $this->getPricesForCustomerGroup($variant, $fallbackKey, $fallbackId, $fallbackKey, $fallbackId);
        }

        if (empty($prices)) {
            return $this->getPricesForCustomerGroup($variant, 'EK', 1, 'EK', 1);
        }

        return $prices;
    }

    /**
     * Helper function to get an query builder object which creates an select
     * on the article price table with an article detail id and customer group key
     * condition.
     * The result will be sorted by the from value of the prices.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getPriceQueryBuilder()
    {
        return $this->modelManager->createQueryBuilder()
            ->select(['prices'])
            ->from(Price::class, 'prices')
            ->where('prices.articleDetailsId = :articleDetailId')
            ->andWhere('prices.customerGroupKey = :customerGroupKey')
            ->orderBy('prices.from', 'ASC');
    }

    /**
     * Helper function for the getVariantData function.
     * This function returns the value for the ordernumber column
     * of the s_order_basket.
     * Override this function to control an own esd handling
     * in the basket section.
     *
     * @param Detail $variant
     *
     * @return string
     */
    private function getEsdFlag(Detail $variant)
    {
        if ($variant->getEsd() instanceof Esd) {
            return 1;
        }

        return 0;
    }

    /**
     * Search an product variant (\Shopware\Models\Article\Detail) with the passed
     * product order number and returns it.
     *
     * @param string $orderNumber
     *
     * @return null|Detail
     */
    private function getVariantByOrderNumber($orderNumber)
    {
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['variant', 'article'])
            ->from(Detail::class, 'variant')
            ->innerJoin('variant.article', 'article')
            ->where('variant.number = :orderNumber')
            ->andWhere('variant.active = :active')
            ->andWhere('article.active = :active')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->setParameter('orderNumber', $orderNumber)
            ->setParameter('active', true);

        return $builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
    }

    /**
     * Helper function to validate the passed product variant and the passed quantity.
     * Checks if the passed variant fulfill all requirements to add the product
     * in the current session to the basket.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return array
     */
    private function validateProduct($variant, $quantity, array $parameter = [])
    {
        //check if the current shop customer group can see/buy the passed product variant.
        if (!$this->isCustomerGroupAllowed($variant, $this->getCurrentCustomerGroup())) {
            return $this->getNoValidOrderNumberFailure();
        }

        //check if the current session is a bot session.
        if ($this->isBotSession()) {
            return $this->getBotSessionFailure();
        }

        //check if the standard shopware notify event returns true.
        if ($this->fireNotifyUntilAddProductStart($variant, $quantity, $parameter)) {
            $lastInsertedCustomProductBasketId = $this->templateManager->getTemplateVars('lastInsertedCustomProductBasketId');
            if ($lastInsertedCustomProductBasketId) {
                return [
                    'success' => true,
                    'isCustomProduct' => true,
                    'lastInsertedCustomProductBasketId' => $lastInsertedCustomProductBasketId,
                ];
            }

            return $this->getAddProductStartFailure();
        }

        //check if the variant is in stock and the last stock flag is set to true.
        if (!$this->isVariantInStock($variant, $quantity)) {
            return $this->getInStockFailure();
        }

        return ['success' => true];
    }

    /**
     * Helper function to check if the passed variant has enough stock.
     * Returns false if the lastStock flag is set to true and
     * the passed quantity is greater than the stock value of the variant.
     * <br>
     * Notice: This function sums the already added quantity of the same variant in the basket.
     *
     * @param Detail $variant
     * @param int    $quantity
     *
     * @return bool
     */
    private function isVariantInStock(Detail $variant, $quantity)
    {
        $basketQuantity = $this->getSummarizedQuantityOfVariant($variant, $quantity);

        $totalQuantity = $basketQuantity + $quantity;

        return !($variant->getLastStock() && $totalQuantity > $variant->getInStock());
    }

    /**
     * Helper function to get the summarized quantity of the basket for the passed variant.
     *
     * @param Detail $variant
     * @param int    $basketId
     *
     * @return int returns the summarized value of the quantity column of the s_order_basket.
     *             If the variant isn't in basket, the function return the numeric value 0
     */
    private function getSummarizedQuantityOfVariant(Detail $variant, $basketId)
    {
        $basketQuantity = (int) $this->connection->createQueryBuilder()
            ->select('SUM(quantity)')
            ->from('s_order_basket')
            ->where('ordernumber = :ordernumber')
            ->andWhere('sessionID = :sessionId')
            ->andWhere('id = :basketId')
            ->groupBy('ordernumber')
            ->setParameter('ordernumber', $variant->getNumber())
            ->setParameter('sessionId', $this->dependencyProvider->getSession()->get('sessionId'))
            ->setParameter('basketId', $basketId)
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);

        return $basketQuantity;
    }

    /**
     * Helper function to fire the notify until event for "Shopware_Modules_Basket_AddArticle_Start".
     * If the event has an event listener in some plugins which returns true, the add product
     * process will be canceled.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return mixed
     *               result of the Shopware_Modules_Basket_AddArticle_Start NotifyUntil event
     */
    private function fireNotifyUntilAddProductStart(Detail $variant, $quantity, array $parameter = [])
    {
        return $this->eventManager->notifyUntil(
            'Shopware_Modules_Basket_AddArticle_Start',
            [
                'subject' => $this,
                'id' => $variant->getNumber(),
                'quantity' => $quantity,
                'parameter' => $parameter,
            ]
        );
    }

    /**
     * Helper function to check if the passed customer group
     * can see the passed product variant.
     *
     * @param Detail $variant
     * @param Group  $customerGroup
     *
     * @return bool
     */
    private function isCustomerGroupAllowed(Detail $variant, Group $customerGroup)
    {
        if ($variant->getArticle()->getCustomerGroups()->contains($customerGroup)) {
            return false;
        }

        return true;
    }

    /**
     * Helper function to check if the passed variant with the additional parameters
     * has to be add as new row or update an existing row.
     * The shopware standard checks only the order number of the passed variant.
     * If this number is already in the basket, the basket id will be returned
     * and the basket row will be updated with the new quantity and the new variant data.
     * To implement an handling for this logic, you can create an event listener
     * for this function with an Enlight_Hook_After event to modify the return value.
     * All parameters of the addArticle function are also available here.
     * To control that an existing row has to been updated, return the id of the
     * basket row.
     *
     * DON'T remove "unused" parameter $parameter, it is used in Bootstrap::onShouldAddAsNewPosition()
     *
     * @param Detail $variant
     * @param array  $parameter
     *
     * @return bool|int
     */
    private function shouldAddAsNewPosition(Detail $variant, array $parameter)
    {
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['basket', 'attribute'])
            ->from(Basket::class, 'basket')
            ->leftJoin('basket.attribute', 'attribute')
            ->where('basket.sessionId = :sessionId')
            ->andWhere('basket.orderNumber = :orderNumber')
            ->andWhere('attribute.swagAboCommerceId = :aboCommerceId')
            ->andWhere('attribute.swagAboCommerceDuration = :aboCommerceDuration OR attribute.swagAboCommerceDuration IS NULL')
            ->andWhere('attribute.swagAboCommerceDeliveryInterval = :aboCommerceDeliveryInterval')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->setParameter('sessionId', $this->getSessionId())
            ->setParameter('orderNumber', $variant->getNumber())
            ->setParameter('aboCommerceId', $parameter['swagAboCommerceId'])
            ->setParameter('aboCommerceDuration', $parameter['swagAboCommerceDuration'])
            ->setParameter('aboCommerceDeliveryInterval', $parameter['swagAboCommerceDeliveryInterval']);

        $result = $builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        if ($result instanceof Basket) {
            return $result->getId();
        }

        return true;
    }

    /**
     * Helper function to create a new basket item.
     * The function expects an array with basket data.
     * All parameters of the addArticle function are also available here.
     *
     * @param array $data
     *
     * @return int|null the inserted data
     */
    private function createItem(array $data)
    {
        $basket = $this->getNewBasketItem();
        $basket->fromArray($data);

        $this->modelManager->persist($basket);
        $this->modelManager->flush($basket);

        if ($basket instanceof Basket) {
            return $basket->getId();
        }

        return null;
    }

    /**
     * Helper function to check if the selected country would be delivered with net prices.
     *
     * @return bool
     */
    private function isShippingCountryNet()
    {
        $session = $this->dependencyProvider->getSession();
        if (empty($session->get('sUserGroupData')['id'])) {
            return false;
        }

        if (empty($country = $session->get('sCountry'))) {
            return false;
        }

        $country = $this->modelManager->find(Country::class, $country);

        return (bool) $country->getTaxFree();
    }

    /**
     * Helper function to check if the current customer would see net or gross prices.
     *
     * @return bool
     */
    private function isCustomerGroupNet()
    {
        return !$this->getCurrentCustomerGroup()->getTax();
    }

    /**
     * Getter function for the session id
     * Used for the customer identification
     *
     * @return string
     */
    private function getSessionId()
    {
        return $this->dependencyProvider->getSession()->get('sessionId');
    }

    /**
     * @param int $orderDetailId
     *
     * @return array
     */
    private function getOrderData($orderDetailId)
    {
        $queryBuilder = $this->modelManager->createQueryBuilder();

        return $queryBuilder->select('orderDetail', 'orderDetailAttributes')
            ->from(OrderDetails::class, 'orderDetail')
            ->leftJoin('orderDetail.attribute', 'orderDetailAttributes')
            ->where('orderDetail.id = :id')
            ->setParameter('id', $orderDetailId)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @param string $productOrderNumber
     *
     * @return string
     */
    private function isShippingFree($productOrderNumber)
    {
        return $this->connection->createQueryBuilder()
            ->select('articleDetails.shippingfree')
            ->from('s_articles_details', 'articleDetails')
            ->where('articleDetails.ordernumber = :orderNumber')
            ->setParameter('orderNumber', $productOrderNumber)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @param array $orderData
     *
     * @return float
     */
    private function getNetPrice($orderData)
    {
        $price = $orderData['price'];
        $taxRate = $orderData['taxRate'];

        return $price / (1 + $taxRate / 100);
    }

    /**
     * Helper function to check if the current session is a bot session.
     *
     * @return bool
     */
    private function isBotSession()
    {
        return $this->dependencyProvider->getSession()->get('Bot');
    }

    /**
     * Getter function for the snippet namespace property.
     * The snippet namespace is used for to get the translation
     * for the different basket notices and errors.
     * If the class property contains null, this function loads automatically the default snippet namespace
     * "Shopware()->Snippets()->getNamespace('frontend/checkout')".
     *
     * @return Enlight_Components_Snippet_Namespace
     */
    private function getSnippetNamespace()
    {
        return $this->snippetManager->getNamespace('frontend/checkout');
    }

    /**
     * Helper function to create an array result with success false and
     * the error "no valid order number passed".
     *
     * @return array
     */
    private function getNoValidOrderNumberFailure()
    {
        return [
            'success' => false,
            'error' => [
                'code' => self::FAILURE_NO_VALID_ORDER_NUMBER,
                'message' => $this->getSnippetNamespace()->get('no_valid_order_number', 'The order number is not valid'),
            ],
        ];
    }

    /**
     * Helper function to create an array result with success false and
     * the error "You are identified as bot!".
     *
     * @return array
     */
    private function getBotSessionFailure()
    {
        return [
            'success' => false,
            'error' => [
                'code' => self::FAILURE_BOT_SESSION,
                'message' => $this->getSnippetNamespace()->get('bot_session', 'You are identified as bot!'),
            ],
        ];
    }

    /**
     * Helper function to create an array result with success false and
     * the error "The add product process aborted over the Shopware_Modules_Basket_AddArticle_Start event.".
     *
     * @return array
     */
    private function getAddProductStartFailure()
    {
        return [
            'success' => false,
            'error' => [
                'code' => self::FAILURE_ADD_PRODUCT_START_EVENT,
                'message' => $this->getSnippetNamespace()->get(
                    'notify_until_add_article_start',
                    'The add article process aborted over the Shopware_Modules_Basket_AddArticle_Start event'
                ),
            ],
        ];
    }

    /**
     * Helper function to create an array result with success false and
     * the error "The add product process aborted over the Shopware_Modules_Basket_AddArticle_Start event.".
     *
     * @return array
     */
    private function getInStockFailure()
    {
        return [
            'success' => false,
            'error' => [
                'code' => self::FAILURE_NOT_ENOUGH_STOCK,
                'message' => $this->getSnippetNamespace()->get('not_enough_stock', 'Not enough article stock!'),
            ],
        ];
    }
}
