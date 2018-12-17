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

namespace SwagEmotionAdvanced\tests\Functional\Bundle\EmotionBundle\ComponentHandler;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\EmotionBundle\Struct\Collection\PrepareDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\EmotionBundle\Struct\ElementConfig;
use Shopware\Bundle\EmotionBundle\Struct\Library\Component;
use Shopware\Bundle\SearchBundle\BatchProductSearchResult;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\Sorting\PriceSorting;
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Media;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\Tax;
use SwagEmotionAdvanced\Bundle\EmotionBundle\ComponentHandler\SideViewComponentHandler;
use SwagEmotionAdvanced\tests\KernelTestCaseTrait;

class SideViewComponentHandlerTest extends TestCase
{
    use KernelTestCaseTrait;

    public function test_supports()
    {
        $handler = $this->getComponentHandler();
        $element = $this->getElement(SideViewComponentHandler::COMPONENT_NAME);

        $result = $handler->supports($element);

        $this->assertTrue($result);
    }

    public function test_supports_fails()
    {
        $handler = $this->getComponentHandler();
        $element = $this->getElement('fooBar');

        $result = $handler->supports($element);

        $this->assertFalse($result);
    }

    public function test_prepare_productStream()
    {
        $handler = $this->getComponentHandler();
        $prepareDataCollection = new PrepareDataCollection();
        $element = $this->getElement(SideViewComponentHandler::COMPONENT_NAME, SideViewComponentHandler::TYPE_PRODUCT_STREAM);
        $element->getConfig()->set('sideview_stream_selection', 1);
        $element->getConfig()->set('sideview_max_products', 10);
        $shopContext = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $handler->prepare($prepareDataCollection, $element, $shopContext);

        $criteriaList = $prepareDataCollection->getBatchRequest()->getCriteriaList();
        $this->assertArrayHasKey('emotion-element--999', $criteriaList);

        /** @var Criteria $criteria */
        $criteria = $criteriaList['emotion-element--999'];

        $this->assertTrue($criteria->hasBaseCondition('category'));
        /** @var CategoryCondition $categoryCondition */
        $categoryCondition = $criteria->getBaseCondition('category');

        $this->assertSame(10, $criteria->getLimit());
        $this->assertSame(3, $categoryCondition->getCategoryIds()[0]);
    }

    public function test_prepare_topSeller()
    {
        $handler = $this->getComponentHandler();
        $prepareDataCollection = new PrepareDataCollection();
        $element = $this->getElement(SideViewComponentHandler::COMPONENT_NAME, SideViewComponentHandler::SORTING_TOP_SELLER);
        $element->getConfig()->set('sideview_category_id', 77);
        $element->getConfig()->set('sideview_max_products', 10);
        $shopContext = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $handler->prepare($prepareDataCollection, $element, $shopContext);

        $criteriaList = $prepareDataCollection->getBatchRequest()->getCriteriaList();
        $this->assertArrayHasKey('emotion-element--999', $criteriaList);

        /** @var Criteria $criteria */
        $criteria = $criteriaList['emotion-element--999'];

        $this->assertTrue($criteria->hasBaseCondition('category'));
        /** @var CategoryCondition $categoryCondition */
        $categoryCondition = $criteria->getBaseCondition('category');

        $this->assertTrue($criteria->hasSorting('popularity'));
        /** @var PopularitySorting $popularitySorting */
        $popularitySorting = $criteria->getSorting('popularity');

        $this->assertSame(10, $criteria->getLimit());
        $this->assertSame(77, $categoryCondition->getCategoryIds()[0]);
        $this->assertSame('DESC', $popularitySorting->getDirection());
    }

