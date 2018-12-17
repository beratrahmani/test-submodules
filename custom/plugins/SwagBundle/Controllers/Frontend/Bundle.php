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

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\QueryAliasMapper;
use SwagBundle\Bundle\SearchBundle\Condition\BundleCondition;

/**
 * @category Shopware
 *
 * @copyright Copyright (c), shopware AG (http://en.shopware.com)
 */
class Shopware_Controllers_Frontend_Bundle extends Enlight_Controller_Action
{
    /**
     * Listing page for bundle products
     */
    public function indexAction()
    {
        $this->View()->assign($this->getListingDataResponsive());
    }

    /**
     * Helper method to collect all necessary information for the responsive-listing.
     *
     * @return array
     */
    private function getListingDataResponsive()
    {
        $page = (int) $this->Request()->getParam('sPage', 1);
        $sSort = $this->Request()->getParam('sSort', 1);
        /** @var QueryAliasMapper $mapper */
        $mapper = $this->get('query_alias_mapper');
        $mapper->replaceShortRequestQueries($this->Request());

        /** @var ContextServiceInterface $contextService */
        $contextService = $this->get('shopware_storefront.context_service');
        $context = $contextService->getShopContext();

        /** @var Criteria $criteria */
        $criteria = $this->get('shopware_search.store_front_criteria_factory')->createListingCriteria($this->Request(), $context);
        $criteria->addBaseCondition(new BundleCondition());
        $criteria->removeFacet('bundle');

        /** @var ProductSearchResult $searchResult */
        $searchResult = $this->get('shopware_search.product_search')->search($criteria, $context);

        $products = $this->get('legacy_struct_converter')->convertListProductStructList($searchResult->getProducts());

        if ($this->container->has('swag_liveshopping.live_shopping')) {
            $products = array_map([$this, 'addLiveShoppingData'], $products);
        }

        $total = $searchResult->getTotalCount();

        /** @var \Shopware\Bundle\StoreFrontBundle\Service\CustomSortingServiceInterface $customSortingService */
        $customSortingService = $this->get('shopware_storefront.custom_sorting_service');
        $sortings = $customSortingService->getAllCategorySortings($context);

        return [
            'facets' => $searchResult->getFacets(),
            'criteria' => $criteria,
            'hasEmotion' => false,
            'shortParameters' => $this->get('query_alias_mapper')->getQueryAliases(),
            'ajaxCountUrlParams' => [
                'bundle_base' => 1,
                'sCategory' => $context->getShop()->getCategory()->getId(),
            ],
            'sortings' => $sortings,
            'products' => $products,
            'sSort' => $sSort,
            'pageSizes' => explode('|', $this->get('config')->get('numberArticlesToShow')),
            'sPage' => $page,
            'sNumberArticles' => $total,
        ];
    }

    /**
     * Helper method to get the live-shopping-data for each product.
     * It is only executed when the live-shopping plugin is installed and active.
     *
     * @param array $product
     *
     * @return mixed
     */
    private function addLiveShoppingData($product)
    {
        $liveShoppingComponent = $this->get('swag_liveshopping.live_shopping');
        $liveShopping = $liveShoppingComponent->getActiveLiveShoppingForProduct($product['articleID']);

        if (!$liveShopping instanceof \SwagLiveShopping\Models\LiveShopping) {
            return $product;
        }

        $product['liveShopping'] = $liveShoppingComponent->getLiveShoppingArrayData($liveShopping);
        $product['price'] = $this->get('modules')->Articles()->sFormatPrice($liveShopping->getCurrentPrice());

        return $product;
    }
}
