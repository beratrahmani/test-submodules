<?php declare(strict_types=1);

namespace Shopware\B2B\LineItemList\Bridge;

use Doctrine\DBAL\Connection;
use Shopware\B2B\LineItemList\Framework\LineItemBridgeRepositoryInterface;

class LineItemBridgeRepository implements LineItemBridgeRepositoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchCartDataById(string $cartId): array
    {
        return (array) $this->connection->createQueryBuilder()
            ->select('*')
            ->from('s_order_basket', '`cart`')
            ->where('`cart`.sessionID = :cartId')
            ->setParameter('cartId', $cartId)
            ->execute()
            ->fetchAll();
    }
}
