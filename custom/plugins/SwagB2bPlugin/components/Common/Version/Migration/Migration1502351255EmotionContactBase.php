<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1502351255EmotionContactBase implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1502351255;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            ALTER TABLE `b2b_store_front_auth`
                ADD COLUMN `emotion_id` INT(11) DEFAULT NULL,
                ADD CONSTRAINT FK_b2b_store_front_auth_s_emotion
                FOREIGN KEY (`emotion_id`) REFERENCES `s_emotion`(`id`)
                ON DELETE SET NULL
                ON UPDATE NO ACTION;
        ');
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
    }
}
