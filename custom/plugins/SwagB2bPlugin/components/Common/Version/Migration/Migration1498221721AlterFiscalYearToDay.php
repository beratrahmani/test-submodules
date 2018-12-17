<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1498221721AlterFiscalYearToDay implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1498221721;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $fiscalYears = $connection->createQueryBuilder()
            ->select('budget.id, budget.fiscal_year')
            ->from('b2b_budget', 'budget')
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        $connection->exec('
            ALTER TABLE `b2b_budget`
              CHANGE COLUMN `fiscal_year` `fiscal_year` DATE NOT NULL;
        ');

        foreach ($fiscalYears as $id => $month) {
            $fiscalYear = (string) (new \DateTime())->format('Y-' . ($month + 1) . '-01');
            $connection->update(
                'b2b_budget',
                ['fiscal_year' => $fiscalYear],
                ['id' => $id]
            );
        }
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
    }
}
