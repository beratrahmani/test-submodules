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

namespace SwagAboCommerce\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;
use Shopware_Components_TemplateMail;
use SwagAboCommerce\Models\Order as OrderModel;
use SwagAboCommerce\Services\AboCommerceServiceInterface;
use SwagAboCommerce\Services\DependencyProviderInterface;

class Order implements SubscriberInterface
{
    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var AboCommerceServiceInterface
     */
    private $aboCommerceService;

    /**
     * @var Shopware_Components_TemplateMail
     */
    private $templateMail;

    /**
     * @param DependencyProviderInterface      $dependencyProvider
     * @param AboCommerceServiceInterface      $aboCommerceService
     * @param ModelManager                     $modelManager
     * @param Shopware_Components_TemplateMail $templateMail
     */
    public function __construct(
        DependencyProviderInterface $dependencyProvider,
        AboCommerceServiceInterface $aboCommerceService,
        ModelManager $modelManager,
        Shopware_Components_TemplateMail $templateMail
    ) {
        $this->dependencyProvider = $dependencyProvider;
        $this->aboCommerceService = $aboCommerceService;
        $this->modelManager = $modelManager;
        $this->templateMail = $templateMail;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Order_SendMail_Create' => 'onOrderSendMailCreate',
            'sOrder::sSaveOrder::after' => 'onAfterSaveOrder',
            'Shopware_Modules_Order_SaveOrder_ProcessDetails' => 'onOrderSaveOrderProcessDetails',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $arguments
     *
     * @throws \RuntimeException
     *
     * @return \Zend_Mail
     */
    public function onOrderSendMailCreate(\Enlight_Event_EventArgs $arguments)
    {
        $session = $this->dependencyProvider->getSession();
        if (!$session->get('initialOrder') && (!$session->get('isRecurringAboOrder') || empty($session->get('aboId')))) {
            return;
        }

        $aboRow = $this->getAboRow($session->get('aboId'));

        if (empty($aboRow)) {
            throw new \RuntimeException('Could not find aboRow');
        }

        $context = $arguments->get('context');
        $context['aboCommerce'] = $aboRow;

        // sends abo mail also for initial order
        if ($session->offsetGet('initialOrder')) {
            $mail = $this->templateMail->createMail('sABOCOMMERCE', $context);
            $variables = $arguments->get('variables');

            $mail->addTo($variables['additional']['user']['email']);
            $mail->send();

            $session->offsetUnset('initialOrder');
            $session->offsetUnset('aboId');

            return;
        }

        // create new mail with other template
        return $this->templateMail->createMail('sABOCOMMERCE', $context);
    }

    /**
     * Hook on the shopware order class to update the user point score
     *
     * @param \Enlight_Hook_HookArgs $args
     */
    public function onAfterSaveOrder(\Enlight_Hook_HookArgs $args)
    {
        $orderNumber = $args->getReturn();

        $session = $this->dependencyProvider->getSession();

        if (!$session->get('isRecurringAboOrder') || empty($session->get('aboId'))) {
            return;
        }

        $lastOrderId = $this->getOrderId($orderNumber);

        $aboId = (int) $session->get('aboId');

        $this->modelManager->getConnection()->createQueryBuilder()
            ->update('s_order_attributes')
            ->set('swag_abo_commerce_id', ':aboId')
            ->set('orderID', ':orderId')
            ->setParameter('aboId', $aboId)
            ->where('orderId = :orderId')
            ->setParameter('orderId', $lastOrderId)
            ->execute();

        /** @var \SwagAboCommerce\Models\Order $order */
        $order = $this->modelManager->getRepository(OrderModel::class)->find($aboId);

        $order->run($lastOrderId);
        $this->modelManager->flush($order);

        $session->offsetUnset('isRecurringAboOrder');
        $session->offsetUnset('aboId');
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     *
     * @throws \RuntimeException
     */
    public function onOrderSaveOrderProcessDetails(\Enlight_Event_EventArgs $args)
    {
        $basketContent = $args->get('details');
        $orderModule = $args->get('subject');
        $orderNumber = $orderModule->sOrderNumber;
        $userData = $orderModule->sUserData;

        $orderRow = $this->getOrderRow($orderNumber);

        if (empty($orderRow)) {
            throw new \RuntimeException('Could not find orderId');
        }

        $userId = $orderRow['userId'];
        $orderId = $orderRow['orderId'];

        foreach ($basketContent as $aboBasketItem) {
            // not a abo-product? step over!
            if (empty($aboBasketItem['abo_attributes']['swagAboCommerceDeliveryInterval'])) {
                continue;
            }

            $discountOrderId = null;
            foreach ($basketContent as $basketItem) {
                if ($aboBasketItem['id'] == $basketItem['abo_attributes']['swagAboCommerceId']) {
                    $discountOrderId = $basketItem['orderDetailId'];
                }
            }

            $variant = $this->aboCommerceService->getVariantByOrderNumber($aboBasketItem['ordernumber']);

            if (!$variant) {
                continue;
            }

            $aboCommerceData = $this->aboCommerceService->getAboCommerceDataSelectedProduct($variant);

            $order = new OrderModel(
                $userId,
                $orderId,
                $aboBasketItem['orderDetailId'],
                $discountOrderId,
                $aboBasketItem['abo_attributes']['swagAboCommerceDuration'],
                $aboCommerceData['durationUnit'] ? $aboCommerceData['durationUnit'] : null,
                $aboBasketItem['abo_attributes']['swagAboCommerceDeliveryInterval'],
                $aboCommerceData['deliveryIntervalUnit'],
                $aboCommerceData['endlessSubscription'],
                $aboCommerceData['periodOfNoticeInterval'],
                $aboCommerceData['periodOfNoticeUnit'],
                $aboCommerceData['directTermination'],
                $userData['additional']['payment']['id'],
                $userData['billingaddress']['id'],
                $userData['shippingaddress']['id']
            );

            $this->modelManager->persist($order);
            $this->modelManager->flush($order);

            $session = $this->dependencyProvider->getSession();
            $session->offsetSet('initialOrder', true);
            $session->offsetSet('aboId', $order->getId());
        }
    }

    /**
     * @param int $aboId
     *
     * @return mixed
     */
    private function getAboRow($aboId)
    {
        return $this->modelManager->getConnection()->createQueryBuilder()
            ->select([
                'duration_unit AS durationUnit',
                'delivery_interval AS deliveryInterval',
                'delivery_interval_unit AS deliveryIntervalUnit',
                'duration',
                'created',
                'delivered',
                'last_run AS lastRun',
                'endless_subscription AS endlessSubscription',
                'period_of_notice_interval AS periodOfNoticeInterval',
                'period_of_notice_unit AS periodOfNoticeUnit',
                'direct_termination AS directTermination',
            ])->from('s_plugin_swag_abo_commerce_orders')
            ->where('id = :aboId')
            ->setParameter('aboId', $aboId)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $orderNumber
     *
     * @return mixed
     */
    private function getOrderRow($orderNumber)
    {
        return $this->modelManager->getConnection()->createQueryBuilder()
            ->select(['id AS orderId', 'userID AS userId'])
            ->from('s_order')
            ->where('ordernumber LIKE :orderNumber')
            ->setParameter('orderNumber', $orderNumber)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $orderNumber
     *
     * @return mixed
     */
    private function getOrderId($orderNumber)
    {
        return $this->modelManager->getConnection()->createQueryBuilder()
            ->select('id')
            ->from('s_order')
            ->where('ordernumber LIKE :orderNumber')
            ->setParameter('orderNumber', $orderNumber)
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);
    }
}
