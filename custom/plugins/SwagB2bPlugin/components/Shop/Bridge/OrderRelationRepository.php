<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Bridge;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Repository\NotFoundException;

class OrderRelationRepository
{
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
     * @param int $shippingId
     * @throws NotFoundException
     * @return array
     */
    public function fetchShippingDataForId(int $shippingId): array
    {
        $shipping = $this->connection
            ->fetchAssoc('SELECT * FROM s_premium_dispatch WHERE id = :id', ['id' => $shippingId]);

        if (!$shipping) {
            throw new NotFoundException('Unable to find a shipping (dispatch) by id "' . $shippingId . '"');
        }

        return $shipping;
    }

    /**
     * @param int $paymentId
     * @throws NotFoundException
     * @return array
     */
    public function fetchPaymentDataForId(int $paymentId): array
    {
        $payment = $this->connection
            ->fetchAssoc('SELECT * FROM s_core_paymentmeans WHERE id = :id', ['id' => $paymentId]);


        if (!$payment) {
            throw new NotFoundException('Unable to find a payment by id "' . $paymentId . '"');
        }

        return $payment;
    }
}
