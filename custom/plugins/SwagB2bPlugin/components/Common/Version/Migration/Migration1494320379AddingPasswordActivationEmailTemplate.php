<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Shopware\Models\Mail\Mail;
use Shopware_Components_Translation;
use Symfony\Component\DependencyInjection\Container;

class Migration1494320379AddingPasswordActivationEmailTemplate implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1494320379;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function updateThroughServices(Container $container)
    {
        $modelManager = $container->get('models');

        $mailRepo = $modelManager->getRepository('Shopware\Models\Mail\Mail');

        if ($mailRepo->findOneBy(['name' => 'b2bPasswordActivation'])) {
            return;
        }

        $fixturePath  = __DIR__ . '/../../../Contact/Fixtures/';

        $mail = new Mail();

        $mail->fromArray([
            'name' => 'b2bPasswordActivation',
            'isHtml' => true,
            'subject' => 'Aktivierung Ihres Benutzerkontos {$passwordActivation.email}.',
            'fromMail' => '{config name=mail}',
            'fromName' => '{config name=shopName}',
            'content' => file_get_contents($fixturePath . 'plain_de.tpl'),
            'contentHtml' => file_get_contents($fixturePath . 'html_de.tpl'),
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
        $mail = $mailRepo->findOneBy(['name' => 'b2bPasswordActivation']);

        $translation = new Shopware_Components_Translation();

        $translations = [
            'subject' => 'Account activation for {$passwordActivation.email}.',
            'content' => file_get_contents($fixturePath . 'plain_en.tpl'),
            'contentHtml' => file_get_contents($fixturePath . 'html_en.tpl'),
        ];

        foreach ($englishShopIds as $englishShopId) {
            $translation->write($englishShopId, 'config_mails', $mail->getId(), $translations);
        }
    }
}
