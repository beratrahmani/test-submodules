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

namespace SwagBundle\Services;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Media\Media;
use Shopware\Models\Order\Basket;
use SwagBundle\Components\BundleComponentInterface;
use SwagBundle\Models\Article as BundleProduct;
use SwagBundle\Models\Bundle;
use SwagBundle\Services\Calculation\BundlePriceCalculatorInterface;
use SwagBundle\Services\Calculation\Validation\BundleLastStockValidatorInterface;
use SwagBundle\Services\Dependencies\ProviderInterface;
use SwagBundle\Services\Discount\BundleDiscountServiceInterface;
use SwagBundle\Services\Products\LongestShippingTimeInspectorInterface;
use SwagBundle\Services\Products\ProductPriceServiceInterface;
use SwagBundle\Services\Products\ProductSelectionServiceInterface;

class FullBundleService implements FullBundleServiceInterface
{
    /**
     * @var ProviderInterface
     */
    private $dependenciesProvider;

    /**
     * @var CustomerGroupServiceInterface
     */
    private $customerGroupService;

    /**
     * @var BundleValidationServiceInterface
     */
    private $bundleValidationService;

    /**
     * @var ProductSelectionServiceInterface
     */
    private $productSelectionService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ProductPriceServiceInterface
     */
    private $productPriceService;

    /**
     * @var BundleLastStockValidatorInterface
     */
    private $bundleLastStockValidator;

    /**
     * @var \Shopware\Components\Model\ModelRepository
     */
    private $mediaRepository;

    /**
     * @var \Shopware\Models\Media\Album
     */
    private $productAlbum;

    /**
     * @var BundlePriceCalculatorInterface
     */
    private $bundlePriceCalculator;

    /**
     * @var BundleDiscountServiceInterface
     */
    private $bundleDiscountService;

    /**
     * @var LongestShippingTimeInspectorInterface
     */
    private $longestShippingTimeInspector;

    /**
     * @var BundleMainProductServiceInterface
     */
    private $bundleMainProductService;

