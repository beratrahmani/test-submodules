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

namespace SwagPromotion\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Components_Session_Namespace as Session;
use Enlight_Hook_HookArgs as HookArgs;

class OrderSubscriber implements SubscriberInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Session    $session
     * @param Connection $connection
     */
    public function __construct(Session $session, Connection $connection)
    {
        $this->session = $session;
        $this->connection = $connection;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return ['sOrder::sSaveOrder::after' => 'afterSaveOrder'];
    }

    /**
     * @param HookArgs $args
     */
    public function afterSaveOrder(HookArgs $args)
    {
        $orderNumber = $args->getReturn();

        $this->saveAppliedPromotions($orderNumber);

        $this->invalidateVouchers();
    }

    /**
     * saves the applied promotions to prevent usage more than allowed for customer
     *
     * @param string $orderNumber
     */
    private function saveAppliedPromotions($orderNumber)
    {
        $promotionIds = $this->session->get('appliedPromotions');
        if (!$promotionIds) {
            return;
        }

        if (!$orderNumber) {
            return;
        }

        $sql = 'SELECT id FROM s_order WHERE `ordernumber` = :orderNumber';
        $orderId = $this->connection->fetchColumn($sql, ['orderNumber' => $orderNumber]);

        if (!$orderId) {
            return;
        }

        $valueString = '';
        $values = [];
        $counter = 1;
        $maxCount = count($promotionIds);
        foreach ($promotionIds as $promotionId) {
            $valueString .= '(?, ?, ?)';
            if ($counter != $maxCount) {
                $valueString .= ',';
            }
            $values[] = $this->session->get('sUserId');
            $values[] = $promotionId;
            $values[] = $orderId;
            ++$counter;
        }

        $sql = 'INSERT INTO s_plugin_promotion_customer_count
              (customer_id, promotion_id, order_id) VALUES
              ' . $valueString;

        $this->connection->executeQuery($sql, $values);
    }

    /**
     * invalidates vouchers used by customer
     */
    private function invalidateVouchers()
    {
        $vouchers = $this->session->get('promotionVouchers');
        $promotionIds = $this->session->get('appliedPromotions');

        if (!$promotionIds || !$vouchers) {
            return;
        }

        $sql = 'UPDATE s_emarketing_voucher_codes SET cashed = 1, userID= :userId WHERE voucherID = :voucherId AND `code` = :code';
        foreach ($vouchers as $id => $voucher) {
            if (in_array($voucher['promotionId'], $promotionIds)) {
                if ($voucher['mode'] == 1) {
                    $this->connection->executeQuery($sql, [
                        'userId' => $this->session->get('sUserId'),
                        'voucherId' => $voucher['voucherId'],
                        'code' => $voucher['code'],
                    ]);
                }
            }
        }

        $this->session->offsetUnset('promotionVouchers');
    }
}
