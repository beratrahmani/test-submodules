<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Bridge;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Order\Framework\ShopOrderRepositoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class ShopOrderRepository implements ShopOrderRepositoryInterface
{
    const TABLE_ALIAS = 'sOrder';
    const TABLE_NAME = 's_order';

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
     * @param string $orderNumber
     * @param int $authId
     */
    public function setOrderIdentity(string $orderNumber, int $authId)
    {
        $orderId = $this->fetchOrderIdByOrderNumber($orderNumber);

        $this->connection->update(
            's_order_attributes',
            ['b2b_auth_id' => $authId],
            ['orderID' => $orderId]
        );
    }

    /**
     * @param string $orderNumber
     * @param int $debtorId
     */
    public function setOrderToShopOwnerUser(string $orderNumber, int $debtorId)
    {
        $orderId = $this->fetchOrderIdByOrderNumber($orderNumber);

        $this->connection->update(
            's_order',
            ['userID' => $debtorId],
            ['id ' => $orderId]
        );

        $this->connection->update(
            's_order_esd',
            ['userID' => $debtorId],
            ['orderID' => $orderId]
        );
    }

    /**
     * @param int $orderContextId
     * @param string $comment
     */
    public function setOrderCommentByOrderContextId(int $orderContextId, string $comment)
    {
        $this->connection->update(
            'b2b_order_context',
            ['comment' => $comment],
            ['id' => $orderContextId]
        );
    }

    /**
     * @internal
     * @param string $orderNumber
     * @throws NotFoundException
     * @return int
     */
    protected function fetchOrderIdByOrderNumber(string $orderNumber): int
    {
        $orderId = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('s_order')
            ->where('ordernumber = :ordernumber')
            ->setParameter('ordernumber', $orderNumber)
            ->execute()
            ->fetchColumn();

        if (!$orderId) {
            throw new NotFoundException(sprintf('order id by number id %s not found', $orderNumber));
        }

        return (int) $orderId;
    }

    /**
     * @param int $orderId
     * @return array
     */
    public function fetchOrderById(int $orderId): array
    {
        return $this->connection
            ->fetchAssoc('SELECT * FROM s_order WHERE id=:orderId', ['orderId' => $orderId]);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStatus(int $orderContextId, int $statusId, OwnershipContext $ownershipContext)
    {
        $statement = $this->connection->prepare('
            UPDATE ' . self::TABLE_NAME . ' as ' . self::TABLE_ALIAS . '
            INNER JOIN b2b_order_context b2bOrder ON b2bOrder.ordernumber = ' . self::TABLE_ALIAS . '.ordernumber
            SET ' . self::TABLE_ALIAS . ' .status = :status
            WHERE b2bOrder.id = :id 
            and b2bOrder.auth_id in (SELECT id FROM b2b_store_front_auth WHERE b2bOrder.auth_id = :ownerId OR context_owner_id = :ownerId);
        ');

        $statement->execute([
            'status' => $statusId,
            'id' => $orderContextId,
            'ownerId' => $ownershipContext->contextOwnerId,
        ]);
    }
}
