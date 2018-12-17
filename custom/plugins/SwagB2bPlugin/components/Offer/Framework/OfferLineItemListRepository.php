<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyCalculator;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OfferLineItemListRepository
{
    const TABLE_NAME = 'b2b_line_item_list';
    const TABLE_ALIAS = 'lineItemList';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var OfferLineItemReferenceRepository
     */
    private $referenceRepository;

    /**
     * @var CurrencyCalculator
     */
    private $currencyCalculator;

    /**
     * @param Connection $connection
     * @param OfferLineItemReferenceRepository $referenceRepository
     * @param CurrencyCalculator $currencyCalculator
     */
    public function __construct(
        Connection $connection,
        OfferLineItemReferenceRepository $referenceRepository,
        CurrencyCalculator $currencyCalculator
    ) {
        $this->connection = $connection;
        $this->referenceRepository = $referenceRepository;
        $this->currencyCalculator = $currencyCalculator;
    }

    /**
     * @param $listId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @throws NotFoundException
     * @return LineItemList
     */
    public function fetchOneListById(int $listId, CurrencyContext $currencyContext, OwnershipContext $ownershipContext): LineItemList
    {
        $listDataQuery = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :id');

        $listDataQuery->andWhere(self::TABLE_ALIAS . '.context_owner_id = :ownerId')
          ->setParameter('ownerId', $ownershipContext->contextOwnerId);

        $listData = $listDataQuery->setParameter('id', $listId)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        if (!$listData) {
            throw new NotFoundException('Unable to find list by id "' . $listId . '"');
        }

        $list = new LineItemList();
        $list->fromDatabaseArray($listData);
        $list->references = $this->referenceRepository->fetchAllForList($listId, $ownershipContext);
        $this->currencyCalculator->recalculateAmount($list, $currencyContext);

        return $list;
    }
}
