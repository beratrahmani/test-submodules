<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\ProductOrderNumberType;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeRepositoryInterface;

class ProductOrderNumberRepository implements ContingentRuleTypeRepositoryInterface
{
    const TABLE_NAME = 'b2b_contingent_group_rule_product_order_number';

    const TABLE_ALIAS = 'ProductOrderNumberType';

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
    public function createSubQuery(): string
    {
        return $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->getSQL();
    }

    /**
     * {@inheritdoc}
     */
    public function addSelect(QueryBuilder $query, string $prefix)
    {
        $query->addSelect($prefix . '.contingent_rule_id as ' . $prefix . '_contingent_rule_id')
            ->addSelect($prefix . '.product_order_number as ' . $prefix . '_product_order_number');
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}
