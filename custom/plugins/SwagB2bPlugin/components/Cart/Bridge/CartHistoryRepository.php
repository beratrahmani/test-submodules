<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Bridge;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Cart\Framework\CartHistory;
use Shopware\B2B\Cart\Framework\CartHistoryRepositoryInterface;
use Shopware\B2B\Currency\Framework\CurrencyCalculator;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class CartHistoryRepository implements CartHistoryRepositoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var CurrencyCalculator
     */
    private $currencyCalculator;

    /**
     * @param Connection $connection
     * @param CurrencyCalculator $currencyCalculator
     */
    public function __construct(Connection $connection, CurrencyCalculator $currencyCalculator)
    {
        $this->connection = $connection;
        $this->currencyCalculator = $currencyCalculator;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchHistory(array $timeRestrictions, OwnershipContext $ownershipContext, CurrencyContext $currencyContext): array
    {
        $invoiceCalculatedSqlPart = $this->currencyCalculator
            ->getSqlCalculationPart('amount_net', 'currency_factor', $currencyContext, 'list');

        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*) AS orderQuantity')
            ->addSelect('ROUND(SUM(' . $invoiceCalculatedSqlPart . ')) AS orderAmount')
            ->addSelect('SUM(item.quantity) AS orderItemQuantity')
            ->from('b2b_order_context', 'orderContext')
            ->innerJoin('orderContext', 'b2b_line_item_list', 'list', 'orderContext.list_id = list.id')
            ->innerJoin('list', 'b2b_line_item_reference', 'item', 'list.id = item.list_id');

        $cartHistory = [];
        foreach ($timeRestrictions as $timeRestriction) {
            $query->where($timeRestriction . '(cleared_at) = ' . $timeRestriction . '(NOW())')
                ->andWhere('orderContext.auth_id = :authId')
                ->andWhere('orderContext.status_id >= 0')
                ->groupBy($timeRestriction . '(cleared_at)')
                ->setParameter('authId', $ownershipContext->authId);

            if ($timeRestriction !== 'YEARWEEK') {
                $query->andWhere('YEAR(cleared_at) = YEAR(NOW())');
            }

            $orderHistory = $query->execute()->fetch(\PDO::FETCH_ASSOC);

            $history = new CartHistory();
            $history->timeRestriction = $timeRestriction;

            if ($orderHistory) {
                $history->fromDatabaseArray($orderHistory);
            }

            $cartHistory[$timeRestriction] = $history;
        }

        return $cartHistory;
    }
}