    public function test_prepare_newcommer()
    {
        $handler = $this->getComponentHandler();
        $prepareDataCollection = new PrepareDataCollection();
        $element = $this->getElement(SideViewComponentHandler::COMPONENT_NAME, SideViewComponentHandler::SORTING_NEWCOMER);
        $element->getConfig()->set('sideview_category_id', 77);
        $element->getConfig()->set('sideview_max_products', 10);
        $shopContext = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $handler->prepare($prepareDataCollection, $element, $shopContext);

        $criteriaList = $prepareDataCollection->getBatchRequest()->getCriteriaList();
        $this->assertArrayHasKey('emotion-element--999', $criteriaList);

        /** @var Criteria $criteria */
        $criteria = $criteriaList['emotion-element--999'];

        $this->assertTrue($criteria->hasBaseCondition('category'));
        /** @var CategoryCondition $categoryCondition */
        $categoryCondition = $criteria->getBaseCondition('category');

        $this->assertTrue($criteria->hasSorting('release_date'));
        /** @var ReleaseDateSorting $releaseDateSorting */
        $releaseDateSorting = $criteria->getSorting('release_date');

        $this->assertSame(10, $criteria->getLimit());
        $this->assertSame(77, $categoryCondition->getCategoryIds()[0]);
        $this->assertSame('DESC', $releaseDateSorting->getDirection());
    }

    public function test_prepare_cheapestPrice()
    {
        $handler = $this->getComponentHandler();
        $prepareDataCollection = new PrepareDataCollection();
        $element = $this->getElement(SideViewComponentHandler::COMPONENT_NAME, SideViewComponentHandler::SORTING_CHEAPEST_PRICE);
        $element->getConfig()->set('sideview_category_id', 77);
        $element->getConfig()->set('sideview_max_products', 10);
        $shopContext = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $handler->prepare($prepareDataCollection, $element, $shopContext);

        $criteriaList = $prepareDataCollection->getBatchRequest()->getCriteriaList();
        $this->assertArrayHasKey('emotion-element--999', $criteriaList);

        /** @var Criteria $criteria */
        $criteria = $criteriaList['emotion-element--999'];

        $this->assertTrue($criteria->hasBaseCondition('category'));
        /** @var CategoryCondition $categoryCondition */
        $categoryCondition = $criteria->getBaseCondition('category');

        $this->assertTrue($criteria->hasSorting('prices'));
        /** @var PriceSorting $priceSorting */
        $priceSorting = $criteria->getSorting('prices');

        $this->assertSame(10, $criteria->getLimit());
        $this->assertSame(77, $categoryCondition->getCategoryIds()[0]);
        $this->assertSame('ASC', $priceSorting->getDirection());
    }

    public function test_prepare_highestPrice()
    {
        $handler = $this->getComponentHandler();
        $prepareDataCollection = new PrepareDataCollection();
        $element = $this->getElement(SideViewComponentHandler::COMPONENT_NAME, SideViewComponentHandler::SORTING_HIGHEST_PRICE);
        $element->getConfig()->set('sideview_category_id', 77);
        $element->getConfig()->set('sideview_max_products', 10);
        $shopContext = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $handler->prepare($prepareDataCollection, $element, $shopContext);

        $criteriaList = $prepareDataCollection->getBatchRequest()->getCriteriaList();
        $this->assertArrayHasKey('emotion-element--999', $criteriaList);

        /** @var Criteria $criteria */
        $criteria = $criteriaList['emotion-element--999'];

        $this->assertTrue($criteria->hasBaseCondition('category'));
        /** @var CategoryCondition $categoryCondition */
        $categoryCondition = $criteria->getBaseCondition('category');

        $this->assertTrue($criteria->hasSorting('prices'));
        /** @var PriceSorting $priceSorting */
        $priceSorting = $criteria->getSorting('prices');

        $this->assertSame(10, $criteria->getLimit());
        $this->assertSame(77, $categoryCondition->getCategoryIds()[0]);
        $this->assertSame('DESC', $priceSorting->getDirection());
    }

    public function test_prepare_products()
    {
        $handler = $this->getComponentHandler();
        $prepareDataCollection = new PrepareDataCollection();
        $element = $this->getElement(SideViewComponentHandler::COMPONENT_NAME, SideViewComponentHandler::TYPE_SELECTED_PRODUCTS);
        $element->getConfig()->set('sideview_selectedproducts', '|SW10003|SW10004|');
        $shopContext = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $handler->prepare($prepareDataCollection, $element, $shopContext);

        $batchProducts = $prepareDataCollection->getBatchRequest()->getProductNumbers();
        $this->assertArrayHasKey('emotion-element--999', $batchProducts);
        $products = $batchProducts['emotion-element--999'];
        $expectedProducts = [1 => 'SW10003', 2 => 'SW10004'];
        $this->assertArraySubset($expectedProducts, $products);
    }

