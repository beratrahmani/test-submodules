<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Frontend;

use Shopware\B2B\Common\Controller\ControllerProxyInterface;
use Shopware\B2B\Common\MvcExtension\EnlightRequest;
use Shopware\B2B\Common\MvcExtension\RoutingInterceptor;

abstract class ControllerProxy extends \Enlight_Controller_Action implements ControllerProxyInterface
{
    /**
     * @return string
     */
    abstract protected function getControllerDiKey(): string;

    /**
     * @return object
     */
    protected function getController()
    {
        return $this->get($this->getControllerDiKey());
    }

    /**
     * @param string $action
     */
    public function dispatch($action)
    {
        (new RoutingInterceptor())->interceptException(
            $this,
            function () use ($action) {
                parent::dispatch($action);
            }
        );
    }

    /**
     * @param string $name
     * @param null $value
     * @return mixed|void
     */
    public function __call($name, $value = null)
    {
        $controller = $this->getController();
        $isAction = substr($name, -6) ===  'Action';
        $controllerHasAction = method_exists($controller, $name);

        if ($isAction && $controllerHasAction) {
            $this->View()->assign($controller->{$name}(new EnlightRequest($this->Request())));

            return;
        }

        return parent::__call($name, $value);
    }
}
