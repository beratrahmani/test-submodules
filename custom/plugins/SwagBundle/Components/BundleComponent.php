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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Attribute\OrderBasket;
use Shopware\Models\Order\Basket;
use SwagBundle\Models\Article as BundleProduct;
use SwagBundle\Models\Bundle;
use SwagBundle\Services\BundleMainProductServiceInterface;
use SwagBundle\Services\BundleValidationServiceInterface;
use SwagBundle\Services\Calculation\BundleBasketDiscountInterface;
use SwagBundle\Services\Dependencies\ProviderInterface;
use SwagBundle\Services\Discount\BundleDiscountServiceInterface;
use SwagBundle\Services\FullBundleServiceInterface;
use SwagBundle\Services\Products\ProductSelectionServiceInterface;

class BundleComponent implements BundleComponentInterface
{
    /**
     * @var \SwagBundle\Models\Repository
     */
    private $bundleRepository;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var ProviderInterface
     */
    private $dependenciesProvider;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var BundleBasketDiscountInterface
     */
    private $bundleBasketDiscount;

    /**
     * @var BundleBasketInterface
     */
    private $bundleBasket;

    /**
     * @var BundleValidationServiceInterface
     */
    private $bundleValidationService;

    /**
     * @var ProductSelectionServiceInterface
     */
    private $productSelectionService;

    /**
     * @var FullBundleServiceInterface
     */
    private $fullBundleService;

    /**
     * @var BundleDiscountServiceInterface
     */
    private $bundleDiscountService;

    /**
     * @var BundleMainProductServiceInterface
     */
    private $bundleMainProductService;

