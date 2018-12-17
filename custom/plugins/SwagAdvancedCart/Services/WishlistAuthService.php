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
use SwagAdvancedCart\Services\Dependencies\DependencyProviderInterface;

class WishlistAuthService implements WishlistAuthServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @param DependencyProviderInterface $dependencyProvider
     * @param Connection                  $connection
     */
    public function __construct(DependencyProviderInterface $dependencyProvider, Connection $connection)
    {
        $this->dependencyProvider = $dependencyProvider;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticateById($wishListId)
    {
        return in_array($wishListId, $this->getBasketList('id'));
    }

    /**
     * {@inheritdoc}
     */
    public function authenticateByHash($hash)
    {
        return in_array($hash, $this->getBasketList('cookie_value'));
    }

    /**
     * {@inheritdoc}
     */
    public function isPublic($wishListId)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder->select('published')
            ->from('s_order_basket_saved')
            ->where('id = :id')
            ->setParameter('id', $wishListId)
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);

        return (bool) $result;
    }

    /**
     * @param string $columnName
     *
     * @return array
     */
    private function getBasketList($columnName)
    {
        $userId = $this->dependencyProvider->getSession()->get('sUserId');

        $queryBuilder = $this->connection->createQueryBuilder();

        return $queryBuilder->select($columnName)
            ->from('s_order_basket_saved')
            ->where('user_id = :userId')
            ->setParameter('userId', $userId)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
