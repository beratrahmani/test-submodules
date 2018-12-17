<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Routing;

use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\DependencyInjection\Container;

class RouteDispatchable implements Dispatchable
{
    /**
     * @var Route
     */
    private $route;
    /**
     * @var array
     */
    private $paramValues;
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     * @param Route $route
     * @param array $paramValues
     */
    public function __construct(
        Container $container,
        Route $route,
        array $paramValues = []
    ) {
        $this->route = $route;
        $this->paramValues = $paramValues;
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @throws \Exception
     * @return mixed
     */
    public function dispatch(
        Request $request
    ) {
        $callable = $this->createCallable();
        $params = $this->createParams([$request]);

        return call_user_func_array($callable, $params);
    }

    /**
     * @internal
     * @throws NotFoundException
     * @return array
     */
    protected function createCallable(): callable
    {
        $controller = $this->container->get($this->route->getController());

        $action = $this->route->getAction() . 'Action';

        if (!method_exists($controller, $action)) {
            throw new NotFoundException();
        }

        return [$controller, $action];
    }

    /**
     * @internal
     * @param array $defaultParams
     * @return array
     */
    protected function createParams(array $defaultParams = [])
    {
        $params = [];

        foreach ($this->route->getParamOrder() as $paramName) {
            if (!isset($this->paramValues[$paramName])) {
                $params[] = null;
                continue;
            }

            $params[] = urldecode($this->paramValues[$paramName]);
        }

        return array_merge($params, $defaultParams);
    }
}
