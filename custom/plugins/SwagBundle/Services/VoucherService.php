<?php
/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

namespace SwagBundle\Services;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use SwagBundle\Services\Dependencies\ProviderInterface;

class VoucherService implements VoucherServiceInterface
{
    /**
     * @var \Enlight_Controller_Front
     */
    private $front;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ProviderInterface
     */
    private $dependenciesProvider;

    /**
     * @param \Enlight_Controller_Front $front
     * @param Connection                $connection
     * @param ProviderInterface         $dependenciesProvider
     */
    public function __construct(
        \Enlight_Controller_Front $front,
        Connection $connection,
        ProviderInterface $dependenciesProvider
    ) {
        $this->front = $front;
        $this->connection = $connection;
        $this->dependenciesProvider = $dependenciesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getVoucherDetails()
    {
        $voucherCode = $this->front->Request()->getParam('sVoucher');

        if (!$voucherCode) {
            return false;
        }

        return $this->getVoucherDetailsFromCode($voucherCode);
    }

    /**
     * {@inheritdoc}
     */
    public function isPercentalVoucherInBasket()
    {
        return $this->isPercentalVoucherAlreadyInBasket() || $this->isPercentalVoucherAdded();
    }

    /**
     * {@inheritdoc}
     */
    public function isCodeFromPercentalVoucher($voucherCode)
    {
        return (bool) $this->getVoucherDetailsFromCode($voucherCode)['percental'];
    }

    /**
     * Fetches the voucher-details depending on the voucher-code.
     *
     * @param string $voucherCode
     *
     * @return array|bool
     */
    private function getVoucherDetailsFromCode($voucherCode)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->connection->createQueryBuilder();
        $voucherDetails = $queryBuilder->select('vouchers.*')
            ->from('s_emarketing_vouchers', 'vouchers')
            ->where('vouchers.modus != 1')
            ->andWhere('LOWER(vouchers.vouchercode) = :code')
            ->andWhere('(valid_to >= CURDATE() AND valid_from <= CURDATE()) OR valid_to IS NULL')
            ->setParameter(':code', $voucherCode)
            ->execute()
            ->fetch();

        if ($voucherDetails) {
            return $voucherDetails;
        }

        $queryBuilder = $this->connection->createQueryBuilder();

        return $queryBuilder->select('vouchers.*')
            ->from('s_emarketing_vouchers', 'vouchers')
            ->innerJoin('vouchers', 's_emarketing_voucher_codes', 'voucherCode', 'voucherCode.voucherID = vouchers.id')
            ->where('LOWER(voucherCode.code) = :code')
            ->andWhere('(valid_to >= CURDATE() AND valid_from <= CURDATE()) OR valid_to IS NULL')
            ->setParameter(':code', $voucherCode)
            ->execute()
            ->fetch();
    }

    /**
     * Checks if a percental voucher is currently being added to the basket.
     *
     * @return bool
     */
    private function isPercentalVoucherAdded()
    {
        $voucherCode = $this->front->Request()->getParam('sVoucher');

        if (!$voucherCode) {
            return false;
        }

        return $this->isCodeFromPercentalVoucher($voucherCode);
    }

    /**
     * Checks if percental voucher is already in the basket.
     *
     * @return bool
     */
    private function isPercentalVoucherAlreadyInBasket()
    {
        $session = $this->dependenciesProvider->getSession();
        if (!$session) {
            return false;
        }

        $builder = $this->connection->createQueryBuilder();
        $result = (bool) $builder->select('voucher.percental')
            ->from('s_order_basket', 'basket')
            ->innerJoin('basket', 's_emarketing_vouchers', 'voucher', 'voucher.ordercode = basket.ordernumber')
            ->where('basket.sessionID = :sessionId')
            ->setParameter(':sessionId', $session->get('sessionId'))
            ->execute()
            ->fetchColumn();

        return $result;
    }
}
