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

namespace SwagAdvancedCart\Services;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;

class ProductsAlsoInListService implements ProductsAlsoInListServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var ListProductServiceInterface
     */
    private $listProductService;

    /**
     * @var LegacyStructConverter
     */
    private $legacyStructConverter;

    /**
     * @param Connection                  $connection
     * @param ContextServiceInterface     $contextService
     * @param ListProductServiceInterface $listProductService
     * @param LegacyStructConverter       $legacyStructConverter
     */
    public function __construct(
        Connection $connection,
        ContextServiceInterface $contextService,
        ListProductServiceInterface $listProductService,
        LegacyStructConverter $legacyStructConverter
    ) {
        $this->connection = $connection;
        $this->contextService = $contextService;
        $this->listProductService = $listProductService;
        $this->legacyStructConverter = $legacyStructConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlsoProductsList($ordernumber)
    {
        $basketIds = $this->getBasketIds($ordernumber);
        $orderNumbers = $this->getOrderNumbers($basketIds, $ordernumber);

        $listProductStructList = $this->listProductService->getList(
            $orderNumbers,
            $this->contextService->getShopContext()
        );

        return $this->legacyStructConverter->convertListProductStructList($listProductStructList);
    }

    /**
     * @param array  $basketIds
     * @param string $ordernumber
     *
     * @return array
     */
    private function getOrderNumbers($basketIds, $ordernumber)
    {
        return $this->connection->createQueryBuilder()
            ->select('DISTINCT article_ordernumber')
            ->from('s_order_basket_saved_items')
            ->where('basket_id IN (:basketIds)')
            ->andWhere('article_ordernumber != :orderNumber')
            ->setParameter('basketIds', $basketIds, Connection::PARAM_INT_ARRAY)
            ->setParameter('orderNumber', $ordernumber)
            ->setMaxResults(25)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param string $orderNumber
     *
     * @return array
     */
    private function getBasketIds($orderNumber)
    {
        return $this->connection->createQueryBuilder()
            ->select('basket_id')
            ->from('s_order_basket_saved_items')
            ->where('article_ordernumber = :orderNumber;')
            ->setParameter('orderNumber', $orderNumber)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