    /**
     * @param ProviderInterface                     $dependenciesProvider
     * @param CustomerGroupServiceInterface         $customerGroupService
     * @param BundleValidationServiceInterface      $bundleValidationService
     * @param ProductSelectionServiceInterface      $productSelectionService
     * @param Connection                            $connection
     * @param ProductPriceServiceInterface          $productPriceService
     * @param BundleLastStockValidatorInterface     $bundleLastStockValidator
     * @param ModelManager                          $modelManager
     * @param BundlePriceCalculatorInterface        $bundlePriceCalculator
     * @param BundleDiscountServiceInterface        $bundleDiscountService
     * @param LongestShippingTimeInspectorInterface $longestShippingTimeInspector
     * @param BundleMainProductServiceInterface     $bundleMainProductService
     */
    public function __construct(
        ProviderInterface $dependenciesProvider,
        CustomerGroupServiceInterface $customerGroupService,
        BundleValidationServiceInterface $bundleValidationService,
        ProductSelectionServiceInterface $productSelectionService,
        Connection $connection,
        ProductPriceServiceInterface $productPriceService,
        BundleLastStockValidatorInterface $bundleLastStockValidator,
        ModelManager $modelManager,
        BundlePriceCalculatorInterface $bundlePriceCalculator,
        BundleDiscountServiceInterface $bundleDiscountService,
        LongestShippingTimeInspectorInterface $longestShippingTimeInspector,
        BundleMainProductServiceInterface $bundleMainProductService
    ) {
        $this->dependenciesProvider = $dependenciesProvider;
        $this->customerGroupService = $customerGroupService;
        $this->bundleValidationService = $bundleValidationService;
        $this->productSelectionService = $productSelectionService;
        $this->connection = $connection;
        $this->productPriceService = $productPriceService;
        $this->bundleLastStockValidator = $bundleLastStockValidator;
        $this->bundlePriceCalculator = $bundlePriceCalculator;
        $this->bundleDiscountService = $bundleDiscountService;
        $this->longestShippingTimeInspector = $longestShippingTimeInspector;
        $this->bundleMainProductService = $bundleMainProductService;

        $this->mediaRepository = $modelManager->getRepository(Media::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getCalculatedBundle(
        Bundle $bundle,
        $productNumber = '',
        $isBasket = false,
        Basket $basketItem = null
    ) {
        //initial the bundle specify internal properties.
        $products = [];
        $totalPrice = ['net' => 0, 'gross' => 0];
        $allConfigured = true;

        //to calculate the whole bundle price and check if the whole bundle is configured,
        //we have to fake an additional bundle product position with the bundle main product.
        //The bundle main product will not be returned as bundle position.
        $bundleProducts = [];
        $bundleProducts[] = $this->bundleMainProductService->getBundleMainProduct($bundle, $productNumber);
        $bundleProducts = array_merge($bundleProducts, $bundle->getArticles()->getValues());

        $validation = $this->bundleValidationService->validateBundle($bundle);
        if (is_array($validation) && $validation['success'] === false) {
            return $validation;
        }

        //get the current customer group for the customer or the default customer group of the current shop.
        $customerGroup = $this->customerGroupService->getCurrentCustomerGroup();
        $sArticleCoreModule = $this->dependenciesProvider->getArticlesModule();

        //iterate all bundle products to get the product data.
        /** @var BundleProduct $bundleProduct */
        foreach ($bundleProducts as $bundleProduct) {
            $noProductInStock = false;
            if (!$bundleProduct instanceof BundleProduct) {
                continue;
            }

            //the get product selection returns the product configuration groups and
            //options and checks if the product was already configured.
            //Additionally the function returns the product variant for the current configurator selection.
            $session = $this->dependenciesProvider->getSession();
            $productSelection = $this->productSelectionService->getConfiguration(
                $bundleProduct,
                $session->get('bundleConfiguration', [])
            );

            /** @var Detail $selectedVariant */
            //get the selected variant.
            $selectedVariant = $productSelection['selectedVariant'];

            if ($isBasket) {
                if (!$basketItem) {
                    throw new \RuntimeException('Parameter $basketItem missing.');
                }

                if (!$this->isProductByNumberInBundlePackage(
                    $selectedVariant->getNumber(),
                    $basketItem->getAttribute()->getBundlePackageId()
                )) {
                    continue;
                }
            }

            //get the configurator groups and options
            $configuration = $productSelection['configuration'];

            $sql = 'SELECT option_id
                    FROM `s_article_configurator_option_relations`
                    WHERE `article_id` = ?';
            $optionIds = $this->connection->executeQuery($sql, [$selectedVariant->getId()])->fetchAll(\PDO::FETCH_COLUMN);

            $configurationGroups = [];
            if (isset($configuration['groups'])) {
                $configurationGroups = $configuration['groups'];
            }

            foreach ($configurationGroups as $key => &$configGroup) {
                if (!$configGroup['selected']) {
                    $configGroup['selected'] = $optionIds[$key];
                }
            }
            unset($configGroup);

            //check if the product is configured
            if (!$productSelection['isConfigured']) {
                $allConfigured = false;
            }

            //get net and gross price for the selected variant and the current customer group.
            $prices = $this->productPriceService->getProductPrices($selectedVariant, $customerGroup, 1);

            //check if a price was found.
            if ($prices === false) {
                return [
                    'success' => false,
                    'bundle' => $bundle->getName(),
                    'article' => $selectedVariant->getNumber(),
                    'noPrice' => true,
                ];
            }

            if (!$basketItem && $this->bundleLastStockValidator->validate($selectedVariant, $bundle) === false) {
                if (!$bundleProduct->getConfigurable()
                    && ((int) $bundle->getType() === BundleComponentInterface::NORMAL_BUNDLE
                        || $bundleProduct->getBundleId() === null) // if the bundleId is not set, it's the main product of the bundle
                ) {
                    return [
                        'success' => false,
                        'bundle' => $bundle->getName(),
                        'article' => $selectedVariant->getNumber(),
                        'noProductInStock' => true,
                    ];
                }

                $noProductInStock = true;
            }

            //check if the product and the selected variant is set to active.
            if (!$selectedVariant->getActive() || !$selectedVariant->getArticle()->getActive()) {
                if ((int) $bundle->getType() === BundleComponentInterface::SELECTABLE_BUNDLE) {
                    $noProductInStock = true;
                } else {
                    return [
                        'success' => false,
                        'bundle' => $bundle->getName(),
                        'article' => $selectedVariant->getNumber(),
                        'notActive' => true,
                    ];
                }
            }

            //check if the customer group can buy the product
            if ($selectedVariant->getArticle()->getCustomerGroups()->contains($customerGroup)) {
                return [
                    'success' => false,
                    'bundle' => $bundle->getName(),
                    'article' => $selectedVariant->getNumber(),
                    'articleNotForCustomerGroup' => true,
                ];
            }

            //get the display price for the selected variant.
            $productPrice = [
                'display' => $sArticleCoreModule->sFormatPrice($prices['net']),
                'numeric' => $prices['net'],
                'total' => $prices['net'] * $bundleProduct->getQuantity(),
            ];

            if (!$this->displayNetPrices()) {
                $productPrice = [
                    'display' => $sArticleCoreModule->sFormatPrice($prices['gross']),
                    'numeric' => $prices['gross'],
                    'total' => $prices['gross'] * $bundleProduct->getQuantity(),
                ];
            }

            $productData = [
                'noProductInStock' => $noProductInStock,
                'bundleArticleId' => $bundleProduct->getId(), //we have to set the original variant id as identification
                'articleId' => $selectedVariant->getArticle()->getId(),
                'name' => $selectedVariant->getArticle()->getName(),
                'quantity' => $bundleProduct->getQuantity(),
                'number' => $selectedVariant->getNumber(),
                'additionalText' => $selectedVariant->getAdditionalText(),
                'supplier' => $selectedVariant->getArticle()->getSupplier()->getName(),
                'description' => $selectedVariant->getArticle()->getDescription(),
                'description_long' => $selectedVariant->getArticle()->getDescriptionLong(),
                'isConfigurable' => $bundleProduct->getConfigurable() && $bundleProduct->getId() > 0,
                'isConfigured' => $productSelection['isConfigured'],
                'cover' => $this->getProductCover($selectedVariant),
                'price' => $productPrice,
                'basePrice' => $this->productPriceService->getBasePriceOfVariant($selectedVariant),
                'configuration' => $configurationGroups,
                'sReleaseDate' => $selectedVariant->getArticle()->getMainDetail()->getReleaseDate(),
                'esd' => $selectedVariant->getArticle()->getMainDetail()->getEsd(),
                'instock' => $selectedVariant->getArticle()->getMainDetail()->getInStock(),
                'shippingtime' => $selectedVariant->getArticle()->getMainDetail()->getShippingTime(),
                'shippingfree' => $selectedVariant->getShippingFree(),
                'attributes' => $this->getProductAttributes($selectedVariant),
            ];

            $productData = $sArticleCoreModule->sGetTranslation($productData, $selectedVariant->getArticle()->getId(), 'article');
            $productData['descriptionLong'] = $productData['description_long'];
            unset($productData['description_long']);

            $products[] = $productData;

            $totalPrice['net'] += ($prices['net'] * $bundleProduct->getQuantity());
            $totalPrice['gross'] += ($prices['gross'] * $bundleProduct->getQuantity());
        }

        $this->longestShippingTimeInspector->determineLongestShippingProduct($products, $bundle);

        //format price
        $totalPrice['display'] = $sArticleCoreModule->sFormatPrice($totalPrice['net']);
        if (!$this->displayNetPrices()) {
            $totalPrice['display'] = $sArticleCoreModule->sFormatPrice($totalPrice['gross']);
        }

        //set the total prices into the bundle object.
        $bundle->setTotalPrice($totalPrice);

        //get the bundle prices
        $bundlePrices = $this->bundlePriceCalculator->getBundlePrices($bundle);
        if ($bundlePrices === false) {
            return [
                'success' => false,
                'bundle' => $bundle->getName(),
                'noPrices' => true,
            ];
        }

        //get the bundle price for the selected customer group.
        $currentPrice = $this->bundlePriceCalculator->getCurrentPrice($bundle, $customerGroup);
        if ($currentPrice === false) {
            return [
                'success' => false,
                'bundle' => $bundle->getName(),
                'noCustomerGroupPrice' => true,
            ];
        }

        //set the current price in the bundle object.
        $bundle->setCurrentPrice($currentPrice);

        //calculate the bundle discount for the passed bundle id.
        //Returns an array with discount data (gross/net prices) for the customer group of the current price property of the bundle
        $discount = $this->bundleDiscountService->getBundleDiscount($bundle);

        //set the discount data into the bundle object.
        $bundle->setDiscount($discount);

        //set the calculated product data into the model to have later access on it.
        $bundle->setProductData($products);

        //set the allConfigured flag into the model.
        $bundle->setAllConfigured($allConfigured);

        return $bundle;
    }

    /**
     * @return bool
     */
    private function displayNetPrices()
    {
        return !$this->customerGroupService->getCurrentCustomerGroup()->getTax();
    }

    /**
     * Checks if the product by the given number is in the same bundle-package as $packageId.
     *
     * @param string $number
     * @param int    $packageId
     *
     * @return bool
     */
    private function isProductByNumberInBundlePackage($number, $packageId)
    {
        if (!$number || !$packageId) {
            return false;
        }

        $sql = 'SELECT basket.id
                FROM s_order_basket basket
                LEFT JOIN s_order_basket_attributes basketAttr ON basket.id = basketAttr.basketID
                WHERE basket.sessionID = ? AND basket.ordernumber = ? AND basketAttr.bundle_package_id = ?';

        $basketItem = $this->connection
            ->executeQuery($sql, [$this->dependenciesProvider->getSession()->get('sessionId'), $number, $packageId])
            ->fetchColumn();

        if ($basketItem) {
            return true;
        }

        return false;
    }

    /**
     * Internal helper function to get the cover of a product variant.
     *
     * @param Detail $productVariant
     *
     * @return array
     */
    private function getProductCover(Detail $productVariant)
    {
        return $this->dependenciesProvider->getArticlesModule()->getArticleCover(
            $productVariant->getArticle()->getId(),
            $productVariant->getNumber(),
            $this->getProductAlbum()
        );
    }

    /**
     * Internal helper function to get the product media album.
     * If this property is set to null the getter function selects
     * the default product album over the media model by using the
     * getAlbumWithSettingsQuery(-1) function.
     *
     * @return \Shopware\Models\Media\Album
     */
    private function getProductAlbum()
    {
        if ($this->productAlbum === null) {
            $this->productAlbum = $this->mediaRepository->getAlbumWithSettingsQuery(-1)->getOneOrNullResult();
        }

        return $this->productAlbum;
    }

    /**
     * @param Detail $selectedVariant
     *
     * @return array
     */
    private function getProductAttributes(Detail $selectedVariant)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select('*')
            ->from('s_articles_attributes')
            ->where('articledetailsID = :variantId')
            ->setParameter('variantId', $selectedVariant->getId());

        $attributes = $builder->execute()->fetchAll();

        return array_shift($attributes);
    }
}