    public function test_prepare_products_empty()
    {
        $handler = $this->getComponentHandler();
        $prepareDataCollection = new PrepareDataCollection();
        $element = $this->getElement(SideViewComponentHandler::COMPONENT_NAME, SideViewComponentHandler::TYPE_SELECTED_PRODUCTS);
        $shopContext = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $handler->prepare($prepareDataCollection, $element, $shopContext);

        $batchProducts = $prepareDataCollection->getBatchRequest()->getProductNumbers();
        $this->assertArrayHasKey('emotion-element--999', $batchProducts);
        $products = $batchProducts['emotion-element--999'];
        $expectedProducts = [];
        $this->assertArraySubset($expectedProducts, $products);
    }

    public function test_prepare_product_variants()
    {
        $handler = $this->getComponentHandler();
        $prepareDataCollection = new PrepareDataCollection();
        $element = $this->getElement(SideViewComponentHandler::COMPONENT_NAME, SideViewComponentHandler::TYPE_SELECTED_VARIANTS);
        $element->getConfig()->set('sideview_selectedvariants', '|SW10003|SW10004|');
        $shopContext = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $handler->prepare($prepareDataCollection, $element, $shopContext);

        $batchProducts = $prepareDataCollection->getBatchRequest()->getProductNumbers();
        $this->assertArrayHasKey('emotion-element--999', $batchProducts);
        $products = $batchProducts['emotion-element--999'];
        $expectedProducts = [1 => 'SW10003', 2 => 'SW10004'];
        $this->assertArraySubset($expectedProducts, $products);
    }

    public function test_prepare_product_variants_empty()
    {
        $handler = $this->getComponentHandler();
        $prepareDataCollection = new PrepareDataCollection();
        $element = $this->getElement(SideViewComponentHandler::COMPONENT_NAME, SideViewComponentHandler::TYPE_SELECTED_VARIANTS);
        $shopContext = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $handler->prepare($prepareDataCollection, $element, $shopContext);

        $batchProducts = $prepareDataCollection->getBatchRequest()->getProductNumbers();
        $this->assertArrayHasKey('emotion-element--999', $batchProducts);
        $products = $batchProducts['emotion-element--999'];
        $expectedProducts = [];
        $this->assertArraySubset($expectedProducts, $products);
    }

    public function test_handle_setBannerData()
    {
        $handler = $this->getComponentHandler();
        $element = $this->getElement(SideViewComponentHandler::COMPONENT_NAME);
        $element->getConfig()->set('sideview_banner', 'http://localhost/5.3/media/image/3b/75/35/genuss_tees_banner.jpg');
        $resolvedDataCollection = $this->getResolvedDataCollection();
        $shopContext = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $handler->handle($resolvedDataCollection, $element, $shopContext);

        $expectedBannerData = [
            'id' => 111,
            'source' => 'fooBar',
            'description' => 'test',
        ];

        $this->assertArrayHasKey('banner_data', $element->getData()->getAll());

        $bannerData = $element->getData()->get('banner_data');

        $this->assertArraySubset($expectedBannerData, $bannerData);
    }

    public function test_handle_productStream()
    {
        $handler = $this->getComponentHandler();
        $element = $this->getElement(SideViewComponentHandler::COMPONENT_NAME, SideViewComponentHandler::TYPE_PRODUCT_STREAM);
        $resolvedDataCollection = $this->getResolvedDataCollection();
        $shopContext = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $handler->handle($resolvedDataCollection, $element, $shopContext);

        $this->assertArrayHasKey('product_data', $element->getData()->getAll());

        $productData = $element->getData()->get('product_data')['SW1000'];

        $this->assertSame(1, $productData['articleID']);
        $this->assertSame(11, $productData['articleDetailsID']);
        $this->assertSame('SW1000', $productData['ordernumber']);
    }

