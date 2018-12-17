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

namespace SwagEmotionAdvanced\Bundle\EmotionBundle\ComponentHandler;

use Shopware\Bundle\EmotionBundle\ComponentHandler\ComponentHandlerInterface;
use Shopware\Bundle\EmotionBundle\Struct\Collection\PrepareDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\Sorting\PriceSorting;
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Shopware\Components\ProductStream\RepositoryInterface;
use Shopware_Components_Config as ShopwareConfig;

class SideViewComponentHandler implements ComponentHandlerInterface
{
    const SORTING_CHEAPEST_PRICE = 'price_asc';
    const SORTING_HIGHEST_PRICE = 'price_desc';
    const SORTING_TOP_SELLER = 'topseller';
    const SORTING_NEWCOMER = 'newcomer';
    const TYPE_SELECTED_PRODUCTS = 'selected_products';
    const TYPE_SELECTED_VARIANTS = 'selected_variants';
    const TYPE_PRODUCT_STREAM = 'product_stream';

    const COMPONENT_NAME = 'emotion-sideview-widget';

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var RepositoryInterface
     */
    private $productStreamRepository;

    /**
     * @var StoreFrontCriteriaFactoryInterface
     */
    private $criteriaFactory;

    /**
     * @var AdditionalTextServiceInterface
     */
    private $additionalTextService;

    /**
     * @var ShopwareConfig
     */
    private $shopwareConfig;

    /**
     * @var LegacyStructConverter
     */
    private $structConverter;

