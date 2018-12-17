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
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use SwagLiveShopping\Bundle\SearchBundle\Condition\LiveShoppingCondition;
use SwagLiveShopping\Components\LiveShopping;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class Shopware_Controllers_Frontend_LiveShopping extends Enlight_Controller_Action
{
    /**
     * Live shopping component of this plugin.
     *
     * The live shopping component of shopware is used for all live shopping
     * processes within shopware.
     * The component calculates the current product price and validates live shopping
     * products for the current frontend session.
     *
     * @var LiveShopping
     */
    protected $liveShoppingComponent;

    /**
     * Standard index action of the live shopping frontend controller
     */
    public function indexAction()
    {
        $defaultSort = 0;
        $defaultTemplate = 'table';

        $configReader = $this->container->get('shopware.plugin.cached_config_reader');
        $config = $configReader->getByPluginName('SwagLiveShopping', $this->get('shop'));

        /** @var ShopContextInterface $context */
        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        /** @var Criteria $criteria */
        $criteria = $this->get('shopware_search.store_front_criteria_factory')
            ->createListingCriteria($this->Request(), $context);

        $criteria->addBaseCondition(new LiveShoppingCondition());
        $criteria->removeFacet('live_shopping');
        $criteria->removeFacet('price');

        /** @var ProductSearchResult $searchResult */
        $searchResult = $this->get('shopware_search.product_search')->search($criteria, $context);

        /** @var LegacyStructConverter $converter */
        $converter = $this->get('legacy_struct_converter');

        $products = $converter->convertListProductStructList($searchResult->getProducts());

        $service = $this->get('shopware_storefront.custom_sorting_service');
        $sortings = $service->getAllCategorySortings($context);

        $data = $this->getListingConfiguration($searchResult, $defaultTemplate, $defaultSort, $config);
        $data = array_merge(
            $data,
            [
                'sArticles' => $products,
                'facets' => $searchResult->getFacets(),
                'criteria' => $criteria,
                'hasEmotion' => false,
                'isUserLoggedIn' => (bool) $this->get('session')->get('sUserId'),
                'shortParameters' => $this->get('query_alias_mapper')->getQueryAliases(),
                'listingHeadline' => $config['listingHeadline'],
                'listingText' => $config['listingText'],
                'listingBanner' => $config['listingBanner'],
                'listingMetaTitle' => $config['listingMetaTitle'],
                'listingMetaKeywords' => $config['listingMetaKeywords'],
                'listingMetaDescription' => $config['listingMetaDescription'],
                'displayRating' => $config['displayRating'],
                'sortings' => $sortings,
                'sNumberArticles' => $searchResult->getTotalCount(),
                'ajaxCountUrlParams' => [
                    'live_base' => 1,
                    'sCategory' => $context->getShop()->getCategory()->getId(),
                ],
            ]
        );

        $this->View()->assign($data);
    }

    /**
     * @param int    $numPages
     * @param int    $page
     * @param int    $sSort
     * @param string $layout
     *
     * @return array
     */
    protected function createPagination($numPages, $page, $sSort, $layout)
    {
        $pages = [];
        for ($i = 1; $i <= $numPages; ++$i) {
            if ($i === $page) {
                $pages['numbers'][$i]['markup'] = true;
            } else {
                $pages['numbers'][$i]['markup'] = false;
            }
            $pages['numbers'][$i]['value'] = $i;
            $pages['numbers'][$i]['link'] = $this->Front()->Router()->assemble(
                [
                    'controller' => 'abo_commerce',
                    'action' => 'index',
                    'sPage' => $i,
                    'sSort' => $sSort,
                    'sTemplate' => $layout,
                ]
            );
        }

        return $pages;
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function createColumnLayout($config)
    {
        switch ($config['listingTemplate']) {
            case 5:
                $categoryContent['productBoxLayout'] = 'basic';
                break;
            case 6:
                $categoryContent['productBoxLayout'] = 'minimal';
                break;
            case 7:
                $categoryContent['productBoxLayout'] = 'image';
                break;
            default:
                $categoryContent['productBoxLayout'] = 'basic';
                break;
        }

        return $categoryContent;
    }

    /**
     * @param ProductSearchResult $searchResult
     * @param                     $defaultTemplate
     * @param                     $defaultSort
     * @param                     $config
     *
     * @return array
     */
    private function getListingConfiguration(ProductSearchResult $searchResult, $defaultTemplate, $defaultSort, $config)
    {
        $defaultPerPage = (int) $this->get('config')->get('articlesperpage');
        $sSort = (int) $this->Request()->getParam('sSort', $defaultSort);
        $page = (int) $this->Request()->getParam('sPage', 1);
        $perPage = (int) $this->Request()->getParam('sPerPage', $defaultPerPage);
        $layout = $this->Request()->getParam('sTemplate', $defaultTemplate);
        $total = $searchResult->getTotalCount();
        $numPages = ceil($total / $perPage);

        $pages = $this->createPagination($numPages, $page, $sSort, $layout);
        $categoryContent = $this->createColumnLayout($config);

        return [
            'sCategoryContent' => $categoryContent,
            'sNumberPages' => $numPages,
            'sPages' => $pages,
            'sPage' => $page,
            'sTemplate' => $layout,
            'total' => $total,
            'sSort' => $sSort,
            'pageSizes' => explode('|', $this->get('config')->get('numberArticlesToShow')),
        ];
    }
}
