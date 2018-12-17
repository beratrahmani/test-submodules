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

namespace SwagLiveShopping\Components;

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Esd;
use Shopware\Models\Article\Price;
use Shopware\Models\Customer\Group;
use Shopware\Models\Order\Basket;
use SwagLiveShopping\Models\LiveShopping as LiveShoppingModel;

/**
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class LiveShoppingBasket implements LiveShoppingBasketInterface
{
    /**
     * Contains the entity manager of shopware.
     * Used for the model access.
     * If the class property contains null, the getter function loads
     * the entity manager over "Shopware()->Models()".
     *
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * Contains the basket snippet namespace object which is used to get the translation
     * for the different basket notices and errors.
     *
     * @var \Enlight_Components_Snippet_Manager
     */
    protected $snippet;

    /**
     * Contains the Enlight_Event_EventManager.
     * If this property is set to null, the getter function
     * of this property loads the default class over "Enlight()->Events()".
     * Used for all application events in this class.
     *
     * @var \Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * This property is only used for unit tests.
     *
     * @var null
     */
    protected $newBasketItem;

    /**
     * @var DependencyProvider
     */
    protected $dependencyProvider;

    /**
     * @var LiveShoppingInterface
     */
    protected $liveShopping;

    /**
     * @param DependencyProvider                  $dependencyProvider
     * @param ModelManager                        $modelManager
     * @param \Enlight_Event_EventManager         $eventManager
     * @param \Enlight_Components_Snippet_Manager $snippet
     * @param LiveShoppingInterface               $liveShopping
     */
    public function __construct(
        DependencyProvider $dependencyProvider,
        ModelManager $modelManager,
        \Enlight_Event_EventManager $eventManager,
        \Enlight_Components_Snippet_Manager $snippet,
        LiveShoppingInterface $liveShopping
    ) {
        $this->dependencyProvider = $dependencyProvider;
        $this->modelManager = $modelManager;
        $this->eventManager = $eventManager;
        $this->snippet = $snippet;
        $this->liveShopping = $liveShopping;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewBasketItem()
    {
        if ($this->newBasketItem === null) {
            return new Basket();
        }

        return $this->newBasketItem;
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
        $id = $this->shouldAddAsNewPosition($variant, $quantity, $parameter);

        if ($id === true) {
            //if the shouldAddAsNewPosition function returns true, the variant will be added as new position
            $id = $this->createItem(
                $this->getVariantCreateData($variant, $quantity, $parameter),
                $variant,
                $quantity,
                $parameter
            );
        } else {
            //in the other case, the shouldAddAsNewPosition returns the id of the basket position which
            //has to be updated.
            $data = $this->getVariantUpdateData($variant, $quantity, $parameter);

            //the quantity could be changed by a hook
            if (isset($data['quantity'])) {
                $quantity = $data['quantity'];
            }

            $this->updateItem(
                $id,
                $data,
                $variant,
                $quantity,
                $parameter
            );
        }

        //we have to execute the sUpdateArticle function to update the basket prices.
        $this->dependencyProvider->getModule('Basket')->sUpdateArticle($id, $quantity);

        return [
            'success' => true,
            'data' => $this->getItem($id),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantCreateData(Detail $variant, $quantity, array $parameter = [])
    {
        $price = $this->getVariantPrice($variant, $quantity, $parameter);

        $result = [
            'sessionId' => (string) $this->dependencyProvider->getSession()->get('sessionId'),
            'customerId' => (string) $this->getUserId(),
            'articleName' => $this->getVariantName($variant, $quantity, $parameter),
            'articleId' => $this->getProductId($variant, $quantity, $parameter),
            'orderNumber' => $this->getNumber($variant, $quantity, $parameter),
            'shippingFree' => $this->getShippingFree($variant, $quantity, $parameter),
            'quantity' => $quantity,
            'price' => $price['gross'],
            'netPrice' => $price['net'],
            'date' => 'now',
            'esdArticle' => $this->getEsdFlag($variant, $quantity, $parameter),
            'partnerId' => (string) $this->dependencyProvider->getSession()->get('sPartner'),
            'attribute' => $this->getAttributeCreateData($variant, $quantity, $parameter),
        ];

        if (array_key_exists('liveShopping', $parameter)) {
            /* @var LiveShoppingModel $liveShopping */
            $liveShopping = $parameter['liveShopping'];

            //the current price is set to gross price if the current customer group is defined as "display gross price in frontend"
            $price = $liveShopping->getCurrentPrice();
            $netPrice = $price;

            $taxRate = $this->liveShopping->getCurrentTaxRate(
                $variant->getArticle()
            );

            if (!$this->liveShopping->displayNetPrices()) {
                //in this case "$price" is a gross price.
                $netPrice = $price / (100 + $taxRate) * 100;
            }

            $result['netPrice'] = $netPrice;
            $result['price'] = $price;
            $result['taxRate'] = $taxRate;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCreateData(Detail $variant, $quantity, array $parameter = [])
    {
        $result = [
            'attribute1' => null,
            'attribute2' => null,
            'attribute3' => null,
            'attribute4' => null,
            'attribute5' => null,
            'attribute6' => null,
        ];

        if (array_key_exists('liveShopping', $parameter)) {
            /* @var LiveShoppingModel $liveShopping */
            $liveShopping = $parameter['liveShopping'];
            $now = new \DateTime();

            $result['swagLiveShoppingId'] = $liveShopping->getId();
            $result['swagLiveShoppingTimestamp'] = $now->format('Y-m-d h:i:s');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantUpdateData($variant, $quantity, array $parameter = [])
    {
        $summarizedQuantity = $this->getSummarizedQuantityOfVariant($variant, $quantity, $parameter);

        $attribute = $this->getAttributeUpdateData($variant, $quantity, $parameter);

        $data = [
            'quantity' => $summarizedQuantity + $quantity,
        ];
        if (!empty($attribute)) {
            $data['attribute'] = $attribute;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeUpdateData($variant, $quantity, array $parameter = [])
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function updateItem($id, array $data, Detail $variant, $quantity, array $parameter)
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
     * {@inheritdoc}
     */
    public function getItem($id, $hydrationMode = AbstractQuery::HYDRATE_ARRAY)
    {
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['basket', 'attribute'])
            ->from(Basket::class, 'basket')
            ->leftJoin('basket.attribute', 'attribute')
            ->where('basket.id = :id')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->setParameter('id', $id);

        return $builder->getQuery()->getOneOrNullResult(
            $hydrationMode
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId()
    {
        return $this->dependencyProvider->getSession()->get('sUserId');
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantName($variant, $quantity, array $parameter = [])
    {
        $translation = $this->dependencyProvider->getModule('Articles')->sGetArticleNameByOrderNumber($variant->getNumber(), true);
        if (!empty($translation['articleName'])) {
            $name = $translation['articleName'];
        } else {
            $name = $variant->getArticle()->getName();
        }

        if ($translation['additionaltext']) {
            $name .= ' ' . $translation['additionaltext'];
        }

        return $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId($variant, $quantity, array $parameter = [])
    {
        return $variant->getArticle()->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getNumber($variant, $quantity, array $parameter = [])
    {
        return $variant->getNumber();
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingFree($variant, $quantity, array $parameter = [])
    {
        if ($variant->getShippingFree()) {
            return 1;
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantPrice($variant, $quantity, array $parameter = [])
    {
        $prices = $this->getPricesForCustomerGroup(
            $variant,
            $this->getCurrentCustomerGroup()->getKey(),
            $this->dependencyProvider->getShop()->getCustomerGroup()->getKey(),
            AbstractQuery::HYDRATE_OBJECT,
            $parameter
        );

        if ($prices === null) {
            return false;
        }

        $price = $this->getPriceForQuantity($prices, $quantity, $variant, $parameter);

        if ($price === null) {
            return false;
        }

        return $this->getNetAndGrossPriceForVariantPrice($price, $variant, $parameter);
    }

    /**
     * {@inheritdoc}
     */
    public function getNetAndGrossPriceForVariantPrice($price, $variant, array $parameter)
    {
        $gross = $this->dependencyProvider->getModule('Articles')->sCalculatingPriceNum(
            $price->getPrice(),
            $variant->getArticle()->getTax()->getTax(),
            false,
            false,
            $variant->getArticle()->getTax()->getId(),
            false,
            $variant
        );

        return [
            'gross' => $gross,
            'net' => $price->getPrice(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPricesForCustomerGroup(
        Detail $variant,
        $customerGroupKey,
        $fallbackKey,
        $hydrationMode = AbstractQuery::HYDRATE_ARRAY,
        array $parameter = []
    ) {
        //no group key passed?
        if (empty($customerGroupKey)) {
            $customerGroupKey = $fallbackKey;
        }

        $builder = $this->getPriceQueryBuilder();
        $builder->setParameter('articleDetailId', $variant->getId());
        $builder->setParameter('customerGroupKey', $customerGroupKey);

        $prices = $builder->getQuery()->getResult($hydrationMode);

        if (empty($prices) && $customerGroupKey !== $fallbackKey) {
            return $this->getPricesForCustomerGroup($variant, $fallbackKey, $fallbackKey, $hydrationMode);
        }

        if (empty($prices)) {
            return null;
        }

        return $prices;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceQueryBuilder()
    {
        $builder = $this->modelManager->createQueryBuilder();

        return $builder->select(['prices'])
            ->from(Price::class, 'prices')
            ->where('prices.articleDetailsId = :articleDetailId')
            ->andWhere('prices.customerGroupKey = :customerGroupKey')
            ->orderBy('prices.from', 'ASC');
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentCustomerGroup()
    {
        $customerGroupData = $this->dependencyProvider->getSession()->get('sUserGroupData');

        $customerGroup = null;

        /* @var Group $customerGroup */
        //check if the customer logged in and get the customer group model for the logged in customer
        if (!empty($customerGroupData['id'])) {
            $repo = $this->modelManager->getRepository(Group::class);
            $customerGroup = $repo->find($customerGroupData['id']);
        }

        //if no customer group given, get the default customer group.
        if (!$customerGroup instanceof Group) {
            $customerGroup = $this->dependencyProvider->getShop()->getCustomerGroup();
        }

        return $customerGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceForQuantity(array $prices, $quantity, Detail $variant, array $parameter = [])
    {
        $currentPrice = null;

        /* @var Price $price */
        foreach ($prices as $price) {
            if (!is_numeric($price->getTo())) {
                $currentPrice = $price;
                break;
            }

            if ($quantity >= $price->getFrom() && $quantity <= $price->getTo()) {
                $currentPrice = $price;
                break;
            }
        }

        return $currentPrice;
    }

    /**
     * {@inheritdoc}
     */
    public function getEsdFlag(Detail $variant, $quantity, array $parameter = [])
    {
        if ($variant->getEsd() instanceof Esd) {
            return 1;
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantByOrderNumber($orderNumber)
    {
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['variant', 'product'])
            ->from(Detail::class, 'variant')
            ->innerJoin('variant.article', 'product')
            ->where('variant.number = :orderNumber')
            ->andWhere('variant.active = :active')
            ->andWhere('product.active = :active')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->setParameter('orderNumber', $orderNumber)
            ->setParameter('active', true);

        return $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_OBJECT
        );
    }

    /**
     * {@inheritdoc}
     */
    public function validateProduct(Detail $variant, $quantity, array $parameter = [])
    {
        //check if the current shop customer group can see/buy the passed product variant.
        if (!$this->isCustomerGroupAllowed($variant, $this->getCurrentCustomerGroup(), $parameter)) {
            return $this->getNoValidOrderNumberFailure();
        }

        //check if the current session is a bot session.
        if ($this->isBotSession()) {
            return $this->getBotSessionFailure();
        }

        //check if the standard shopware notify event returns true.
        if ($this->fireNotifyUntilAddArticleStart($variant, $quantity, $parameter)) {
            return $this->getAddArticleStartFailure();
        }

        //check if the variant is in stock and the last stock flag is set to true.
        if (!$this->isVariantInStock($variant, $quantity, $parameter)) {
            return $this->getInStockFailure();
        }

        return ['success' => true];
    }

    /**
     * {@inheritdoc}
     */
    public function isVariantInStock($variant, $quantity, array $parameter = [])
    {
        $basketQuantity = $this->getSummarizedQuantityOfVariant($variant, $quantity, $parameter);

        $totalQuantity = $basketQuantity + $quantity;

        if ($variant->getArticle()->getLastStock() && $totalQuantity > $variant->getInStock()) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSummarizedQuantityOfVariant($variant, $quantity, array $parameter = [])
    {
        $sql = '
            SELECT SUM(basket.quantity)
            FROM s_order_basket basket
            WHERE basket.ordernumber = ?
            AND sessionID = ?
            GROUP BY basket.ordernumber
        ';

        $connection = $this->modelManager->getConnection();
        $basketQuantity = $connection->fetchColumn($sql, [
            $variant->getNumber(),
            $this->dependencyProvider->getSession()->get('sessionId'),
        ]);

        if (!is_numeric($basketQuantity)) {
            $basketQuantity = 0;
        }

        return $basketQuantity;
    }

    /**
     * {@inheritdoc}
     */
    public function fireNotifyUntilAddArticleStart(Detail $variant, $quantity, array $parameter = [])
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
     * {@inheritdoc}
     */
    public function isCustomerGroupAllowed(Detail $variant, Group $customerGroup, array $parameter)
    {
        if ($variant->getArticle()->getCustomerGroups()->contains($customerGroup)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldAddAsNewPosition(Detail $variant, $quantity, array $parameter)
    {
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['basket', 'attribute'])
            ->from(Basket::class, 'basket')
            ->leftJoin('basket.attribute', 'attribute')
            ->where('basket.sessionId = :sessionId')
            ->andWhere('basket.orderNumber = :orderNumber')
            ->setFirstResult(0)
            ->setMaxResults(1);

        $builder->setParameter('sessionId', $this->dependencyProvider->getSession()->get('sessionId'));
        $builder->setParameter('orderNumber', $variant->getNumber());

        $result = $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_OBJECT
        );

        //check if the current process would add a bundle product
        if ($parameter['liveShopping'] || $parameter['forceNewPosition']) {
            return true;
        }

        if ($result instanceof Basket) {
            return $result->getId();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function createItem(array $data, Detail $variant, $quantity, array $parameter)
    {
        $this->modelManager->clear();

        $basket = $this->getNewBasketItem();
        $basket->fromArray($data);

        $this->modelManager->persist($basket);
        $this->modelManager->flush();

        if ($basket instanceof Basket) {
            return $basket->getId();
        }

        return null;
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
                'code' => LiveShoppingBasketInterface::FAILURE_NO_VALID_ORDER_NUMBER,
                'message' => $this->snippet->getNamespace('frontend/checkout')->get(
                    'no_valid_order_number',
                    'The order number is not valid'
                ),
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
                'code' => LiveShoppingBasketInterface::FAILURE_BOT_SESSION,
                'message' => $this->snippet->getNamespace('frontend/checkout')->get(
                    'bot_session',
                    'You are identified as bot!'
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
    private function getAddArticleStartFailure()
    {
        return [
            'success' => false,
            'error' => [
                'code' => LiveShoppingBasketInterface::FAILURE_ADD_PRODUCT_START_EVENT,
                'message' => $this->snippet->getNamespace('frontend/checkout')->get(
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
                'code' => LiveShoppingBasketInterface::FAILURE_NOT_ENOUGH_STOCK,
                'message' => $this->snippet->getNamespace('frontend/checkout')->get(
                    'not_enough_stock',
                    'Not enough product stock!'
                ),
            ],
        ];
    }
}
