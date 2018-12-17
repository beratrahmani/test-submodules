<?php declare(strict_types=1);

use Shopware\B2B\Common\MvcExtension\EnlightRequest;
use Shopware\B2B\Common\RestApi\RestRoutingService;

class Shopware_Controllers_Api_SwagB2BController extends \Shopware_Controllers_Api_Rest
{
    /**
     * @param $action
     */
    public function dispatch($action)
    {
        $args = new Enlight_Controller_ActionEventArgs([
            'subject'  => $this,
            'request'  => $this->Request(),
            'response' => $this->Response(),
        ]);

        $moduleName = ucfirst($this->Request()->getModuleName());

        $this->triggerPreDispatchEvents($args, $moduleName);

        $this->preDispatch();

        if ($this->isRequestDispatchable()) {
            /** @var RestRoutingService $subRouter */
            $subRouter = Shopware()->Container()->get('b2b_common.rest_routing_service');

            $data = $subRouter
                ->getDispatchable($this->Request()->getMethod(), $this->Request()->getPathInfo())
                ->dispatch(new EnlightRequest($this->Request()));

            $this->Request()->setDispatched(true);

            $this->postDispatch();

            $this->Response()
                ->setBody(json_encode($data), JSON_PRETTY_PRINT);
        }

        $this->triggerPostDispatchEvents($args, $moduleName);
    }

    /**
     * @internal
     * @param Enlight_Controller_ActionEventArgs $args
     * @param string $moduleName
     */
    protected function triggerPreDispatchEvents(Enlight_Controller_ActionEventArgs $args, string $moduleName)
    {
        Shopware()->Events()->notify(
            __CLASS__ . '_PreDispatch',
            $args
        );

        Shopware()->Events()->notify(
            __CLASS__ . '_PreDispatch_' . $moduleName,
            $args
        );

        Shopware()->Events()->notify(
            __CLASS__ . '_PreDispatch_' . $this->controller_name,
            $args
        );
    }

    /**
     * @internal
     * @param Enlight_Controller_ActionEventArgs $args
     * @param string $moduleName
     */
    protected function triggerPostDispatchEvents(Enlight_Controller_ActionEventArgs $args, string $moduleName)
    {
        // fire non-secure/legacy-PostDispatch-Events
        Shopware()->Events()->notify(
            __CLASS__ . '_PostDispatch_' . $this->controller_name,
            $args
        );

        Shopware()->Events()->notify(
            __CLASS__ . '_PostDispatch_' . $moduleName,
            $args
        );

        Shopware()->Events()->notify(
            __CLASS__ . '_PostDispatch',
            $args
        );
    }

    /**
     * @internal
     * @return bool
     */
    protected function isRequestDispatchable(): bool
    {
        return $this->Request()->isDispatched()
            && !$this->Response()->isRedirect();
    }
}
