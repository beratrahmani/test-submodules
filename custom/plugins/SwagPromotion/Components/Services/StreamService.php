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

namespace SwagPromotion\Components\Services;

use Shopware\Bundle\SearchBundle\Condition\OrdernumberCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\ProductStream\RepositoryInterface;

class StreamService
{
    /**
     * @var array
     */
    private $streams;

    /**
     * @var ContextService
     */
    private $contextService;

    /**
     * @var StoreFrontCriteriaFactoryInterface
     */
    private $criteriaFactory;

    /**
     * @var RepositoryInterface
     */
    private $streamRepository;

    /**
     * @var ProductNumberSearchInterface
     */
    private $productNumberSearch;

    /**
     * StreamService constructor.
     *
     * @param ContextService                     $contextService
     * @param StoreFrontCriteriaFactoryInterface $criteriaFactory
     * @param RepositoryInterface                $streamRepository
     * @param ProductNumberSearchInterface       $productNumberSearch
     */
    public function __construct(
        ContextService $contextService,
        StoreFrontCriteriaFactoryInterface $criteriaFactory,
        RepositoryInterface $streamRepository,
        ProductNumberSearchInterface $productNumberSearch
    ) {
        $this->contextService = $contextService;
        $this->criteriaFactory = $criteriaFactory;
        $this->streamRepository = $streamRepository;
        $this->productNumberSearch = $productNumberSearch;
        $this->streams = [];
    }

    /**
     * @param string   $id
     * @param string[] $numbers
     *
     * @return BaseProduct[]
     */
    public function getProductNumbersFromStreamByStreamId($id, array $numbers)
    {
        $key = $id . md5(json_encode($numbers));
        if (isset($this->streams[$key])) {
            return $this->streams[$key];
        }

        $context = $this->contextService->getProductContext();

        $criteria = $this->criteriaFactory->createBaseCriteria([$context->getShop()->getCategory()->getId()], $context);
        $this->streamRepository->prepareCriteria($criteria, $id);

        $filtered = $this->filterByNumberCondition($numbers, $criteria);
        if (empty($filtered)) {
            return [];
        }

        $result = $this->search($criteria, $filtered, $context);
        $this->streams[$key] = $result->getProducts();

        return $this->streams[$key];
    }

    /**
     * @param string[] $numbers
     * @param Criteria $criteria
     *
     * @return string[]
     */
    private function filterByNumberCondition($numbers, Criteria $criteria)
    {
        if (!$criteria->hasCondition('ordernumber')) {
            return $numbers;
        }
        /** @var OrdernumberCondition $condition */
        $condition = $criteria->getCondition('ordernumber');

        return array_values(array_intersect($numbers, $condition->getOrdernumbers()));
    }

    /**
     * @param Criteria             $criteria
     * @param string[]             $filtered
     * @param ShopContextInterface $context
     *
     * @return ProductNumberSearchResult
     */
    private function search(Criteria $criteria, array $filtered, ShopContextInterface $context)
    {
        $criteria->addBaseCondition(new OrdernumberCondition($filtered));
        $criteria->limit(count($filtered));
        $criteria->offset(0);
        $criteria->resetSorting();
        $criteria->resetFacets();

        return $this->productNumberSearch->search($criteria, $context);
    }
}
