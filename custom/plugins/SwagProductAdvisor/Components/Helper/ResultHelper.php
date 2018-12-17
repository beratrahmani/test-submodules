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

namespace SwagProductAdvisor\Components\Helper;

use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductSearchInterface;
use Shopware\Bundle\SearchBundle\Sorting\PriceSorting;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Shopware\Components\ProductStream\RepositoryInterface;
use Shopware\Components\Theme\Inheritance;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Config;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\Advisor;
use SwagProductAdvisor\Bundle\SearchBundle\AdvisorSorting;
use SwagProductAdvisor\Components\DependencyProvider\DependencyProviderInterface;

class ResultHelper implements ResultHelperInterface
{
    /**
     * @var Criteria
     */
    private $criteria;

    /**
     * @var int
     */
    private $totalCount;

    /**
     * @var StoreFrontCriteriaFactoryInterface
     */
    private $storeFrontCriteriaFactory;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var ProductSearchInterface
     */
    private $advisorSearch;

    /**
     * @var LegacyStructConverter
     */
    private $legacyStructConverter;

    /**
     * @var LiveShoppingHelperInterface
     */
    private $liveShoppingHelper;

    /**
     * @var Inheritance
     */
    private $themeInheritance;

    /**
     * @var RepositoryInterface
     */
    private $streamRepository;

    /**
     * @var Shop
     */
    private $shop;

    /**
     * @param StoreFrontCriteriaFactoryInterface $storeFrontCriteriaFactory
     * @param Shopware_Components_Config         $config
     * @param ContextServiceInterface            $contextService
     * @param RepositoryInterface                $streamRepository
     * @param ProductSearchInterface             $advisorSearch
     * @param LegacyStructConverter              $legacyStructConverter
     * @param LiveShoppingHelperInterface        $liveShoppingHelper
     * @param Inheritance                        $themeInheritance
     * @param DependencyProviderInterface        $dependencyProvider
     */
    public function __construct(
        StoreFrontCriteriaFactoryInterface $storeFrontCriteriaFactory,
        Shopware_Components_Config $config,
        ContextServiceInterface $contextService,
        RepositoryInterface $streamRepository,
        ProductSearchInterface $advisorSearch,
        LegacyStructConverter $legacyStructConverter,
        LiveShoppingHelperInterface $liveShoppingHelper,
        Inheritance $themeInheritance,
        DependencyProviderInterface $dependencyProvider
    ) {
        $this->storeFrontCriteriaFactory = $storeFrontCriteriaFactory;
        $this->config = $config;
        $this->contextService = $contextService;
        $this->advisorSearch = $advisorSearch;
        $this->legacyStructConverter = $legacyStructConverter;
        $this->liveShoppingHelper = $liveShoppingHelper;
        $this->themeInheritance = $themeInheritance;
        $this->streamRepository = $streamRepository;
        $this->shop = $dependencyProvider->getShop();
    }

    /**
     * {@inheritdoc}
     */
    public function getAdvisorResult(Advisor $advisor, Request $request, array $answers = [])
    {
        if (!empty($answers) && count($answers) >= $advisor->getMinMatchingAttributes()) {
            if ($advisor->isHighlightTopHit() && $topHit = $this->getTopHit($advisor, $request)) {
                $advisor->setTopHit(reset($topHit));
            }

            $advisor->setResult($this->getResult($advisor, $request));
            $advisor->setTotalCount($this->getTotalCount());
            if ($advisor->getResult()) {
                $advisor->setOthersTitle($this->checkForOthersTitle($advisor, $request));
            }
        }

        return $advisor;
    }