    /**
     * @param ModelManager                      $modelManager
     * @param ProviderInterface                 $dependenciesProvider
     * @param Connection                        $connection
     * @param BundleBasketDiscountInterface     $bundleBasketDiscount
     * @param BundleBasketInterface             $bundleBasket
     * @param BundleValidationServiceInterface  $bundleValidationService
     * @param ProductSelectionServiceInterface  $productSelectionService
     * @param FullBundleServiceInterface        $fullBundleService
     * @param BundleDiscountServiceInterface    $bundleDiscountService
     * @param BundleMainProductServiceInterface $bundleMainProductService
     */
    public function __construct(
        ModelManager $modelManager,
        ProviderInterface $dependenciesProvider,
        Connection $connection,
        BundleBasketDiscountInterface $bundleBasketDiscount,
        BundleBasketInterface $bundleBasket,
        BundleValidationServiceInterface $bundleValidationService,
        ProductSelectionServiceInterface $productSelectionService,
        FullBundleServiceInterface $fullBundleService,
        BundleDiscountServiceInterface $bundleDiscountService,
        BundleMainProductServiceInterface $bundleMainProductService
    ) {
        $this->modelManager = $modelManager;
        $this->dependenciesProvider = $dependenciesProvider;
        $this->connection = $connection;
        $this->bundleBasketDiscount = $bundleBasketDiscount;
        $this->bundleBasket = $bundleBasket;
        $this->bundleValidationService = $bundleValidationService;
        $this->productSelectionService = $productSelectionService;
        $this->fullBundleService = $fullBundleService;
        $this->bundleDiscountService = $bundleDiscountService;
        $this->bundleMainProductService = $bundleMainProductService;

        $this->bundleRepository = $modelManager->getRepository(Bundle::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBundlesForDetailPage($productId, $productNumber)
    {
        $now = new \DateTime();
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select('bundle')
                ->from(Bundle::class, 'bundle')
                ->innerJoin('bundle.articles', 'products')
                ->innerJoin('products.articleDetail', 'productDetail')
                ->where($builder->expr()->eq('productDetail.articleId', $productId))
                ->orWhere($builder->expr()->eq('bundle.articleId', $productId))
                ->andWhere('bundle.validTo >= :now OR bundle.validTo IS NULL')
                ->andWhere('bundle.validFrom <= :now  OR bundle.validFrom IS NULL')
                ->andWhere('bundle.active = 1')
                ->setParameter('now', $now->format('Y-m-d H:i:s'))
                ->orderBy('bundle.position', 'ASC');

        $bundles = $builder
            ->getQuery()
            ->setHydrationMode(AbstractQuery::HYDRATE_ARRAY)
            ->getResult();

        $data = [];
        $productHasBundle = false;
        $bundleNotAvailableForSelectedVariant = false;
        /** @var Bundle $bundle */
        foreach ($bundles as $bundle) {
            $productHasBundle = true;

            // bundle not displayed global
            if (!$bundle->getDisplayGlobal() && $productId !== (int) $bundle->getArticleId()) {
                continue;
            }

            $calculatedBundle = $this->fullBundleService->getCalculatedBundle($bundle, $productNumber);
            if (is_array($calculatedBundle) && $calculatedBundle['success'] === false) {
                // If a skipped product was skipped because of the selected variant, set the corresponding flag
                if (isset($calculatedBundle['notForSelectedVariant'])) {
                    $bundleNotAvailableForSelectedVariant = true;
                }

                continue;
            }

            $bundleData = $this->getArrayDataOfBundle($calculatedBundle);

            if (isset($bundleData['success']) && $bundleData['success'] === false) {
                continue;
            }

            $products = [];
            foreach ($bundleData['articles'] as $product) {
                if ((int) $product['articleId'] !== $productId) {
                    $products[] = $product['articleId'];
                }
            }

            //hide bundle if products in bundle not match to products in current sub shop
            if (count($products) > 0) {
                $isInShop = $this->productsInSubShop($products);
                if (!$isInShop) {
                    continue;
                }
            }

            $data[] = $bundleData;
        }

        if (empty($data)) {
            return $productHasBundle && $bundleNotAvailableForSelectedVariant;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function validateBundlesInBasket()
    {
        $bundles = $this->getBasketBundles();
        $validations = [];
        foreach ($bundles as $bundle) {
            if (!$bundle instanceof Bundle || !$bundle->getActive()) {
                $validations[] = [
                    'success' => false,
                    'deletedBundle' => true,
                ];
                continue;
            }
            $validation = $this->bundleValidationService->validateBundle($bundle);

            if (is_array($validation) && $validation['success'] === false) {
                $this->removeBasketBundle($bundle);
                $validations[] = $validation;
            }
        }

        return empty($validations) ? true : $validations;
    }

    /**
     * {@inheritdoc}
     */
    public function updateBundleBasketDiscount()
    {
        $session = $this->dependenciesProvider->getSession();
        $shop = $this->dependenciesProvider->getShop();

        $basketItems = $this->bundleRepository->getBundleBasketItemsBySessionId(
            $session->get('sessionId')
        );

        $this->bundleBasketDiscount->updateBundleBasketDiscount(
            $basketItems,
            $shop->getCurrency()->getFactor()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function removeBundleFromBasket(array $params)
    {
        $builder = $this->getBasketBuilder($params['bundle']);
        $builder->join('basket.attribute', 'attr')
            ->andWhere('attr.bundleArticleOrdernumber = :productNumber')
            ->andWhere('attr.bundlePackageId = :bundlePackageId')
            ->setParameter('productNumber', $params['productNumber'])
            ->setParameter('bundlePackageId', $params['bundlePackageId']);

        foreach ($builder->getQuery()->getResult() as $basket) {
            $this->modelManager->remove($basket);
        }

        $this->modelManager->flush();

        $this->removeBundleAttribute($params);
    }

    /**
     * {@inheritdoc}
     */
    public function addBundleToBasket($bundleId, array $selection = [])
    {
        if (!$selection instanceof ArrayCollection && is_array($selection)) {
            $selection = new ArrayCollection($selection);
        }

        $bundleId = (int) $bundleId;

        /** @var Bundle $bundle */
        $bundle = $this->modelManager->find(Bundle::class, $bundleId);

        if (!$bundle instanceof Bundle) {
            return ['success' => false, 'alreadyInBasket' => true];
        }

        if (empty($selection) && $bundle->getType() === BundleComponentInterface::SELECTABLE_BUNDLE) {
            return ['success' => false, 'noSelection' => true];
        }

        //get the calculated bundle data.
        $bundle = $this->fullBundleService->getCalculatedBundle($bundle);

        if (!$bundle instanceof Bundle) {
            return $bundle;
        }

        //get the product of the bundle as faked bundle position.
        $mainProduct = $this->bundleMainProductService->getBundleMainProduct($bundle);
        if (!$mainProduct instanceof BundleProduct) {
            return ['success' => false, 'noMainProduct' => true];
        }

        $session = $this->dependenciesProvider->getSession();
        $bundleConfiguration = $session->get('bundleConfiguration', []);
        //get the configuration for the main product
        $configuration = $this->productSelectionService->getConfiguration($mainProduct, $bundleConfiguration);

        /** @var Detail $selected */
        $selected = $configuration['selectedVariant'];

        $productNumber = $selected->getNumber();

        //add the main product to the basket
        $mainProductResult = $this->bundleBasket->addProduct(
            $selected->getNumber(),
            1,
            [
                'bundleId' => $bundle->getId(),
                'bundleArticleId' => 0,
                'bundleArticleOrdernumber' => $productNumber,
            ]
        );

        $bundlePackageId = (int) $mainProductResult['data']['id'];

        //update the bundle package id of the main product
        $this->connection->executeUpdate(
            'UPDATE s_order_basket_attributes SET bundle_package_id = :bundlePackageId WHERE basketID = :bundlePackageId',
            ['bundlePackageId' => $bundlePackageId]
        );

        //iterate all bundle positions and add them to the shopware basket

        /** @var BundleProduct $bundleProduct */
        foreach ($bundle->getArticles() as $bundleProduct) {
            //get the configuration for the bundle position
            $configuration = $this->productSelectionService->getConfiguration(
                $bundleProduct,
                $bundleConfiguration
            );

            /** @var Detail $selected */
            $selected = $configuration['selectedVariant'];

            //a selectable bundle can be configured by the customer.
            //we have to check if the current bundle product was selected
            //the selected products are passed in the "$selection" parameter.
            if ($bundle->getType() === BundleComponentInterface::SELECTABLE_BUNDLE
                && !$selection->contains($bundleProduct)
            ) {
                continue;
            }

            //check if a variant was selected
            if ($selected instanceof Detail) {
                $productResult = $this->bundleBasket->addProduct(
                    $selected->getNumber(),
                    $bundleProduct->getQuantity(),
                    [
                        'bundleId' => $bundle->getId(),
                        'bundleArticleId' => $bundleProduct->getId(),
                        'bundleArticleOrdernumber' => $productNumber,
                        'bundlePackageId' => $bundlePackageId,
                    ]
                );

                if ($productResult['success'] === false) {
                    $this->removeBasketBundle($bundle);

                    return $productResult;
                }
            }
        }

        return $this->bundleDiscountService->insertBundleDiscountInCart(
            $bundle,
            $selection,
            $productNumber,
            $bundlePackageId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isVariantAsBundleInBasket($number)
    {
        $session = $this->dependenciesProvider->getSession();
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['basket'])
            ->from(Basket::class, 'basket')
            ->innerJoin('basket.attribute', 'attribute')
            ->where('attribute.bundleId > :bundleId')
            ->andWhere('basket.sessionId = :sessionId')
            ->andWhere('basket.orderNumber = :orderNumber')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->setParameter('sessionId', $session->get('sessionId'))
            ->setParameter('bundleId', 0)
            ->setParameter('orderNumber', $number);

        $basket = $builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        if ($basket instanceof Basket) {
            return $basket->getId();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isVariantAsNormalInBasket($number)
    {
        $session = $this->dependenciesProvider->getSession();
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['basket'])
            ->from(Basket::class, 'basket')
            ->innerJoin('basket.attribute', 'attribute')
            ->where('attribute.bundleId IS NULL')
            ->andWhere('basket.sessionId = :sessionId')
            ->andWhere('basket.orderNumber = :orderNumber')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->setParameter('sessionId', $session->get('sessionId'))
            ->setParameter('orderNumber', $number);

        $basket = $builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        if ($basket instanceof Basket) {
            return $basket->getId();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function decreaseBundleStock($bundleId)
    {
        $query = $this->connection->createQueryBuilder();
        $query->update('s_articles_bundles')
            ->set('max_quantity', 'max_quantity - 1')
            ->where('id = :bundleId')
            ->setParameter('bundleId', $bundleId)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getBasketBuilder(Bundle $bundle)
    {
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['basket'])
            ->from(Basket::class, 'basket')
            ->where('basket.orderNumber = :number')
            ->andWhere('basket.sessionId = :sessionId')
            ->setParameter('number', $bundle->getNumber())
            ->setParameter('sessionId', $this->dependenciesProvider->getSession()->get('sessionId'));

        return $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function getBasketAttributeBuilder(Bundle $bundle)
    {
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['attribute'])
            ->from(OrderBasket::class, 'attribute')
            ->innerJoin('attribute.orderBasket', 'basket')
            ->where('basket.sessionId = :sessionId')
            ->andWhere('attribute.bundleId = :bundleId')
            ->setParameter('bundleId', $bundle->getId())
            ->setParameter('sessionId', $this->dependenciesProvider->getSession()->get('sessionId'));

        return $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function clearBasketFromDeletedBundles()
    {
        $sessionId = $this->dependenciesProvider->getSession()->get('sessionId');
        $sql = 'SELECT basket.ordernumber AS orderNumber, attributes.bundle_id AS bundleId
                FROM s_order_basket AS basket
                INNER JOIN s_order_basket_attributes AS attributes ON (basket.id = attributes.basketID)
                WHERE basket.modus = :mode AND basket.sessionID = :sessionId';

        $basketItems = $this->connection
            ->executeQuery($sql, [
                'mode' => BundleBasketInterface::BUNDLE_DISCOUNT_BASKET_MODE,
                'sessionId' => $sessionId,
            ])
            ->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($basketItems as $item) {
            if (empty($item['orderNumber']) || empty($item['bundleId'])) {
                continue;
            }

            $bundle = $this->bundleRepository->find($item['bundleId']);
            if (!$bundle instanceof Bundle || !$bundle->getActive()) {
                $sql = 'DELETE FROM s_order_basket WHERE sessionID = ? AND ordernumber = ?';
                $this->connection->executeUpdate($sql, [$sessionId, $item['orderNumber']]);

                $sql = 'UPDATE s_order_basket_attributes AS attributes
                        INNER JOIN s_order_basket AS basket ON (basket.id = attributes.basketID)
                        SET attributes.bundle_id = NULL
                        WHERE basket.sessionID = ? AND attributes.bundle_id = ?';
                $this->connection->executeUpdate($sql, [$sessionId, $item['bundleId']]);
            }
        }
    }

    /**
     * Search all products in current sub shop and compare number of products to results
     *
     * @param array $products
     *
     * @return bool
     */
    private function productsInSubShop(array $products)
    {
        $products = array_unique($products);

        $mainCategoryId = $this->dependenciesProvider->getShop()->getCategory()->getId();

        $this->modelManager->getDBALQueryBuilder();
        $builder = $this->connection->createQueryBuilder();
        $builder->select('product.name')
                ->from('s_articles', 'product')
                ->innerJoin('product', 's_articles_categories_ro', 'ac', 'ac.articleID = product.id AND ac.categoryID = :categoryId')
                ->innerJoin('product', 's_categories', 'c', 'c.id = ac.categoryID AND c.active = 1')
                ->where($builder->expr()->in('product.id', $products))
                ->groupBy('product.id')
                ->setParameter('categoryId', $mainCategoryId);

        /** @var \Doctrine\DBAL\Driver\Statement $statement */
        $statement = $builder->execute();
        $productsAmount = (int) $statement->rowCount();

        return count($products) === $productsAmount;
    }

    /**
     * Helper function to remove the passed bundle flags from the basket.
     *
     * @param Bundle $bundle
     */
    private function removeBasketBundle(Bundle $bundle)
    {
        $builder = $this->getBasketBuilder($bundle);

        foreach ($builder->getQuery()->getResult() as $basket) {
            $this->modelManager->remove($basket);
        }

        $this->modelManager->flush();
        $this->removeBundleFlagOfBasket($bundle);
    }

    /**
     * Helper method to remove the bundle flag and product order number from
     * basket attributes. All bundle positions will be converted to normal product
     *
     * @param array $params
     */
    private function removeBundleAttribute(array $params)
    {
        $this->modelManager->clear();

        $builder = $this->getBasketAttributeBuilder($params['bundle']);
        $builder->andWhere('attribute.bundleArticleOrdernumber = :productNumber')
            ->andWhere('attribute.bundlePackageId = :bundlePackageId')
            ->setParameter('productNumber', $params['productNumber'])
            ->setParameter('bundlePackageId', $params['bundlePackageId']);
        $attributes = $builder->getQuery()->getResult();

        /** @var \Shopware\Models\Attribute\OrderBasket $attribute */
        foreach ($attributes as $attribute) {
            $attribute->setBundleId(null);
            $attribute->setBundleArticleOrdernumber(null);
            $attribute->setBundlePackageId(null);
        }
        $this->modelManager->flush();
    }

    /**
     * Helper function to remove the bundle ids from all basket positions for the
     * current session id. This function called if the customer removes
     * a bundle positions. All bundle positions will be converted to normal product
     * positions.
     *
     * @param Bundle $bundle
     */
    private function removeBundleFlagOfBasket(Bundle $bundle)
    {
        $this->modelManager->clear();

        $builder = $this->getBasketAttributeBuilder($bundle);

        $attributes = $builder->getQuery()->getResult();

        /** @var \Shopware\Models\Attribute\OrderBasket $attribute */
        foreach ($attributes as $attribute) {
            $attribute->setBundleId(null);
        }
        $this->modelManager->flush();
    }

    /**
     * Helper function to get all basket bundles.
     *
     * @return array
     */
    private function getBasketBundles()
    {
        $sql = 'SELECT basket.ordernumber
                FROM s_order_basket AS basket
                  INNER JOIN s_articles_bundles AS bundle
                    ON basket.ordernumber = bundle.ordernumber
                WHERE basket.modus = :mode
                  AND basket.sessionID = :sessionId';

        $discounts = $this->connection
            ->executeQuery($sql, [
                'mode' => BundleBasketInterface::BUNDLE_DISCOUNT_BASKET_MODE,
                'sessionId' => $this->dependenciesProvider->getSession()->get('sessionId'),
            ])
            ->fetchAll(\PDO::FETCH_COLUMN);

        $bundles = [];

        foreach ($discounts as $discount) {
            if (!empty($discount)) {
                $bundles[] = $this->bundleRepository->findOneBy([
                    'number' => $discount,
                ]);
            }
        }

        return $bundles;
    }

    /**
     * Method to convert the passed bundle model into
     * an array.
     * This helper function is used for the product detail page in the store front.
     *
     * @param Bundle $bundle
     *
     * @return array
     */
    private function getArrayDataOfBundle(Bundle $bundle)
    {
        $totalPrice = $bundle->getTotalPrice();

        $bundlePrice = $bundle->getCurrentPrice()->getNetPrice();
        if ($bundle->getCurrentPrice()->getCustomerGroup()->getTax()) {
            $bundlePrice = $bundle->getCurrentPrice()->getGrossPrice();
        }

        return [
            'id' => $bundle->getId(),
            'mainProductId' => $bundle->getArticleId(),
            'name' => $bundle->getName(),
            'showName' => $bundle->getShowName(),
            'number' => $bundle->getNumber(),
            'discountType' => $bundle->getDiscountType(),
            'description' => $this->getTranslations($bundle->getId(), $bundle->getDescription()),
            'type' => $bundle->getType(),
            'displayDelivery' => $bundle->getDisplayDelivery(),
            'allConfigured' => $bundle->getAllConfigured(),
            'limited' => $bundle->getLimited(),
            'quantity' => $bundle->getQuantity(),
            'price' => [
                'display' => $bundle->getCurrentPrice()->getDisplayPrice(),
                'numeric' => $bundlePrice,
            ],
            'valid' => [
                'from' => $bundle->getValidFrom(),
                'to' => $bundle->getValidTo(),
            ],
            'totalPrice' => $totalPrice['display'],
            'discount' => $bundle->getDiscount(),
            'articles' => $bundle->getProductData(),
            'longestShippingTimeProduct' => $bundle->getLongestShippingTimeProduct(),
        ];
    }

    /**
     * Search for bundle translation in current language
     * if no translation found return base
     *
     * @param int    $key
     * @param string $data
     *
     * @return string
     */
    private function getTranslations($key, $data)
    {
        $shop = $this->dependenciesProvider->getShop();
        if (empty($data) || $shop->get('skipbackend')) {
            return $data;
        }

        $language = $shop->getId();

        $sql = "
            SELECT s.objectdata
            FROM s_core_translations s
            WHERE
                s.objecttype = 'bundle-description'
                AND s.objectkey = ?
                AND s.objectlanguage = ?
        ";

        $translation = $this->connection->executeQuery($sql, [$key, $language])->fetch(\PDO::FETCH_COLUMN);
        if (empty($translation)) {
            return $data;
        }

        $description = unserialize($translation);

        return $description['description'];
    }
}
