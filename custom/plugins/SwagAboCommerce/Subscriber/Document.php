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
use Shopware\Models\Attribute\Order as OrderAttribute;
use SwagAboCommerce\Models\Order as AboOrder;
use SwagAboCommerce\Services\AboCommerceServiceInterface;

/**
 * Class Document
 */
class Document implements SubscriberInterface
{
    /**
     * @var AboCommerceServiceInterface
     */
    private $aboCommerceService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param AboCommerceServiceInterface $aboCommerceService
     * @param ModelManager                $modelManager
     */
    public function __construct(
        AboCommerceServiceInterface $aboCommerceService,
        ModelManager $modelManager
    ) {
        $this->aboCommerceService = $aboCommerceService;
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Components_Document::assignValues::before' => 'onBeforeRenderDocument',
        ];
    }

    /**
     * Fired before a document is being rendered.
     * In this case we can check if this document belongs to an abo order and if so, we can
     * assign AboCommerce specific variables to the document's view.
     *
     * @param \Enlight_Hook_HookArgs $args
     */
    public function onBeforeRenderDocument(\Enlight_Hook_HookArgs $args)
    {
        /** @var \Shopware_Components_Document $document */
        $document = $args->getSubject();

        if (!$document) {
            return;
        }

        $orderId = $document->_order->order['id'];

        if (!$this->aboCommerceService->isAboOrder($orderId)) {
            return;
        }

        $orderPositions = $document->_order->positions;
        $initialOrder = $this->aboCommerceService->isInitialOrder($orderId);

        // The initial order may contain several abo products, therefore we have to do the foreach loop.
        // Once the first order was delivered it will become one separate order with only one product.
        // Actually the database mapping between abo-orders and order details is not comfortable with upcoming orders
        // since the productOrderDetailId always points to the initial order detail and will never be updated later on.
        if ($initialOrder === true) {
            foreach ($orderPositions as &$orderPosition) {
                if ((int) $orderPosition['modus'] !== 0) {
                    continue;
                }

                /** @var \SwagAboCommerce\Models\Order $model */
                $model = $this->modelManager->getRepository(AboOrder::class)->findOneBy(['articleOrderDetailId' => $orderPosition['id']]);

                // This position does not have an active abo, so we have nothing to do here.
                if (!$model) {
                    continue;
                }

                $orderPosition['aboCommerce'] = $model->toArray('d.m.Y');
                $orderPosition['aboCommerce']['currentDelivery'] = $this->getCurrentDelivery(
                    $orderPosition['aboCommerce']['id'],
                    $orderPosition['orderID']
                );
            }

            return;
        }

        // Check which position has the correct mode and assign the abo data to it.
        foreach ($orderPositions as &$position) {
            // We only have one product in this order, so we simply can obtain the whole model and assign it.
            if ((int) $position['modus'] === 0) {
                /** @var \SwagAboCommerce\Models\Order $model */
                $model = $this->modelManager->getRepository(AboOrder::class)->findOneBy(['lastOrderId' => $orderId]);

                // If a matching entity was found, it must have been the last-order of that abo.
                // Otherwise, there still might be an abo-order, which just isn't the "last-order",
                if (!$model) {
                    $orderAttribute = $this->modelManager
                        ->getRepository(OrderAttribute::class)->findOneBy(['orderId' => $orderId]);

                    $aboCommerceId = $orderAttribute->getSwagAboCommerceId();

                    if (!$aboCommerceId) {
                        continue;
                    }

                    $model = $this->modelManager->getRepository(AboOrder::class)->find($aboCommerceId);
                }

                $position['aboCommerce'] = $model->toArray('d.m.Y');
                $position['aboCommerce']['currentDelivery'] = $this->getCurrentDelivery(
                    $position['aboCommerce']['id'],
                    $position['orderID']
                );

                // The one and only product was found, it's not required to continue the search.
                break;
            }
        }
    }

    /**
     * Returns the current delivery-count. E.g. it's the 2nd order of an abo, so it returns "2".
     *
     * @param string $aboId
     * @param string $orderId
     *
     * @return int
     */
    private function getCurrentDelivery($aboId, $orderId)
    {
        $orders = $this->collectOrdersByAboId($aboId);

        foreach ($orders as $index => $order) {
            if ((int) $order === (int) $orderId) {
                return $index + 1;
            }
        }

        return 0;
    }

    /**
     * Collects all orders by the given abo-id.
     * We need to check both the s_order_attributes as well as the s_plugin_swag_abo_commerce_orders-table.
     *
     * @param string $aboId
     *
     * @return array
     */
    private function collectOrdersByAboId($aboId)
    {
        $orderSql = '
            SELECT * FROM s_order WHERE id IN(
                SELECT order_id FROM s_plugin_swag_abo_commerce_orders WHERE id = :aboId
                    UNION
                SELECT orderID FROM s_order_attributes WHERE swag_abo_commerce_id = :aboId
            )
            ORDER BY ordertime ASC;';

        return $this->modelManager->getConnection()->fetchColumn($orderSql, ['aboId' => $aboId]);
    }
}