    public function test_handle_selectedProducts()
    {
        $handler = $this->getComponentHandler();
        $element = $this->getElement(SideViewComponentHandler::COMPONENT_NAME, SideViewComponentHandler::TYPE_SELECTED_PRODUCTS);
        $element->getConfig()->set('sideview_selectedproducts', '|SW1000|SW10004|');
        $resolvedDataCollection = $this->getResolvedDataCollection();
        $shopContext = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $handler->handle($resolvedDataCollection, $element, $shopContext);

        $this->assertArrayHasKey('product_data', $element->getData()->getAll());

        $productData = $element->getData()->get('product_data')['SW1000'];

        $this->assertSame(1, $productData['articleID']);
        $this->assertSame(11, $productData['articleDetailsID']);
        $this->assertSame('SW1000', $productData['ordernumber']);
    }

    public function test_handle_selectedVariants()
    {
        $handler = $this->getComponentHandler();
        $element = $this->getElement(SideViewComponentHandler::COMPONENT_NAME, SideViewComponentHandler::TYPE_SELECTED_VARIANTS);
        $element->getConfig()->set('sideview_selectedvariants', '|SW1000|SW10004|');
        $resolvedDataCollection = $this->getResolvedDataCollection();
        $shopContext = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $handler->handle($resolvedDataCollection, $element, $shopContext);

        $this->assertArrayHasKey('product_data', $element->getData()->getAll());

        $productData = $element->getData()->get('product_data')['SW1000'];

        $this->assertSame(1, $productData['articleID']);
        $this->assertSame(11, $productData['articleDetailsID']);
        $this->assertSame('SW1000', $productData['ordernumber']);
    }

    public function test_handle_selectedVariants_with_price_config()
    {
        Shopware()->Config()->offsetSet('useLastGraduationForCheapestPrice', true);
        $handler = $this->getComponentHandler();
        $element = $this->getElement(SideViewComponentHandler::COMPONENT_NAME, SideViewComponentHandler::TYPE_SELECTED_VARIANTS);
        $element->getConfig()->set('sideview_selectedvariants', '|SW1000|SW10004|');
        $resolvedDataCollection = $this->getResolvedDataCollection();
        $shopContext = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $handler->handle($resolvedDataCollection, $element, $shopContext);

        $this->assertArrayHasKey('product_data', $element->getData()->getAll());

        $productData = $element->getData()->get('product_data')['SW1000'];

        $this->assertSame(1, $productData['articleID']);
        $this->assertSame(11, $productData['articleDetailsID']);
        $this->assertSame('SW1000', $productData['ordernumber']);
    }

    /**
     * @return SideViewComponentHandler
     */
    private function getComponentHandler()
    {
        return Shopware()->Container()->get('swag_emotion_advanced.emotion_bundle.side_view_component_handler');
    }

    /**
     * @param string $componentType
     * @param string $productType
     *
     * @return Element
     */
    private function getElement($componentType, $productType = '')
    {
        $component = new Component();
        $component->setType($componentType);

        $config = new ElementConfig();
        $config->set('sideview_product_type', $productType);

        $element = new Element();
        $element->setId(999);
        $element->setComponent($component);
        $element->setConfig($config);

        return $element;
    }

    /**
     * @return ResolvedDataCollection
     */
    private function getResolvedDataCollection()
    {
        $collection = new ResolvedDataCollection();

        $media = ['media/image/genuss_tees_banner.jpg' => $this->getMediaStruct()];
        $collection->setMediaList($media);
        $batchResult = new BatchProductSearchResult(['emotion-element--999' => $this->getListProductArray()]);
        $collection->setBatchResult($batchResult);

        return $collection;
    }

    /**
     * @return Media
     */
    private function getMediaStruct()
    {
        $media = new Media();

        $media->setId(111);
        $media->setName('test');
        $media->setFile('fooBar');

        return $media;
    }

    /**
     * @return ListProduct[]
     */
    private function getListProductArray()
    {
        $listProduct = new ListProduct(1, 11, 'SW1000');
        $listProduct->setTax(new Tax());
        $listProduct->setListingPrice($this->getPrice());
        $listProduct->setPrices([$this->getPrice()]);

        return ['SW1000' => $listProduct];
    }

    /**
     * @return Price
     */
    private function getPrice()
    {
        $priceRule = new PriceRule();
        $customerGroup = new Group();
        $priceRule->setCustomerGroup($customerGroup);

        return new Price($priceRule);
    }
}
