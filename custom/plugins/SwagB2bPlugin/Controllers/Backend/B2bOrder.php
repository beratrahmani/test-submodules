<?php declare(strict_types=1);

use Shopware\B2B\Common\Repository\NotFoundException;

class Shopware_Controllers_Backend_B2bOrder extends Shopware_Controllers_Backend_ExtJs
{
    public function fetchOrderContextBackendDataAction()
    {
        if (!$this->Request()->isPost()) {
            throw new \InvalidArgumentException('no post');
        }

        $orderNumber = $this->Request()->getParam('orderNumber');

        if (!$orderNumber) {
            throw new \InvalidArgumentException('no order number given');
        }

        $repository = $this->container->get('b2b_order.order_context_repository');

        $orderContext = null;
        try {
            $orderContext = $repository->fetchOneOrderContextByOrderNumber($orderNumber);
        } catch (NotFoundException $e) {
            //nth
        }

        $this->View()->assign([
            'success' => true, 'orderContext' => $orderContext,
        ]);
    }

    public function saveOrderContextBackendDataAction()
    {
        if (!$this->Request()->isPost()) {
            throw new \InvalidArgumentException('no post');
        }

        $orderNumber = $this->Request()->getParam('orderNumber');

        if (!$orderNumber) {
            throw new \InvalidArgumentException('no order number given');
        }

        $repository = $this->container->get('b2b_order.order_context_repository');

        $orderContext = $repository->fetchOneOrderContextByOrderNumber($orderNumber);

        $orderContext->requestedDeliveryDate = $this->Request()->getParam('requestedDeliveryDate');
        $orderContext->orderReference = $this->Request()->getParam('orderReference');

        $repository->updateContext($orderContext);

        $this->View()->assign([
            'success' => true, 'orderContext' => $orderContext,
        ]);
    }
}
