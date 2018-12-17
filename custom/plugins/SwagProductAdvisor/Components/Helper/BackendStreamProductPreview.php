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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\ProductNumberSearchInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\ProductStream\CriteriaFactory;
use Shopware\Components\ProductStream\CriteriaFactoryInterface;
use Shopware\Components\ProductStream\RepositoryInterface;
use SwagProductAdvisor\Components\DeHydrationInterface;
use SwagProductAdvisor\Structs\BackendSearchResult;

class BackendStreamProductPreview implements BackendStreamProductPreviewInterface
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var CriteriaFactory
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
     * @var DefaultSettingsServiceInterface
     */
    private $defaultSettingsService;

    /**
     * @var DeHydrationInterface
     */
    private $advisorDehydrator;

    /**
     * @param Connection                      $dbalConnection
     * @param ContextServiceInterface         $contextService
     * @param CriteriaFactoryInterface        $criteriaFactory
     * @param RepositoryInterface             $streamRepository
     * @param ProductNumberSearchInterface    $productNumberSearch
     * @param DefaultSettingsServiceInterface $defaultSettingsService
     * @param DeHydrationInterface            $advisorDehydrator
     */
    public function __construct(
        Connection $dbalConnection,
        ContextServiceInterface $contextService,
        CriteriaFactoryInterface $criteriaFactory,
        RepositoryInterface $streamRepository,
        ProductNumberSearchInterface $productNumberSearch,
        DefaultSettingsServiceInterface $defaultSettingsService,
        DeHydrationInterface $advisorDehydrator
    ) {
        $this->dbalConnection = $dbalConnection;
        $this->contextService = $contextService;
        $this->criteriaFactory = $criteriaFactory;
        $this->streamRepository = $streamRepository;
        $this->productNumberSearch = $productNumberSearch;
        $this->defaultSettingsService = $defaultSettingsService;
        $this->advisorDehydrator = $advisorDehydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsInStream(
        $streamId,
        $limit,
        $offset,
        $shopId = null,
        $currencyId = null,
        $customerGroupKey = null
    ) {
        $defaultSettings = $this->defaultSettingsService->getDefaultSettings();

        /** @var ShopContext $context */
        $context = $this->contextService->createProductContext(
            $shopId ? $shopId : $defaultSettings->getShopId(),
            $currencyId ? $currencyId : $defaultSettings->getCurrencyId(),
            $customerGroupKey ? $customerGroupKey : $defaultSettings->getCustomerGroupKey()
        );

        $productNumberSearchResult = $this->getProductNumbersByStreamId($streamId, $context, $limit, $offset);

        $productOrderNumbers = $this->advisorDehydrator->dehydrateProductNumberSearchResult($productNumberSearchResult);
        $productNames = $this->getProductNamesById($productOrderNumbers);

        return new BackendSearchResult(
            $productNames,
            $productNumberSearchResult->getTotalCount()
        );
    }

    /**
     * @param array $productIds
     *
     * @return array
     */
    private function getProductNamesById(array $productIds)
    {
        return $this->dbalConnection->createQueryBuilder()
            ->select(['CONCAT(article.name, details.additionaltext, " ") as name', 'article.id as id'])
            ->from('s_articles', 'article')
            ->join('article', 's_articles_details', 'details', 'article.id = details.articleID')
            ->where('article.id in (:productIds)')
            ->setParameter(':productIds', $productIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param int                  $streamId
     * @param ShopContextInterface $context
     * @param int                  $limit
     * @param int                  $offset
     *
     * @return ProductNumberSearchResult
     */
    private function getProductNumbersByStreamId($streamId, ShopContextInterface $context, $limit = 50, $offset = 0)
    {
        $criteria = $this->criteriaFactory->createCriteria(Shopware()->Front()->Request(), $context);
        $criteria->limit($limit);
        $criteria->offset($offset);
        $this->streamRepository->prepareCriteria($criteria, $streamId);

        return $this->productNumberSearch->search($criteria, $context);
    }
}