    /**
     * @param MediaServiceInterface              $mediaService
     * @param RepositoryInterface                $productStreamRepository
     * @param StoreFrontCriteriaFactoryInterface $criteriaFactory
     * @param LegacyStructConverter              $structConverter
     * @param AdditionalTextServiceInterface     $additionalTextService
     * @param ShopwareConfig                     $shopwareConfig
     */
    public function __construct(
        MediaServiceInterface $mediaService,
        RepositoryInterface $productStreamRepository,
        StoreFrontCriteriaFactoryInterface $criteriaFactory,
        LegacyStructConverter $structConverter,
        AdditionalTextServiceInterface $additionalTextService,
        ShopwareConfig $shopwareConfig
    ) {
        $this->mediaService = $mediaService;
        $this->productStreamRepository = $productStreamRepository;
        $this->criteriaFactory = $criteriaFactory;
        $this->structConverter = $structConverter;
        $this->additionalTextService = $additionalTextService;
        $this->shopwareConfig = $shopwareConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Element $element)
    {
        return $element->getComponent()->getType() === self::COMPONENT_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $mediaPathNormalized = $this->mediaService->normalize($element->getConfig()->get('sideview_banner'));
        $collection->addMediaPaths([$mediaPathNormalized]);

        $type = $element->getConfig()->get('sideview_product_type');
        $key = 'emotion-element--' . $element->getId();

        switch ($type) {
            case self::TYPE_PRODUCT_STREAM:
                $criteria = $this->generateCriteria($element, $context);

                $productStreamId = $element->getConfig()->get('sideview_stream_selection');
                $this->productStreamRepository->prepareCriteria($criteria, $productStreamId);

                // request multiple products by criteria
                $collection->getBatchRequest()->setCriteria($key, $criteria);
                break;

            case self::SORTING_TOP_SELLER:
            case self::SORTING_NEWCOMER:
            case self::SORTING_CHEAPEST_PRICE:
            case self::SORTING_HIGHEST_PRICE:
                $criteria = $this->generateCriteria($element, $context);

                // request multiple products by criteria
                $collection->getBatchRequest()->setCriteria($key, $criteria);
                break;

            case self::TYPE_SELECTED_PRODUCTS:
                $products = $element->getConfig()->get('sideview_selectedproducts', '|');
                $productNumbers = array_filter(explode('|', $products));

                $collection->getBatchRequest()->setProductNumbers($key, $productNumbers);
                break;
            case self::TYPE_SELECTED_VARIANTS:
                $products = $element->getConfig()->get('sideview_selectedvariants', '|');
                $productNumbers = array_filter(explode('|', $products));

                $collection->getBatchRequest()->setProductNumbers($key, $productNumbers);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $type = $element->getConfig()->get('sideview_product_type');
        $key = 'emotion-element--' . $element->getId();

        $this->setBannerData($collection, $element);

        switch ($type) {
            case self::TYPE_PRODUCT_STREAM:
            case self::SORTING_NEWCOMER:
            case self::SORTING_TOP_SELLER:
            case self::SORTING_HIGHEST_PRICE:
            case self::SORTING_CHEAPEST_PRICE:
                $requestedProducts = $collection->getBatchResult()->get($key);
                $requestedProductsArray = $this->structConverter->convertListProductStructList($requestedProducts);
                $element->getData()->set('product_data', $requestedProductsArray);
                break;

            case self::TYPE_SELECTED_PRODUCTS:
                $products = $element->getConfig()->get('sideview_selectedproducts', '|');
                $productNumbers = array_filter(explode('|', $products));
                $listProducts = $collection->getBatchResult()->get($key);

                $products = [];
                foreach ($productNumbers as $productNumber) {
                    if (!array_key_exists($productNumber, $listProducts) || !$listProducts[$productNumber]) {
                        continue;
                    }
                    $products[$productNumber] = $listProducts[$productNumber];
                }

                $productsArray = $this->structConverter->convertListProductStructList($products);
                $element->getData()->set('product_data', $productsArray);
                break;
            case self::TYPE_SELECTED_VARIANTS:
                $products = $element->getConfig()->get('sideview_selectedvariants', '|');
                $productNumbers = array_filter(explode('|', $products));
                $listProducts = $collection->getBatchResult()->get($key);
                $listProducts = $this->additionalTextService->buildAdditionalTextLists($listProducts, $context);

                $products = [];
                foreach ($productNumbers as $productNumber) {
                    if (!array_key_exists($productNumber, $listProducts) || !$listProducts[$productNumber]) {
                        continue;
                    }

                    /** @var ListProduct $product */
                    $product = $listProducts[$productNumber];
                    $this->switchPrice($product);
                    $products[$productNumber] = $product;
                }

                $productsArray = $this->structConverter->convertListProductStructList($products);
                $element->getData()->set('product_data', $productsArray);
                break;
        }
    }

    /**
     * @param ResolvedDataCollection $collection
     * @param Element                $element
     */
    private function setBannerData(ResolvedDataCollection $collection, Element $element)
    {
        $mediaPathNormalized = $this->mediaService->normalize($element->getConfig()->get('sideview_banner'));
        $media = $collection->getMediaByPath($mediaPathNormalized);
        $mediaArray = $this->structConverter->convertMediaStruct($media);

        $element->getData()->set('banner_data', $mediaArray);
    }

    /**
     * @param Element              $element
     * @param ShopContextInterface $context
     *
     * @return \Shopware\Bundle\SearchBundle\Criteria
     */
    private function generateCriteria(Element $element, ShopContextInterface $context)
    {
        $type = $element->getConfig()->get('sideview_product_type');
        $limit = (int) $element->getConfig()->get('sideview_max_products');
        $categoryId = (int) $element->getConfig()->get('sideview_category_id');

        if ($type === self::TYPE_PRODUCT_STREAM) {
            $categoryId = $context->getShop()->getCategory()->getId();
        }

        $criteria = $this->criteriaFactory->createBaseCriteria([$categoryId], $context);
        $criteria->limit($limit);

        switch ($type) {
            case self::SORTING_CHEAPEST_PRICE:
                $criteria->addSorting(new PriceSorting(SortingInterface::SORT_ASC));
                break;
            case self::SORTING_HIGHEST_PRICE:
                $criteria->addSorting(new PriceSorting(SortingInterface::SORT_DESC));
                break;
            case self::SORTING_TOP_SELLER:
                $criteria->addSorting(new PopularitySorting(SortingInterface::SORT_DESC));
                break;
            case self::SORTING_NEWCOMER:
                $criteria->addSorting(new ReleaseDateSorting(SortingInterface::SORT_DESC));
                break;
        }

        return $criteria;
    }

    /**
     * @param ListProduct $product
     */
    private function switchPrice(ListProduct $product)
    {
        $prices = array_values($product->getPrices());
        $product->setListingPrice($prices[0]);

        $product->setDisplayFromPrice(count($product->getPrices()) > 1);

        if ($this->shopwareConfig->get('useLastGraduationForCheapestPrice')) {
            $product->setListingPrice(
                $prices[count($prices) - 1]
            );
        }
    }
}
