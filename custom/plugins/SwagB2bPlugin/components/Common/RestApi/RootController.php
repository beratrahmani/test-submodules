<?php declare(strict_types=1);

namespace Shopware\B2B\Common\RestApi;

use Shopware\B2B\Common\MvcExtension\MvcEnvironment;
use Shopware\B2B\Common\Routing\Router;

class RootController
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var MvcEnvironment
     */
    private $environment;

    /**
     * @param Router $router
     * @param MvcEnvironment $environment
     */
    public function __construct(
        Router $router,
        MvcEnvironment $environment
    ) {
        $this->router = $router;
        $this->environment = $environment;
    }

    /**
     * @return string[]
     */
    public function indexAction(): array
    {
        $return = [];
        $routes = $this->router->getRoutes();

        $apiBaseUrl = $this->environment->getPathinfo()
            . RestRoutingService::PATH_INFO_PREFIX;

        /** @var \Shopware\B2B\Common\Routing\Route $route */
        foreach ($routes as $route) {
            $query = $route->getQuery();

            $return[RestRoutingService::PATH_INFO_PREFIX . $query][] = [
                'method' => $route->getMethod(),
                'url' => $apiBaseUrl . $query,
                'params' => $route->getParamOrder(),
            ];
        }
        ksort($return);

        return $return;
    }
}