    /**
     * {@inheritdoc}
     */
    public function getTopHit(Advisor $advisor, Request $request)
    {
        return $this->getResult($advisor, $request, true, 1, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getCriteria(
        Advisor $advisor,
        Request $request,
        ProductContextInterface $context
    ) {
        $criteria = $this->storeFrontCriteriaFactory->createListingCriteria(
            $request,
            $context
        );
        $criteria->resetSorting();

        $sorting = new AdvisorSorting($advisor);
        $criteria->addSorting($sorting);
        $criteria->addSorting(new PriceSorting($advisor->getLastListingSort()));
        $criteria->limit(
            $request->getParam(
                'sPerPage',
                (int) $this->config->get('articlesperpage')
            )
        );

        $this->criteria = $criteria;

        return $this->criteria;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        if ($this->totalCount !== null) {
            return $this->totalCount;
        }

        throw new \Exception('Method \'getResult\' has to be called first.');
    }

    /**
     * @param Advisor $advisor
     * @param Request $request
     * @param bool    $isTopHit
     * @param null    $limit
     * @param null    $offset
     *
     * @return array
     */
    private function getResult(
        Advisor $advisor,
        Request $request,
        $isTopHit = false,
        $limit = null,
        $offset = null
    ) {
        /** @var ProductContextInterface $context */
        $context = $this->contextService->getProductContext();

        /** @var Criteria $criteria */
        $criteria = $this->getCriteria($advisor, $request, $context);

        if ($limit !== null) {
            $criteria->limit($limit);
        }

        if ($offset !== null) {
            $criteria->offset($offset);
        }

        /*
         * We want to remove the first result when not looking for the top-hit.
         * Therefore we have to increase the offset by one.
         */
        if ($isTopHit === false && $advisor->isHighlightTopHit()) {
            $criteria->offset($criteria->getOffset() + 1);
        }

        $this->streamRepository->prepareCriteria($criteria, $advisor->getStream());

        $result = $this->advisorSearch->search($criteria, $context);
        $this->totalCount = $result->getTotalCount();

        /*
         * Same condition as above.
         * When we had to increase the offset by 1 to not display the top-hit twice, we also need to remove that
         * top-hit from the total-count.
         * Therefore we decrease the total-count by 1.
         */
        if ($isTopHit === false && $advisor->isHighlightTopHit()) {
            --$this->totalCount;
        }

        $products = array_map(
            function ($item) {
                return $this->legacyStructConverter->convertListProductStruct($item);
            },
            $result->getProducts()
        );

        $products = $this->liveShoppingHelper->checkForLiveShopping($products);

        return $products;
    }

    /**
     * Helper method to check if the current result should contain the "others"-title.
     *
     * Will return an array like this:
     * [
     *      'showTitle' => true | false,
     *      'replaceTitle' => true | false,
     *      'showLastTitle' => true | false
     * ]
     *
     * 'showTitle' will show the "others" title on the current-page on the first result without a match.
     * 'replaceTitle' When this is set, we want to replace the title above the paging-bar, the "main"-title.
     * 'showLastTitle' This is a rare special-case. It will display the "others"-title on the very end of a page with
     *                 matches only.
     *
     * @param Advisor $advisor
     * @param Request $request
     *
     * @return array
     */
    private function checkForOthersTitle(Advisor $advisor, Request $request)
    {
        $products = $advisor->getResult();
        $firstProduct = $this->getAdvisorAttribute(reset($products));
        $lastProduct = $this->getAdvisorAttribute(end($products));

        if ($firstProduct->hasMatch()) {
            //First result does have a match, last result of current page doesn't have a match.
            //Therefore the "switch" must have been on that page
            if (!$lastProduct->hasMatch()) {
                return ['showTitle' => true];
            }
        } else {
            if ($this->isInfiniteScrollingActive()) {
                return $this->checkInfiniteScrolling($advisor, $request);
            }

            return ['replaceTitle' => true];
        }

        if ($this->isInfiniteScrollingActive() && $request->isXmlHttpRequest() && $request->getParam('mode') === 'previous') {
            return $this->checkNextPage($advisor, $request);
        }

        return [];
    }

    /**
     * Helper method to check if the title should be replaced when using infinite scrolling.
     *
     * @param Advisor $advisor
     * @param Request $request
     *
     * @return array
     */
    private function checkInfiniteScrolling(Advisor $advisor, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            return $this->checkPreviousPage($advisor, $request);
        }

        return ['replaceTitle' => true];
    }

    /**
     * Helper method to check if the last match of the previous page was also only having misses.
     *
     * @param Advisor $advisor
     * @param Request $request
     *
     * @return array
     */
    private function checkPreviousPage(Advisor $advisor, Request $request)
    {
        $currentPage = (int) $request->getParam('sPage');
        if ($currentPage > 1) {
            $previousPage = $currentPage - 1;
            $request->setParam('sPage', $previousPage);

            $result = $this->getResult($advisor, $request);
            $lastProduct = $this->getAdvisorAttribute(end($result));

            $request->setParam('sPage', $currentPage);

            if ($lastProduct->hasMatch()) {
                return ['showTitle' => true];
            }
        }

        return [];
    }

    /**
     * This is only called, when the first and the last products each have a match and if this is an XML-HTTP-Request.
     * We need to check if the the first product of the next page should have the "others"-title.
     * In this case we have to show the title on the end of the current page.
     *
     * @param Advisor $advisor
     * @param Request $request
     *
     * @return array
     */
    private function checkNextPage(Advisor $advisor, Request $request)
    {
        $currentPage = (int) $request->getParam('sPage');
        /** @var ProductContextInterface $context */
        $context = $this->contextService->getProductContext();
        $lastPage = ceil($advisor->getTotalCount() / $this->getCriteria($advisor, $request, $context)->getLimit());

        if ($currentPage < $lastPage) {
            $nextPage = $currentPage + 1;
            $request->setParam('sPage', $nextPage);

            $result = $this->getResult($advisor, $request);
            $firstProduct = $this->getAdvisorAttribute(reset($result));

            $request->setParam('sPage', $currentPage);
            if (!$firstProduct->hasMatch()) {
                return ['showLastTitle' => true];
            }
        }

        return [];
    }

    /**
     * Helper method to get the advisor-attributes for a product.
     *
     * @param array $product
     *
     * @return \SwagProductAdvisor\Bundle\AdvisorBundle\Struct\AdvisorAttribute
     */
    private function getAdvisorAttribute(array $product)
    {
        return $product['attributes']['advisor'];
    }

    /**
     * Helper method to find out if infinite-scrolling is active.
     *
     * @return mixed
     */
    private function isInfiniteScrollingActive()
    {
        $config = $this->themeInheritance->buildConfig(
            $this->shop->getTemplate(),
            $this->shop,
            false
        );

        return $config['infiniteScrolling'];
    }
}
