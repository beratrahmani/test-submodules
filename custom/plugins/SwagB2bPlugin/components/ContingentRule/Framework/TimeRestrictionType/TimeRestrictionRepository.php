<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\TimeRestrictionType;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeRepositoryInterface;

class TimeRestrictionRepository implements ContingentRuleTypeRepositoryInterface
{
    const TABLE_NAME = 'b2b_contingent_group_rule_time_restriction';

    const TABLE_ALIAS = 'timeRestrictionType';

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
            ->addSelect($prefix . '.time_restriction as ' . $prefix . '_time_restriction')
            ->addSelect($prefix . '.value as ' . $prefix . '_value')
            ->addSelect($prefix . '.currency_factor as ' . $prefix . '_currency_factor');
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}
