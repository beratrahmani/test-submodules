<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Shopware_Components_Translation;
use Symfony\Component\DependencyInjection\Container;

class Migration1497431573AddClearancePayment implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1497431573;
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
        /** @var \Shopware\Components\Plugin\PaymentInstaller $installer */
        $paymentInstaller = $container->get('shopware.plugin_payment_installer');

        $options = [
            'name' => 'b2b_order_clearance_payment',
            'description' => 'Freigabe',
            'action' => '',
            'active' => 1,
            'position' => 0,
            'additionalDescription' => '',
        ];
        $payment = $paymentInstaller->createOrUpdate('SwagB2bPlugin', $options);

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

        $translation = new Shopware_Components_Translation();

        foreach ($englishShopIds as $englishShopId) {
            $translation->write($englishShopId, 'config_payment', $payment->getId(), ['description' => 'Clearance'], true);
        }
    }
}
