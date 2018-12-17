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

use Shopware\Bundle\SearchBundle\Facet\ManufacturerFacet;
use Shopware\Bundle\SearchBundle\Facet\PriceFacet;
use Shopware\Bundle\SearchBundle\Facet\ProductAttributeFacet;
use Shopware\Bundle\SearchBundle\Facet\PropertyFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\FacetResultGroup;
use Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListItem;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\SearchBundleDBAL\ProductNumberSearch;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContext;
use Shopware\Components\ProductStream\RepositoryInterface;

class BackendStreamHelper implements BackendStreamHelperInterface
{
    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var RepositoryInterface
     */
    private $streamRepository;

    /**
     * @var ProductNumberSearch
     */
    private $productNumberSearch;

    /**
     * @var DefaultSettingsServiceInterface
     */
    private $defaultSettingsService;

    /**
     * @var StoreFrontCriteriaFactoryInterface
     */
    private $storeFrontCriteriaFactory;

    /**
     * @param ContextServiceInterface            $contextService
     * @param RepositoryInterface                $streamRepository
     * @param ProductNumberSearchInterface       $productNumberSearch
     * @param DefaultSettingsServiceInterface    $defaultSettingsService
     * @param StoreFrontCriteriaFactoryInterface $storeFrontCriteriaFactory
     */
    public function __construct(
        ContextServiceInterface $contextService,
        RepositoryInterface $streamRepository,
        ProductNumberSearchInterface $productNumberSearch,
        DefaultSettingsServiceInterface $defaultSettingsService,
        StoreFrontCriteriaFactoryInterface $storeFrontCriteriaFactory
    ) {
        $this->contextService = $contextService;
        $this->streamRepository = $streamRepository;
        $this->productNumberSearch = $productNumberSearch;
        $this->defaultSettingsService = $defaultSettingsService;
        $this->storeFrontCriteriaFactory = $storeFrontCriteriaFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxPriceByStreamIds($streamId)
    {
        $facet = new PriceFacet();

        /** @var ProductNumberSearchInterface $searchResult */
        $searchResult = $this->getSearchResult($streamId, $facet);

        $facets = $searchResult->getFacets();

        $maxPrice = 0;
        /** @var RangeFacetResult $facet */
        foreach ($facets as $facet) {
            if ($facet->getMax() > $maxPrice) {
                $maxPrice = $facet->getMax();
            }
        }

        return $maxPrice;
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyValuesByStreamAndPropertyId($streamId, $propertyId)
    {
        $facet = new PropertyFacet();
        $result = null;

        /** @var ProductNumberSearchInterface $searchResult */
        $searchResult = $this->getSearchResult($streamId, $facet);

        if (count($searchResult->getFacets()) > 0) {
            /** @var FacetResultGroup $valueListItems */
            $valueListItems = array_shift($searchResult->getFacets());
            $result = $valueListItems->getFacetResults();
        }

        $propertyArray = [];
        /** @var ValueListFacetResult $item */
        foreach ($result as $item) {
            $parentId = $this->getParentId(json_decode($this->generateId($item)));
            if ($propertyId === $parentId) {
                foreach ($item->getValues() as $value) {
                    $propertyArray[] = [
                        'key' => $value->getId(),
                        'value' => $value->getLabel(),
                    ];
                }
            }
        }

        return $propertyArray;
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertiesByStreamId($streamId)
    {
        $facet = new PropertyFacet();

        /** @var ProductNumberSearchInterface $searchResult */
        $searchResult = $this->getSearchResult($streamId, $facet);

        /** @var FacetResultGroup $valueListItems */
        $valueListItems = array_shift($searchResult->getFacets());

        if (!$valueListItems) {
            return [];
        }

        $result = $valueListItems->getFacetResults();

        $propertyArray = [];
        /** @var ValueListFacetResult $item */
        foreach ($result as $item) {
            $parentId = $this->getParentId(json_decode($this->generateId($item)));
            $propertyArray[] = [
                'id' => $parentId,
                'name' => $item->getLabel(),
            ];
        }

        return $propertyArray;
    }

    /**
     * {@inheritdoc}
     */
    public function getManufacturerByStreamIds($streamId)
    {
        $facet = new ManufacturerFacet();
        $valueListItems = null;

        /** @var ProductNumberSearchInterface $searchResult */
        $searchResult = $this->getSearchResult($streamId, $facet);

        if (count($searchResult->getFacets()) > 0) {
            $valueListItems = array_shift($searchResult->getFacets())->getValues();
        }

        $manufacturerArray = [];

        /** @var ValueListItem $manufacturer */
        foreach ($valueListItems as $manufacturer) {
            $manufacturerArray[] = [
                'key' => $manufacturer->getId(),
                'value' => $manufacturer->getLabel(),
            ];
        }

        return $manufacturerArray;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeValuesByStreamIdAndAttributeColumnName($streamId, $columnName)
    {
        $facet = new ProductAttributeFacet(
            $columnName,
            ProductAttributeFacet::MODE_VALUE_LIST_RESULT,
            'empty',
            'empty'
        );

        /** @var ProductNumberSearchInterface $searchResult */
        $searchResult = $this->getSearchResult($streamId, $facet);

        if (count($searchResult->getFacets()) <= 0) {
            return [];
        }

        $facet = array_shift($searchResult->getFacets());

        return array_map(function (ValueListItem $item) {
            return ['key' => $item->getLabel(), 'value' => $item->getId()];
        }, $facet->getValues());
    }

    /**
     * Reads the filter-id from the child-ids.
     *
     * @param array $ids
     *
     * @return bool|string
     */
    private function getParentId($ids)
    {
        $builder = Shopware()->Models()->getConnection()->createQueryBuilder();

        return $builder->select('optionID')
            ->from('s_filter_values', 'fValues')
            ->where('id in (:ids)')
            ->setParameter('ids', $ids, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
            ->setMaxResults(1)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @param int            $streamId
     * @param FacetInterface $facet
     *
     * @return ProductNumberSearchResult
     */
    private function getSearchResult($streamId, $facet)
    {
        $defaultSettings = $this->defaultSettingsService->getDefaultSettings();
        /** @var ProductContext $context */
        $context = $this->contextService->createProductContext(
            $defaultSettings->getShopId(),
            $defaultSettings->getCurrencyId(),
            $defaultSettings->getCustomerGroupKey()
        );

        $criteria = $this->storeFrontCriteriaFactory->createListingCriteria(Shopware()->Front()->Request(), $context);
        $criteria->resetFacets();
        $criteria->addFacet($facet);
        $this->streamRepository->prepareCriteria($criteria, $streamId);

        return $this->productNumberSearch->search($criteria, $context);
    }

    /**
     * @param FacetResultInterface $item
     *
     * @return string
     */
    private function generateId(FacetResultInterface $item)
    {
        $idAsArray = [];
        foreach ($item->getValues() as $value) {
            $idAsArray[] = $value->getId();
        }

        return json_encode($idAsArray);
    }
}
