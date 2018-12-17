<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1517913593AddSortingColumnLineItemReference implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1517913593;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            ALTER TABLE `b2b_line_item_reference`
            ADD COLUMN `sort` INT(11) DEFAULT NULL;
        ');

        $lineItemLists = $connection->query('
            SELECT * FROM `b2b_line_item_list`
        ')->fetchAll();

        foreach ($lineItemLists as $lineItemList) {
            $items = $connection->createQueryBuilder()
                ->select('*')
                ->from('b2b_line_item_reference', 'reference')
                ->where('reference.list_id = :lineItemListId')
                ->setParameter('lineItemListId', $lineItemList['id'])
                ->orderBy('id')
                ->execute()
                ->fetchAll();
            
            foreach ($items as $key => $item) {
                $connection->update(
                    'b2b_line_item_reference',
                    ['sort' => $key + 1],
                    ['id' => $item['id']]
                );
            }
        }
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
    }
}
