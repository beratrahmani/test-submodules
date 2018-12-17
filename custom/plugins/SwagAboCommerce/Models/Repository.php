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

namespace SwagAboCommerce\Models;

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelRepository;

class Repository extends ModelRepository
{
    /**
     * @return \Doctrine\ORM\Query
     */
    public function getAboCommerceSettingsQuery()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(['settings'])
            ->from(Settings::class, 'settings')
            ->setMaxResults(1);

        return $builder->getQuery();
    }

    /**
     * @param int|array $productIds
     *
     * @return \Doctrine\ORM\Query
     */
    public function getActiveAboProductByProductIdQuery($productIds)
    {
        $productIds = (array) $productIds;

        return $this->createQueryBuilder('aboCommerce')
            ->select('aboCommerce', 'prices')
            ->andWhere('aboCommerce.articleId IN(:productIds)')
            ->andWhere('aboCommerce.active = true')
            ->leftJoin('aboCommerce.prices', 'prices')
            ->setParameter('productIds', $productIds)
            ->getQuery();
    }

    /**
     * @param int $productId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDetailQuery($productId)
    {
        return $this->createQueryBuilder('aboCommerce')
            ->select('aboCommerce', 'prices')
            ->andWhere('aboCommerce.articleId = :productId')
            ->leftJoin('aboCommerce.prices', 'prices')
            ->setParameter('productId', $productId)
            ->getQuery();
    }

    /**
     * @param int $customerId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getOpenOrderByCustomerIdQuery($customerId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select([
            'aboOrders',
            'articleOrderDetail',
            'discountOrderDetail',
            'orders',
            'lastOrder',
        ]);

        $builder->from(Order::class, 'aboOrders')
            ->innerJoin('aboOrders.order', 'orders')
            ->innerJoin('aboOrders.lastOrder', 'lastOrder')
            ->innerJoin('aboOrders.articleOrderDetail', 'articleOrderDetail')
            ->leftJoin('aboOrders.discountOrderDetail', 'discountOrderDetail')
            ->andWhere('aboOrders.dueDate <= aboOrders.lastRun')
            ->orWhere('aboOrders.endlessSubscription = 1 AND aboOrders.lastRun IS NULL')
            ->andWhere('aboOrders.customerId = :customerId')
            ->orderBy('aboOrders.dueDate')
            ->setParameter('customerId', $customerId);

        return $builder->getQuery();
    }

    /**
     * @param int $aboOrderId
     *
     * @return array
     */
    public function getAboWithUserData($aboOrderId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->from(Order::class, 'aboOrder')
            ->select(['aboOrder', 'customer', 'defaultBillingAddress'])
            ->leftJoin('aboOrder.customer', 'customer')
            ->leftJoin('customer.defaultBillingAddress', 'defaultBillingAddress')
            ->where('aboOrder.id = :id')
            ->setParameter('id', $aboOrderId)
            ->getQuery()
            ->getSingleResult(AbstractQuery::HYDRATE_ARRAY)
        ;
    }
}
