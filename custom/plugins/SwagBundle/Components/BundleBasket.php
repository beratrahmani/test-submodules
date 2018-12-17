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

namespace SwagBundle\Components;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Esd;
use Shopware\Models\Customer\Group;
use Shopware\Models\Order\Basket;
use SwagBundle\Services\CustomerGroupServiceInterface;
use SwagBundle\Services\Dependencies\ProviderInterface;
use SwagBundle\Services\Products\ProductPriceServiceInterface;

class BundleBasket implements BundleBasketInterface
{
    /**
     * @var ProviderInterface
     */
    private $dependenciesProvider;

    /**
     * @var ProductPriceServiceInterface
     */
    private $productPriceService;

    /**
     * @var CustomerGroupServiceInterface
     */
    private $customerGroupService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @param ProviderInterface                    $dependenciesProvider
     * @param ProductPriceServiceInterface         $productPriceService
     * @param CustomerGroupServiceInterface        $customerService
     * @param Connection                           $connection
     * @param \Enlight_Event_EventManager          $eventManager
     * @param ModelManager                         $modelManager
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     */
    public function __construct(
        ProviderInterface $dependenciesProvider,
        ProductPriceServiceInterface $productPriceService,
        CustomerGroupServiceInterface $customerService,
        Connection $connection,
        \Enlight_Event_EventManager $eventManager,
        ModelManager $modelManager,
        \Shopware_Components_Snippet_Manager $snippetManager
    ) {
        $this->dependenciesProvider = $dependenciesProvider;
        $this->productPriceService = $productPriceService;
        $this->customerGroupService = $customerService;
        $this->connection = $connection;
        $this->eventManager = $eventManager;
        $this->modelManager = $modelManager;
        $this->snippetManager = $snippetManager;
    }

