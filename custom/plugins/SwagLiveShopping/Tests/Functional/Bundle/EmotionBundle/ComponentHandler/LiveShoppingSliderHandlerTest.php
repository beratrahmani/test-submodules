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

namespace SwagLiveShopping\Tests\Unit\Bundle\EmotionBundle\ComponentHandler;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\EmotionBundle\Struct\Collection\PrepareDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\EmotionBundle\Struct\ElementConfig;
use Shopware\Bundle\EmotionBundle\Struct\Library\Component;
use Shopware\Bundle\SearchBundle\BatchProductSearchResult;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\Tax;
use SwagLiveShopping\Bundle\EmotionBundle\ComponentHandler\LiveShoppingSliderHandler;
use SwagLiveShopping\Tests\KernelTestCaseTrait;

class LiveShoppingSliderHandlerTest extends TestCase
{
    use KernelTestCaseTrait;

    public function test_supports()
    {
        $handler = $this->getComponentHandler();
        $element = $this->getElement(LiveShoppingSliderHandler::COMPONENT_TYPE);

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

    public function test_prepare()
    {
        $handler = $this->getComponentHandler();
        $prepareDataCollection = new PrepareDataCollection();
        $element = $this->getElement(LiveShoppingSliderHandler::COMPONENT_TYPE);
        $element->getConfig()->set('number_products', 5);
        $shopContext = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $handler->prepare($prepareDataCollection, $element, $shopContext);

        $criteriaList = $prepareDataCollection->getBatchRequest()->getCriteriaList();
        $this->assertArrayHasKey('emotion-element--999', $criteriaList);

        /** @var Criteria $criteria */
        $criteria = $criteriaList['emotion-element--999'];

        $this->assertSame(5, $criteria->getLimit());
        $this->assertTrue($criteria->hasBaseCondition('live_shopping'));
    }

    public function test_handle()
    {
        $handler = $this->getComponentHandler();
        $resolvedDataCollection = $this->getResolvedDataCollection();
        $element = $this->getElement(LiveShoppingSliderHandler::COMPONENT_TYPE);
        $element->getConfig()->set('show_arrows', true);
        $element->getConfig()->set('rotate_automatically', false);
        $element->getConfig()->set('scroll_speed', 1234);
        $element->getConfig()->set('rotation_speed', 5678);
        $shopContext = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $handler->handle($resolvedDataCollection, $element, $shopContext);

        $data = $element->getData()->getAll();

        $this->assertArrayHasKey('values', $data);

        $productData = $data['values']['SW1000'];

        $this->assertSame(1, $productData['articleID']);
        $this->assertSame(11, $productData['articleDetailsID']);
        $this->assertSame('SW1000', $productData['ordernumber']);

        $this->assertSame(3, $data['pages']);
        $this->assertSame('', $data['ajaxFeed']);
        $this->assertSame('selected_article', $data['article_slider_type']);
        $this->assertTrue($data['article_slider_arrows']);
        $this->assertFalse($data['article_slider_rotation']);
        $this->assertSame(1234, $data['article_slider_scrollspeed']);
        $this->assertSame(5678, $data['article_slider_rotatespeed']);
    }

    /**
     * @return LiveShoppingSliderHandler
     */
    private function getComponentHandler()
    {
        return new LiveShoppingSliderHandler(
            Shopware()->Container()->get('shopware_search.store_front_criteria_factory'),
            Shopware()->Container()->get('legacy_struct_converter')
        );
    }

    /**
     * @param string $componentType
     *
     * @return Element
     */
    private function getElement($componentType)
    {
        $component = new Component();
        $component->setType($componentType);

        $config = new ElementConfig();

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

        $batchResult = new BatchProductSearchResult(['emotion-element--999' => $this->getListProductArray()]);
        $collection->setBatchResult($batchResult);

        return $collection;
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
