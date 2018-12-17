<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException;
use Shopware\B2B\Order\Framework\ShopOrderRepositoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OfferContextRepository
{
    const TABLE_NAME = 'b2b_order_context';
    const TABLE_ALIAS = 'b2bOrder';
    const STATUS_OFFER = -4;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ShopOrderRepositoryInterface
     */
    private $shopOrderRepository;

    /**
     * @param Connection $connection
     * @param ShopOrderRepositoryInterface $shopOrderRepository
     */
    public function __construct(
        Connection $connection,
        ShopOrderRepositoryInterface $shopOrderRepository
    ) {
        $this->connection = $connection;
        $this->shopOrderRepository = $shopOrderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function sendToOfferState(int $orderContextId, OwnershipContext $ownershipContext)
    {
        $this->setOrderStatusId($orderContextId, self::STATUS_OFFER, [], $ownershipContext);
    }

    /**
     * @internal
     * @param int $orderContextId
     * @param int $statusId
     * @param array $additionalData
     * @param OwnershipContext $ownershipContext
     */
    protected function setOrderStatusId(
        int $orderContextId,
        int $statusId,
        array $additionalData,
        OwnershipContext $ownershipContext
    ) {
        $success = (bool) $this->connection->update(
            self::TABLE_NAME,
            array_merge(
                ['status_id' => $statusId],
                $additionalData
            ),
            ['id' => $orderContextId]
        );

        if (!$success) {
            throw new CanNotUpdateExistingRecordException(
                "Could not update b2b order context status with context id {$orderContextId}"
            );
        }

        $this->shopOrderRepository
            ->updateStatus($orderContextId, $statusId, $ownershipContext);
    }
}
