<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Routing;

class Route
{
    const METHOD_POST = 'POST';

    const METHOD_PUT = 'PUT';

    const METHOD_GET = 'GET';

    const METHOD_DELETE =  'DELETE';

    /**
     * @var string one of the constants METHOD_*
     */
    private $method;

    /**
     * @var string
     */
    private $query;

    /**
     * @var string
     */
    private $controller;

    /**
     * @var string
     */
    private $action;
    /**
     * @var array
     */
    private $paramOrder;

    /**
     * @param $method
     * @param $query
     * @param $controller
     * @param $action
     * @param array $paramOrder
     */
    public function __construct(
        string $method,
        string $query,
        string $controller,
        string $action,
        array $paramOrder = []
    ) {
        $this->method = $method;
        $this->query = $query;
        $this->controller = $controller;
        $this->action = $action;
        $this->paramOrder = $paramOrder;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getParamOrder(): array
    {
        return $this->paramOrder;
    }
}
