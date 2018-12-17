<?php declare(strict_types=1);

namespace Shopware\B2B\LineItemList\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyCalculator;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class LineItemListRepository
{
    const TABLE_NAME = 'b2b_line_item_list';

    const TABLE_ALIAS = 'lineItemList';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var LineItemReferenceRepository
     */
    private $referenceRepository;

    /**
     * @var CurrencyCalculator
     */
    private $currencyCalculator;

    /**
     * @param Connection $connection
     * @param LineItemReferenceRepository $referenceRepository
     * @param CurrencyCalculator $currencyCalculator
     */
    public function __construct(
        Connection $connection,
        LineItemReferenceRepository $referenceRepository,
        CurrencyCalculator $currencyCalculator
    ) {
        $this->connection = $connection;
        $this->referenceRepository = $referenceRepository;
        $this->currencyCalculator = $currencyCalculator;
    }

    /**
     * @param int $listId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return LineItemList
     */
    public function fetchOneListById(
        int $listId,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): LineItemList {
        $listData = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->andWhere(self::TABLE_ALIAS . '.context_owner_id = :contextOwner')
            ->setParameter('id', $listId)
            ->setParameter('contextOwner', $ownershipContext->contextOwnerId)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        if (!$listData) {
            throw new NotFoundException('Unable to find list by id "' . $listId . '"');
        }

        $list = new LineItemList();
        $list->fromDatabaseArray($listData);
        $list->references = $this->referenceRepository->fetchAllForList($listId);
        $this->currencyCalculator->recalculateAmount($list, $currencyContext);

        return $list;
    }

    /**
     * @param LineItemList $list
     * @param OwnershipContext $ownershipContext
     * @throws \Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException
     * @return LineItemList
     */
    public function addList(LineItemList $list, OwnershipContext $ownershipContext): LineItemList
    {
        if (!$list->isNew()) {
            throw new CanNotInsertExistingRecordException('The list provided already exists');
        }

        $this->connection->insert(
            self::TABLE_NAME,
            array_merge(
                $list->toDatabaseArray(),
                ['context_owner_id' => $ownershipContext->contextOwnerId]
            )
        );

        $list->id = (int) $this->connection->lastInsertId();

        return $list;
    }

    /**
     * @param LineItemList $lineItemList
     * @param OwnershipContext $ownershipContext
     */
    public function updateListPrices(LineItemList $lineItemList, OwnershipContext $ownershipContext)
    {
        $this->connection->update(
            self::TABLE_NAME,
            [
                'amount_net' => $lineItemList->amountNet,
                'amount' => $lineItemList->amount,
                'currency_factor' => $lineItemList->currencyFactor,
            ],
            [
                'id' => $lineItemList->id,
                'context_owner_id' => $ownershipContext->contextOwnerId,
            ]
        );
    }

    /**
     * @param LineItemList $lineItemList
     * @param OwnershipContext $ownershipContext
     * @throws CanNotRemoveExistingRecordException
     * @return LineItemList
     */
    public function removeLineItemList(LineItemList $lineItemList, OwnershipContext $ownershipContext): LineItemList
    {
        if ($lineItemList->isNew()) {
            throw new CanNotRemoveExistingRecordException('The line item list provided does not exist');
        }

        $this->removeLineItemListById((int) $lineItemList->id, $ownershipContext);
        $lineItemList->id = null;

        return $lineItemList;
    }

    /**
     * @param int $listId
     * @param OwnershipContext $ownershipContext
     */
    public function removeLineItemListById(int $listId, OwnershipContext $ownershipContext)
    {
        $this->connection->delete(
            self::TABLE_NAME,
            [
                'id' => $listId,
                'context_owner_id' => $ownershipContext->contextOwnerId,
            ]
        );
    }
}
