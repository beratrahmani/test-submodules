<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Shopware\Models\Mail\Mail;
use Shopware_Components_Translation;
use Symfony\Component\DependencyInjection\Container;

class Migration1493892870BudgetMail implements MigrationStepInterface
{
    /**
     * @return int
     */
    public function getCreationTimeStamp(): int
    {
        return 1493892870;
    }

    /**
     * @param Connection $connection
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->query('
            CREATE TABLE `b2b_budget_notify` (
                `budget_id` INT(11) NOT NULL,
                `refresh_group` INT(11) NOT NULL,
                `time` DATETIME NOT NULL,
            
                PRIMARY KEY (`budget_id`, `refresh_group`),
                INDEX `FK_b2b_budget_notify_b2b_budget` (`budget_id`),
            
                CONSTRAINT `FK_b2b_budget_notify_b2b_budget` FOREIGN KEY (`budget_id`) 
                  REFERENCES `b2b_budget` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB
        ');
    }

    /**
     * @param Container $container
     */
    public function updateThroughServices(Container $container)
    {
        $modelManager = $container->get('models');

        $mailRepo = $modelManager->getRepository('Shopware\Models\Mail\Mail');

        if ($mailRepo->findOneBy(['name' => 'b2bBudgetNotify'])) {
            return;
        }

        $fixturePath  = __DIR__ . '/../../../Budget/Fixtures/';

        $mail = new Mail();

        $mail->fromArray([
            'name' => 'b2bBudgetNotify',
            'isHtml' => false,
            'subject' => 'Budget ({$budget.name}) hat die angegebene Prozentzahl erreicht',
            'fromMail' => '{config name=mail}',
            'fromName' => '{config name=shopName}',
            'content' => file_get_contents($fixturePath . 'plain_de.tpl'),
            'mailType' => $mail::MAILTYPE_SYSTEM,
        ]);

        $modelManager->persist($mail);
        $modelManager->flush();

        $query = $container->get('dbal_connection')->createQueryBuilder();

        $englishShopIds = $query->select('shops.id')
            ->from('s_core_shops', 'shops')
            ->leftJoin('shops', 's_core_locales', 'locales', 'shops.locale_id = locales.id')
            ->where('locales.locale = :locale')
            ->setParameter('locale', 'en_GB')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        if (count($englishShopIds) === 0) {
            return;
        }

        /** @var Mail $mail */
        $mail = $mailRepo->findOneBy(['name' => 'b2bBudgetNotify']);

        $translation = new Shopware_Components_Translation();

        $translations = [
            'subject' => 'Budget ({$budget.name}) notify percentage reached',
            'content' => file_get_contents($fixturePath . 'plain_en.tpl'),
        ];

        foreach ($englishShopIds as $englishShopId) {
            $translation->write($englishShopId, 'config_mails', $mail->getId(), $translations);
        }
    }
}
