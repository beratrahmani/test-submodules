<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Routing;

use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedDataGanerator;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as StandardRouteParser;
use Shopware\Components\DependencyInjection\Container;

class Router
{
    /**
     * @var RouteProvider[]
     */
    private $routeProviders;

    /**
     * @var RouteCollector|null
     */
    private $routeCollector;

    /**
     * @var GroupCountBasedDispatcher|null
     */
    private $dispatcher;

    /**
     * @var Route[]|null
     */
    private $routes;
    /**
     * @var Container
     */
    private $container;

    /**
     * @param array $routeProviders
     * @param Container $container
     */
    public function __construct(
        array $routeProviders,
        Container $container
    ) {
        $this->routeProviders = $routeProviders;
        $this->container = $container;
    }

    /**
     * @return Route[]
     */
    public function getRoutes(): array
    {
        if (!$this->routes) {
            $this->routes = [];

            foreach ($this->routeProviders as $routeProvider) {
                $rawRouteData = $routeProvider->getRoutes();

                foreach ($rawRouteData as $rawRoute) {
                    $this->routes[] = $this->createRoute($rawRoute);
                }
            }
        }

        return $this->routes;
    }

    /**
     * @param string $method
     * @param string $query
     * @throws \RuntimeException
     * @return Dispatchable
     */
    public function match($method, $query): Dispatchable
    {
        $result = $this->getDispatcher($this->getRouteCollector())->dispatch($method, $query);

        switch ($result[0]) {
            case Dispatcher::FOUND:
                return new RouteDispatchable($this->container, $result[1], $result[2]);
            case Dispatcher::METHOD_NOT_ALLOWED:
                return new NotAllowedDispatchable($query);
            case Dispatcher::NOT_FOUND:
                return new NotFoundDispatchable($query);
            default:
                throw new \RuntimeException('Missing mapping of dispatcher status "' . $result[0] . '"');
        }
    }

    /**
     * @internal
     * @return RouteCollector
     */
    protected function getRouteCollector(): RouteCollector
    {
        if (!$this->routeCollector) {
            $this->routeCollector = new RouteCollector(
                new StandardRouteParser(),
                new GroupCountBasedDataGanerator()
            );

            foreach ($this->getRoutes() as $route) {
                $this->routeCollector->addRoute($route->getMethod(), $route->getQuery(), $route);
            }
        }

        return $this->routeCollector;
    }

    /**
     * @internal
     * @param RouteCollector $routeCollector
     * @return GroupCountBasedDispatcher
     */
    protected function getDispatcher(RouteCollector $routeCollector)
    {
        if (!$this->dispatcher) {
            $this->dispatcher = new GroupCountBasedDispatcher($routeCollector->getData());
        }

        return $this->dispatcher;
    }

    /**
     * @internal
     * @param array $rawRouteData
     * @return Route
     */
    protected function createRoute(array $rawRouteData): Route
    {
        list($method, $query, $controller, $action) = $rawRouteData;

        $params = [];
        if (5 === count($rawRouteData)) {
            $params = $rawRouteData[4];
        }

        return new Route(
            $method,
            $query,
            $controller,
            $action,
            $params
        );
    }
}
