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

class UserService implements UserServiceInterface
{
    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @var Connection
     */
    private $connection;

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
    public function getUserIdByArgument($arguments)
    {
        $user = $arguments->get('user');
        $userId = null;
        if ($user) {
            return $user['id'];
        }

        return $this->getSessionUserId();
    }

    /**
     * @return int|null
     */
    public function getSessionUserId()
    {
        $session = $this->dependencyProvider->getSession();

        if ($session) {
            return $session->get('sUserId');
        }

        return null;
    }

    /**
     * @param int $userId
     *
     * @return array
     */
    public function getUserData($userId)
    {
        $result = $this->connection->createQueryBuilder()
            ->select(['user.email', 'addresses.firstname', 'addresses.lastname'])
            ->from('s_user', 'user')
            ->join('user', 's_user_addresses', 'addresses', 'user.id = addresses.user_id')
            ->where('user.id = :userId')
            ->setParameter('userId', $userId)
            ->execute()
            ->fetch();

        return $result;
    }
}
