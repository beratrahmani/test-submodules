<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\CategoryType;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeRepositoryInterface;

class CategoryRepository implements ContingentRuleTypeRepositoryInterface
{
    const TABLE_NAME = 'b2b_contingent_group_rule_category';

    const TABLE_ALIAS = 'categoryType';

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
            ->select(self::TABLE_ALIAS . '.*, category.description as category_name')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(self::TABLE_ALIAS, 's_categories', 'category', self::TABLE_ALIAS . '.category_id = category.id')
            ->getSQL();
    }

    /**
     * {@inheritdoc}
     */
    public function addSelect(QueryBuilder $query, string $prefix)
    {
        $query->addSelect($prefix . '.contingent_rule_id as ' . $prefix . '_contingent_rule_id')
            ->addSelect($prefix . '.category_id as ' . $prefix . '_category_id')
            ->addSelect($prefix . '.category_name as ' . $prefix . '_category_name');
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}
