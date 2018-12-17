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

use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Country\Country;
use Shopware\Models\Customer\Group;
use Shopware\Models\Shop\Shop;
use SwagLiveShopping\Models\LiveShopping as LiveShoppingModel;
use SwagLiveShopping\Models\Price;

/**
 * Used for all LiveShopping resource specified processes.
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class LiveShopping implements LiveShoppingInterface
{
    /**
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * @var \Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * @var DependencyProvider
     */
    protected $dependencyProvider;

    /**
     * @var PriceServiceInterface
     */
    private $priceService;

    /**
     * @param ModelManager                $modelManager
     * @param \Enlight_Event_EventManager $eventManager
     * @param DependencyProvider          $dependencyProvider
     * @param PriceServiceInterface       $priceService
     */
    public function __construct(
        ModelManager $modelManager,
        \Enlight_Event_EventManager $eventManager,
        DependencyProvider $dependencyProvider,
        PriceServiceInterface $priceService
    ) {
        $this->modelManager = $modelManager;
        $this->eventManager = $eventManager;
        $this->dependencyProvider = $dependencyProvider;
        $this->priceService = $priceService;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveLiveShoppingForProduct($productId, Detail $variant = null)
    {
        $builder = $this->modelManager->getRepository(LiveShoppingModel::class)
            ->getActiveLiveShoppingForProductQueryBuilder(
                $productId,
                $variant,
                $this->getCurrentCustomerGroup(),
                $this->dependencyProvider->getShop()
            );

        $builder->setFirstResult(0)
            ->setMaxResults(1);

        $query = $builder->getQuery();

        $query->setHydrationMode(AbstractQuery::HYDRATE_OBJECT);

        $paginator = $this->modelManager->createPaginator($query);

        $liveShopping = $paginator->getIterator()->current();

        if (!$liveShopping instanceof LiveShoppingModel) {
            return false;
        }

        $now = new \DateTime();

        /* @var LiveShoppingModel $liveShopping */
        $liveShopping = $this->getLiveShoppingWithGrossPrices($liveShopping);

        $currentPrice = $this->priceService->getLiveShoppingPrice(
            $liveShopping->getId(),
            $liveShopping->getType(),
            $now,
            $liveShopping->getValidFrom(),
            $liveShopping->getValidTo()
        );

        if ($currentPrice === false) {
            return false;
        }

        $liveShopping->setCurrentPrice($currentPrice);

        /* @var Detail $product */
        $product = $this->modelManager->find(Detail::class, $productId);
        $liveShopping->setReferenceUnitPrice(
            $this->getReferenceUnitPriceForLiveShopping($liveShopping, $product)
        );

        return $liveShopping;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveLiveShoppingForVariant(Detail $variant)
    {
        $builder = $this->modelManager->getRepository(LiveShoppingModel::class)
            ->getActiveLiveShoppingForProductQueryBuilder(
                $variant->getArticle()->getId(),
                null,
                $this->getCurrentCustomerGroup(),
                $this->dependencyProvider->getShop()
            );

        $query = $builder->getQuery();

        $query->setHydrationMode(AbstractQuery::HYDRATE_OBJECT);

        $paginator = $this->modelManager->createPaginator($query);

        $liveShopping = null;
        foreach ($paginator->getIterator() as $activeLiveShopping) {
            if ($this->isVariantAllowed($activeLiveShopping, $variant)) {
                $liveShopping = $activeLiveShopping;
                break;
            }
        }
        if (!$liveShopping instanceof LiveShoppingModel) {
            return false;
        }

        $now = new \DateTime();

        /* @var LiveShoppingModel $liveShopping */
        $liveShopping = $this->getLiveShoppingWithGrossPrices($liveShopping);

        $currentPrice = $this->priceService->getLiveShoppingPrice(
            $liveShopping->getId(),
            $liveShopping->getType(),
            $now,
            $liveShopping->getValidFrom(),
            $liveShopping->getValidTo()
        );

        if ($currentPrice === false) {
            return false;
        }

        $liveShopping->setCurrentPrice($currentPrice);

        $liveShopping->setReferenceUnitPrice(
            $this->getReferenceUnitPriceForLiveShopping($liveShopping, $variant)
        );

        return $liveShopping;
    }

    /**
     * {@inheritdoc}
     */
    public function validateLiveShopping($liveShopping)
    {
        if (!$liveShopping instanceof LiveShoppingModel) {
            return ['noLiveShoppingDetected' => true];
        }

        if (!$liveShopping->getActive()) {
            return ['noMoreActive' => true, 'article' => $this->getLiveShoppingProductName($liveShopping)];
        }

        if (!$this->isCustomerGroupAllowed($liveShopping, $this->getCurrentCustomerGroup())) {
            return [
                'notForCurrentCustomerGroup' => true,
                'article' => $this->getLiveShoppingProductName($liveShopping),
            ];
        }

        if (!$this->hasLiveShoppingPriceForCustomerGroup($liveShopping, $this->getCurrentCustomerGroup())) {
            return [
                'notForCurrentCustomerGroup' => true,
                'article' => $this->getLiveShoppingProductName($liveShopping),
            ];
        }

        if ($liveShopping->getLimited() && $liveShopping->getQuantity() <= 0) {
            return ['noStock' => true, 'article' => $this->getLiveShoppingProductName($liveShopping)];
        }

        if (!$this->isShopAllowed($liveShopping, $this->dependencyProvider->getShop())) {
            return ['notForShop' => true, 'article' => $this->getLiveShoppingProductName($liveShopping)];
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceUnitPriceForLiveShopping(LiveShoppingModel $liveShopping, $product = null)
    {
        if ($product === null) {
            $product = $liveShopping->getArticle()->getMainDetail();
        }

        $referencePrice = 0.00;
        if ($product->getPurchaseUnit() > 0 && $product->getReferenceUnit()) {
            $referencePrice = $liveShopping->getCurrentPrice() / $product->getPurchaseUnit() * $product->getReferenceUnit();
            $referencePrice = round($referencePrice, 2);
        }

        $referencePrice = (float) str_replace(',', '.', $referencePrice);

        return $referencePrice;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveLiveShoppingById($liveShoppingId)
    {
        $builder = $this->modelManager->getRepository(LiveShoppingModel::class)
            ->getActiveLiveShoppingByIdQueryBuilder(
                (int) $liveShoppingId,
                $this->getCurrentCustomerGroup(),
                $this->dependencyProvider->getShop()
            );

        $builder->setFirstResult(0)
            ->setMaxResults(1);

        $liveShopping = $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_OBJECT
        );

        if (!$liveShopping instanceof LiveShoppingModel) {
            return false;
        }

        $now = new \DateTime();

        /* @var LiveShoppingModel $liveShopping */
        $liveShopping = $this->getLiveShoppingWithGrossPrices($liveShopping);

        $currentPrice = $this->priceService->getLiveShoppingPrice(
            $liveShopping->getId(),
            $liveShopping->getType(),
            $now,
            $liveShopping->getValidFrom(),
            $liveShopping->getValidTo()
        );

        if ($currentPrice === false) {
            return false;
        }

        $liveShopping->setCurrentPrice($currentPrice);

        $product = $this->getProductByLiveShopping($liveShopping);
        $liveShopping->setReferenceUnitPrice(
            $this->getReferenceUnitPriceForLiveShopping($liveShopping, $product)
        );

        return $liveShopping;
    }

    /**
     * {@inheritdoc}
     */
    public function getLiveShoppingArrayData($liveShopping)
    {
        if (!$liveShopping instanceof LiveShoppingModel) {
            return [];
        }

        /* @var Price $price */
        $price = $liveShopping->getUpdatedPrices()->first();

        $remainDateTimeInterval = $liveShopping->getRemainingDateInterval();
        if ($remainDateTimeInterval === false) {
            $this->eventManager->notify('Shopware_Plugins_HttpCache_InvalidateCacheId', ['cacheId' => 'a' . $liveShopping->getId()]);

            return false;
        }

        return [
            'id' => $liveShopping->getId(),
            'name' => $liveShopping->getName(),
            'type' => $liveShopping->getType(),
            'number' => $liveShopping->getNumber(),
            'remaining' => $this->getDateIntervalArrayData($remainDateTimeInterval),
            'expired' => $this->getDateIntervalArrayData($liveShopping->getExpiredDateInterval()),
            'startPrice' => $price->getPrice(),
            'endPrice' => $price->getEndPrice(),
            'currentPrice' => $liveShopping->getCurrentPrice(),
            'percentage' => 100 - $liveShopping->getCurrentPrice() * 100 / $price->getPrice(),
            'perMinute' => $liveShopping->getPerMinuteValue(),
            'limited' => $liveShopping->getLimited(),
            'quantity' => $liveShopping->getQuantity(),
            'sells' => $liveShopping->getSells(),
            'validTo' => $liveShopping->getValidTo() ? $liveShopping->getValidTo()->getTimestamp() : null,
            'referenceUnitPrice' => $liveShopping->getReferenceUnitPrice(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentTaxRate(Article $product)
    {
        $taxRate = $product->getTax()->getTax();

        if ($this->dependencyProvider->hasShop()) {
            $taxRate = $this->dependencyProvider->getModule('Articles')->getTaxRateByConditions(
                $product->getTax()->getId()
            );
        }

        return $taxRate;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentCurrencyFactor()
    {
        $currencyFactor = $this->dependencyProvider->getShop()->getCurrency()->getFactor();
        if (empty($currencyFactor)) {
            $currencyFactor = 1;
        }

        return $currencyFactor;
    }

    /**
     * {@inheritdoc}
     */
    public function displayNetPrices()
    {
        $isCustomerGroupNet = $this->isCustomerGroupNet();
        if ($isCustomerGroupNet === true) {
            return true;
        }

        return $this->isShippingCountryNet();
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Will be removed in 4.0.0. Use function displayNetPrices() instead.
     */
    public function useNetPriceInBasket()
    {
        return $this->displayNetPrices();
    }

    /**
     * {@inheritdoc}
     */
    public function getLiveShoppingProductName($liveShopping)
    {
        if (!$liveShopping instanceof LiveShoppingModel) {
            return '';
        }

        $product = $liveShopping->getArticle();

        $translation = $this->dependencyProvider->getModule('Articles')->sGetArticleNameByOrderNumber($product->getId(), true);

        if (!empty($translation['articleName'])) {
            return $translation['articleName'];
        }

        return $product->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function isLiveShoppingDateActive($liveShopping)
    {
        if (!$liveShopping instanceof LiveShoppingModel) {
            return false;
        }

        $now = new \DateTime();

        if ($liveShopping->getValidFrom() > $now) {
            return false;
        }

        if ($liveShopping->getValidTo() < $now) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getBasketLiveShoppingProducts()
    {
        $builder = $this->modelManager
            ->getRepository(LiveShoppingModel::class)
            ->getBasketAttributesWithLiveShoppingFlagQueryBuilder(
                $this->dependencyProvider->getSession()->get('sessionId')
            );

        $basket = $builder->getQuery()->getArrayResult();

        $liveShoppings = [];
        foreach ($basket as $item) {
            if (!empty($item['swagLiveShoppingId'])) {
                $liveShoppings[$item['orderBasketId']] = $this->modelManager->getRepository(LiveShoppingModel::class)->find($item['swagLiveShoppingId']);
            }
        }

        return $liveShoppings;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductByLiveShopping(LiveShoppingModel $liveShopping)
    {
        $productId = $this->dependencyProvider->getFrontendController()->Request()->getParam('productId');

        if ($productId !== null) {
            /* @var Detail $variant */
            $variant = $this->modelManager->find(Detail::class, $productId);

            if ($this->isVariantAllowed($liveShopping, $variant)) {
                return $variant;
            }
        }

        //check whether the main detail is part of limited variants group
        $mainVariant = $liveShopping->getArticle()->getMainDetail();

        if ($this->isVariantAllowed($liveShopping, $mainVariant)) {
            return $mainVariant;
        }

        $limitedVariants = $liveShopping->getLimitedVariants();
        if ($limitedVariants->count() === 0) {
            return $mainVariant;
        }

        //if the main detail is not part of limited variants group, get first allowed variant
        foreach ($limitedVariants as $limitedVariant) {
            if ($limitedVariant instanceof Detail) {
                return $limitedVariant;
            }

            continue;
        }

        return $mainVariant;
    }

    /**
     * {@inheritdoc}
     */
    public function decreaseLiveShoppingStock(LiveShoppingModel $liveShopping, $quantity = 1)
    {
        $sql = 'UPDATE s_articles_lives
            SET max_quantity = max_quantity - :quantity
            WHERE s_articles_lives.id = :liveshoppingId
        ';

        $stmt = $this->modelManager->getConnection()->prepare($sql);
        $stmt->bindValue('quantity', $quantity);
        $stmt->bindValue('liveshoppingId', $liveShopping->getId());

        $stmt->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function isVariantAllowed(LiveShoppingModel $liveShopping, Detail $variant)
    {
        if ($liveShopping->getLimitedVariants()->count() === 0) {
            return true;
        }

        foreach ($liveShopping->getLimitedVariants() as $limitedVariant) {
            if (!($limitedVariant instanceof Detail)) {
                continue;
            }
            if ($limitedVariant->getNumber() === $variant->getNumber()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getLiveShoppingByNumber($number)
    {
        $repo = $this->modelManager->getRepository(Detail::class);
        $selectedVariant = $repo->findOneBy(['number' => $number]);
        $liveShopping = $this->getActiveLiveShoppingForVariant($selectedVariant);

        if (!$liveShopping instanceof LiveShoppingModel) {
            return false;
        }

        /** @var array $data */
        $data = $this->getLiveShoppingArrayData($liveShopping);

        if (empty($data)) {
            return false;
        }

        $date = new \DateTime();
        if ($date->getTimestamp() > $data['validTo']) {
            return false;
        }

        return [$data, $liveShopping];
    }

    /**
     * {@inheritdoc}
     */
    public function haveVariantsLiveShopping(array $products, ShopContextInterface $context)
    {
        $connection = $this->modelManager->getConnection();
        $query = $connection->createQueryBuilder();

        $query->select(['variant.ordernumber', 'variant.id']);
        $query->from('s_articles_lives', 'liveShopping');
        $query->innerJoin('liveShopping', 's_articles_details', 'variant', 'variant.articleID = liveShopping.article_id AND variant.id IN (:variantIds)');
        $query->innerJoin('liveShopping', 's_articles_live_customer_groups', 'customerGroup', 'customerGroup.live_shopping_id = liveShopping.id AND customerGroup.customer_group_id = :customerGroupId');
        $query->leftJoin('liveShopping', 's_articles_live_shoprelations', 'shops', 'shops.live_shopping_id = liveShopping.id AND shops.shop_id = :shopId');
        $query->andWhere('liveShopping.active = 1');
        $query->andWhere('(liveShopping.max_quantity_enable = 0 OR liveShopping.max_quantity_enable = 1 AND liveShopping.max_quantity > 0)');
        $query->andWhere('liveShopping.valid_to >= :validTo');
        $query->andWhere('liveShopping.valid_from <= :validFrom');

        $now = new \DateTime();
        $query->setParameter('validTo', $now->format('Y-m-d H:i:00'));
        $query->setParameter('validFrom', $now->format('Y-m-d H:i:00'));
        $query->setParameter('customerGroupId', $context->getCurrentCustomerGroup()->getId());
        $query->setParameter('shopId', $context->getShop()->getId());

        // listing doesn't support currently the limitedVariants - PT-6937
        // $query->addSelect("GROUP_CONCAT(limitedVariants.article_detail_id SEPARATOR '|') as limited");
        // $query->leftJoin('liveShopping', 's_articles_live_stint', 'limitedVariants', 'limitedVariants.live_shopping_id = liveShopping.id');

        $variantIds = array_map(function (ListProduct $product) {
            return $product->getVariantId();
        }, $products);

        $query->setParameter(':variantIds', $variantIds, Connection::PARAM_INT_ARRAY);
        $mapping = $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);

        return array_map(function ($limited) {
            return explode('|', $limited);
        }, $mapping);
    }

    /**
     * Internal helper function to get the current customer group for the customer
     * or the default customer group of the current shop.
     *
     * @return Group
     */
    private function getCurrentCustomerGroup()
    {
        $customerGroup = null;
        if ($this->dependencyProvider->hasShop()
            && $this->dependencyProvider->hasSession()
        ) {
            $session = $this->dependencyProvider->getSession();
            $customerGroupData = $session->get('sUserGroupData');

            /* @var Group $customerGroup */
            //check if the customer logged in and get the customer group model for the logged in customer
            if (!empty($customerGroupData['groupkey'])) {
                $repo = $this->modelManager->getRepository(Group::class);
                $customerGroup = $repo->findOneBy(['key' => $customerGroupData['groupkey']]);
            }
        }

        //if no customer group given, get the default customer group.
        if (!$customerGroup instanceof Group) {
            $customerGroup = $this->dependencyProvider->getShop()->getCustomerGroup();
        }

        return $customerGroup;
    }

    /**
     * Array mapping function.
     *
     * Returns the passed date interval object as array data.
     *
     * @param \DateInterval $dateInterval
     *
     * @return array
     */
    private function getDateIntervalArrayData($dateInterval)
    {
        return [
            'days' => sprintf('%02d', $dateInterval->days),
            'hours' => sprintf('%02d', $dateInterval->h),
            'minutes' => sprintf('%02d', $dateInterval->i),
            'seconds' => sprintf('%02d', $dateInterval->s),
        ];
    }

    /**
     * Formats the live shopping prices.
     *
     * This function iterates the prices of the passed live shopping
     * object and sets the right values for gross and net prices.
     *
     * @param LiveShoppingModel $liveShopping
     *
     * @return LiveShoppingModel
     */
    private function getLiveShoppingWithGrossPrices(LiveShoppingModel $liveShopping)
    {
        $prices = [];

        $taxRate = $this->getCurrentTaxRate(
            $liveShopping->getArticle()
        );

        $currencyFactor = $this->getCurrentCurrencyFactor();

        /* @var Price $price */
        foreach ($liveShopping->getPrices() as $price) {
            if (!$price->getCustomerGroup() instanceof Group) {
                continue;
            }
            $price->setPrice(
                (float) $price->getPrice() * (float) $currencyFactor
            );
            $price->setEndPrice(
                (float) $price->getEndPrice() * (float) $currencyFactor
            );

            if (!$this->displayNetPrices()) {
                $price->setPrice(
                    $price->getPrice() / 100 * (100 + $taxRate)
                );
                $price->setEndPrice(
                    $price->getEndPrice() / 100 * (100 + $taxRate)
                );
            }
            $prices[] = $price;
        }

        $liveShopping->getUpdatedPrices()->clear();
        foreach ($prices as $price) {
            $liveShopping->getUpdatedPrices()->add($price);
        }
        $this->modelManager->clear();

        return $liveShopping;
    }

    /**
     * Helper function to check if the selected country would be delivered with net prices.
     *
     * @return bool
     */
    private function isShippingCountryNet()
    {
        if (!$this->dependencyProvider->hasSession()) {
            return false;
        }

        $session = $this->dependencyProvider->getSession();
        $country = (int) $session->get('sCountry', 0);
        $country = $this->modelManager->find(Country::class, $country);

        if (!$country) {
            return false;
        }

        /** @var \sAdmin $adminModule */
        $adminModule = $this->dependencyProvider->getModule('admin');

        /** @var array $userData */
        $userData = $adminModule->sGetUserData();

        if (!isset($userData['shippingaddress'])) {
            return (bool) $country->getTaxFree();
        }

        $shippingAddress = $userData['shippingaddress'];

        return (bool) $country->getTaxFree() || ((bool) $country->getTaxFreeUstId() && !empty($shippingAddress['vatId']));
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
     * Helper function to check if the passed customer group has a defined price.
     *
     * This function is used to validate the basket live shopping products for the current customer group.
     * It returns false if the passed live shopping product has no price for the passed customer group.
     *
     * @param LiveShoppingModel $liveShopping
     * @param Group             $customerGroup Group
     *
     * @return bool
     */
    private function hasLiveShoppingPriceForCustomerGroup(LiveShoppingModel $liveShopping, Group $customerGroup)
    {
        /* @var Price $price */
        foreach ($liveShopping->getPrices() as $price) {
            if ($price->getCustomerGroup()->getId() === $customerGroup->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper function to validate shop limitations.
     *
     * This function is used to check if the passed live shopping product
     * contains the passed shop object.
     *
     * @param LiveShoppingModel $liveShopping
     * @param Shop              $shop
     *
     * @return bool
     */
    private function isShopAllowed(LiveShoppingModel $liveShopping, Shop $shop)
    {
        foreach ($liveShopping->getShops() as $liveShoppingShop) {
            if ($liveShoppingShop->getId() === $shop->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper function to check if the passed customer group is allowed for the passed live shopping product.
     *
     * @param LiveShoppingModel $liveShopping
     * @param Group             $customerGroup
     *
     * @return bool
     */
    private function isCustomerGroupAllowed(LiveShoppingModel $liveShopping, Group $customerGroup)
    {
        /* @var Group $customerGroupBundle */
        foreach ($liveShopping->getCustomerGroups() as $customerGroupBundle) {
            if ($customerGroup->getKey() === $customerGroupBundle->getKey()) {
                return true;
            }
        }

        return false;
    }
}
