<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Repository\MysqlRepository;
use Shopware\B2B\Common\Repository\NotFoundException;

class BudgetNotificationRepository
{
    const TABLE_NAME = 'b2b_budget_notify';

    const TABLE_ALIAS = 'notify';

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
     * @param int $budgetId
     * @param int $refreshGroup
     * @param \DateTime $time
     */
    public function addNotify(int $budgetId, int $refreshGroup, \DateTime $time)
    {
        $this->connection->insert(
            self::TABLE_NAME,
            [
                'budget_id' => $budgetId,
                'refresh_group' => $refreshGroup,
                'time' => $time->format(MysqlRepository::MYSQL_DATETIME_FORMAT),
            ]
        );
    }

    /**
     * @param int $budgetId
     * @param int $refreshGroup
     * @throws NotFoundException
     * @return array
     */
    public function fetchNotifyByIdAndRefreshGroup(int $budgetId, int $refreshGroup): array
    {
        $query = $this->connection->createQueryBuilder();

        $notify = $query->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.budget_id = :budgetId AND ' . self::TABLE_ALIAS . '.refresh_group = :refreshGroup')
            ->setParameters([
                'budgetId' => $budgetId,
                'refreshGroup' => $refreshGroup,
            ])
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        if (!$notify) {
            throw new NotFoundException(sprintf(
                'No notification found for budget %d and refresh group %d',
                $budgetId,
                $refreshGroup
            ));
        }

        return $notify;
    }
}