    /**
     * {@inheritdoc}
     */
    public function addProduct($orderNumber, $quantity = 1, array $parameter = [])
    {
        // ensure that the used quantity is an integer value.
        if (empty($quantity) || !is_numeric($quantity)) {
            $quantity = 1;
        } else {
            $quantity = (int) $quantity;
        }

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

        if ($id === true) {
            //if the shouldAddAsNewPosition function returns true, the variant will be added as new position
            $data = $this->getVariantCreateData($variant, $quantity, $parameter);
            $id = $this->createItem($data);
        } else {
            //in the other case, the shouldAddAsNewPosition returns the id of the basket position which
            //has to be updated.
            $data = $this->getVariantUpdateData($quantity, $parameter);
            $quantity = $data['quantity'];

            $this->updateItem($id, $data);
        }

        //we have to execute the sUpdateArticle function to update the basket prices.
        $this->dependenciesProvider->getBasketModule()->sUpdateArticle($id, $quantity);

        return [
            'success' => true,
            'data' => $this->getItem($id),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validateProduct(Detail $variant, $quantity, array $parameter = [])
    {
        //check if the current shop customer group can see/buy the passed product variant.
        if (!$this->isCustomerGroupAllowed($variant, $this->customerGroupService->getCurrentCustomerGroup())) {
            return $this->getNoValidOrderNumberFailure();
        }

        //check if the current session is a bot session.
        if ($this->dependenciesProvider->getSession()->get('Bot')) {
            return $this->getBotSessionFailure();
        }

        //check if the standard shopware notify event returns true.
        if ($this->fireNotifyUntilAddArticleStart($variant, $quantity, $parameter)) {
            return $this->getAddArticleStartFailure();
        }

        //check if the variant is in stock and the last stock flag is set to true.
        if (!$this->isVariantInStock($variant, $quantity)) {
            return $this->getInStockFailure();
        }

        return ['success' => true];
    }

    /**
     * {@inheritdoc}
     */
    public function shouldAddAsNewPosition(Detail $variant, array $parameter)
    {
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['basket', 'attribute'])
            ->from(Basket::class, 'basket')
            ->leftJoin('basket.attribute', 'attribute')
            ->where('basket.sessionId = :sessionId')
            ->andWhere('basket.orderNumber = :orderNumber')
            ->setFirstResult(0)
            ->setMaxResults(1);

        $builder->setParameters([
            'sessionId' => $this->dependenciesProvider->getSession()->get('sessionId'),
            'orderNumber' => $variant->getNumber(),
        ]);

        $result = $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_OBJECT
        );

        //check if the current process would add a bundle product
        if ($parameter['bundleId'] || $parameter['forceNewPosition']) {
            return true;
        }

        //the update position parameter is passed in the following szenario:
        //The customer added a bundle with the product "SW-2000"
        //and the customer added the product "SW-2000" as normal product,
        //the $additional['updatePosition'] property contains now the id of the normal position
        //this position has to been updated.
        if ($parameter['updatePosition']) {
            return (int) $parameter['updatePosition'];
        }

        if ($result instanceof Basket) {
            return (int) $result->getId();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantCreateData(Detail $variant, $quantity, array $parameter = [])
    {
        $session = $this->dependenciesProvider->getSession();
        $customerGroup = $this->customerGroupService->getCurrentCustomerGroup();
        $price = $this->productPriceService->getProductPrices($variant, $customerGroup, $quantity);

        return [
            'sessionId' => (string) $session->get('sessionId'),
            'customerId' => (string) $session->get('sUserId'),
            'articleName' => $this->getVariantName($variant),
            'articleId' => $variant->getArticle()->getId(),
            'orderNumber' => $variant->getNumber(),
            'shippingFree' => $variant->getShippingFree(),
            'quantity' => $quantity,
            'price' => $price['gross'],
            'netPrice' => $price['net'],
            'date' => 'now',
            'esdArticle' => $this->getEsdFlag($variant),
            'partnerId' => (string) $session->get('sPartner'),
            'attribute' => $this->getAttributeCreateData($parameter),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantUpdateData($quantity, array $parameter = [])
    {
        $oldQuantity = (int) $this->connection->createQueryBuilder()
            ->select('quantity')
            ->from('s_order_basket')
            ->where('id = :basketId')
            ->setParameter('basketId', $parameter['updatePosition'])
            ->execute()->fetchColumn();

        $data = [];
        $data['quantity'] = $oldQuantity + $quantity;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getSummarizedQuantityOfVariant(Detail $variant)
    {
        $basketQuantity = (int) $this->connection->createQueryBuilder()
            ->select('SUM(quantity)')
            ->from('s_order_basket')
            ->where('ordernumber = :orderNumber')
            ->andWhere('sessionID = :sessionId')
            ->groupBy('ordernumber')
            ->setParameters([
                'orderNumber' => $variant->getNumber(),
                'sessionId' => $this->dependenciesProvider->getSession()->get('sessionId'),
            ])
            ->execute()->fetchColumn();

        return $basketQuantity;
    }

    /**
     * Helper function of the addProduct function.
     * Generates the default shopware attribute data for the new basket row and adds additional bundle attributes
     *
     * @param array $parameter
     *
     * @return array
     */
    private function getAttributeCreateData(array $parameter = [])
    {
        $attributesArray = [
            'attribute1' => null,
            'attribute2' => null,
            'attribute3' => null,
            'attribute4' => null,
            'attribute5' => null,
            'attribute6' => null,
        ];

        if (array_key_exists('bundleId', $parameter)) {
            $attributesArray['bundleId'] = $parameter['bundleId'];
            $attributesArray['bundleArticleOrdernumber'] = $parameter['bundleArticleOrdernumber'];
            $attributesArray['bundlePackageId'] = $parameter['bundlePackageId'];
        }

        return $attributesArray;
    }

    /**
     * Helper function to update an existing basket item.
     * The function expects an array with basket data.
     *
     * @param int   $id
     * @param array $data
     */
    private function updateItem($id, $data)
    {
        $basket = $this->modelManager->find(Basket::class, $id);
        if (!$basket instanceof Basket) {
            $basket = new Basket();
        }

        if (empty($data)) {
            return;
        }

        $basket->fromArray($data);

        $this->modelManager->persist($basket);
        $this->modelManager->flush();
    }

    /**
     * Helper function for the getVariantData function.
     * This function returns the translated product name and additional text
     *
     * @param Detail $variant
     *
     * @return string
     */
    private function getVariantName(Detail $variant)
    {
        /** @var array $translation */
        $translation = $this->dependenciesProvider->getArticlesModule()
            ->sGetArticleNameByOrderNumber($variant->getNumber(), true);
        if ($translation['articleName']) {
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
     * @param Detail $variant
     *
     * @return int
     */
    private function getEsdFlag(Detail $variant)
    {
        return $variant->getEsd() instanceof Esd ? 1 : 0;
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
        $basketQuantity = $this->getSummarizedQuantityOfVariant($variant);

        $totalQuantity = $basketQuantity + $quantity;

        if ($totalQuantity > $variant->getInStock() && $variant->getLastStock()) {
            return false;
        }

        return true;
    }

    /**
     * Returns the basket data for the passed basket row id.
     *
     * @param int $id
     *
     * @return array|null
     */
    private function getItem($id)
    {
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['basket', 'attribute'])
            ->from(Basket::class, 'basket')
            ->leftJoin('basket.attribute', 'attribute')
            ->where('basket.id = :id')
            ->setFirstResult(0)
            ->setMaxResults(1);

        $builder->setParameters(['id' => $id]);

        return $builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * Search a product variant (\Shopware\Models\Article\Detail) with the passed
     * product order number and returns it.
     *
     * @param string $orderNumber
     *
     * @return null|Detail
     */
    private function getVariantByOrderNumber($orderNumber)
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
            ->setParameters(['orderNumber' => $orderNumber, 'active' => true]);

        return $builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
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
     * @return \Enlight_Event_EventArgs result of the Shopware_Modules_Basket_AddArticle_Start NotifyUntil event
     */
    private function fireNotifyUntilAddArticleStart($variant, $quantity, array $parameter = [])
    {
        return $this->eventManager->notifyUntil(
            'Shopware_Modules_Basket_AddArticle_Start',
            [
                'subject' => $this,
                'id' => $variant->getId(),
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
    private function isCustomerGroupAllowed($variant, $customerGroup)
    {
        if ($variant->getArticle()->getCustomerGroups()->contains($customerGroup)) {
            return false;
        }

        return true;
    }

    /**
     * Helper function to create a new basket item.
     * The function expects an array with basket data.
     * All parameters of the addProduct function are also available here.
     *
     * @param array $data
     *
     * @return int the inserted basket id
     */
    private function createItem(array $data)
    {
        $basket = new Basket();
        $basket->fromArray($data);

        $this->modelManager->clear();
        $this->modelManager->persist($basket);
        $this->modelManager->flush();

        return $basket->getId();
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
                'code' => BundleBasketInterface::FAILURE_NO_VALID_ORDER_NUMBER,
                'message' => $this->snippetManager->getNamespace('frontend/checkout')->get(
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
                'code' => BundleBasketInterface::FAILURE_BOT_SESSION,
                'message' => $this->snippetManager->getNamespace('frontend/checkout')->get(
                    'bot_session',
                    'You are identified as bot!'
                ),
            ],
        ];
    }

    /**
     * Helper function to create an array result with success false and
     * the error "The add article process aborted over the Shopware_Modules_Basket_AddArticle_Start event.".
     *
     * @return array
     */
    private function getAddArticleStartFailure()
    {
        return [
            'success' => false,
            'error' => [
                'code' => BundleBasketInterface::FAILURE_ADD_PRODUCT_START_EVENT,
                'message' => $this->snippetManager->getNamespace('frontend/checkout')->get(
                    'notify_until_add_article_start',
                    'The add product process aborted over the Shopware_Modules_Basket_AddArticle_Start event'
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
                'code' => BundleBasketInterface::FAILURE_NOT_ENOUGH_STOCK,
                'message' => $this->snippetManager->getNamespace('frontend/checkout')->get(
                    'not_enough_stock',
                    'Not enough product stock!'
                ),
            ],
        ];
    }
}
