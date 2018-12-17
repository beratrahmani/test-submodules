<?php declare(strict_types=1);

namespace Shopware\B2B\Common\RestApi;

use Shopware\B2B\Common\Routing\Router;

class RestRoutingService
{
    const PATH_INFO_PREFIX = '/api/b2b';

    /**
     * @var Router
     */
    private $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param string $method
     * @param string $pathInfo
     * @return \Shopware\B2B\Common\Routing\Dispatchable
     */
    public function getDispatchable(string $method, string $pathInfo)
    {
        $subQuery = $this->extractSubRouteQueryString($pathInfo);

        return $this->router->match($method, $subQuery);
    }

    /**
     * @internal
     * @param string $queryString
     * @param $queryString
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function extractSubRouteQueryString(string $queryString): string
    {
        if (0 !== strpos($queryString, self::PATH_INFO_PREFIX)) {
            throw new \InvalidArgumentException(
                sprintf('Trying to create dispatchable with invalid query string prefix, "%s"', $queryString)
            );
        }

        return substr($queryString, strlen(self::PATH_INFO_PREFIX));
    }
}
