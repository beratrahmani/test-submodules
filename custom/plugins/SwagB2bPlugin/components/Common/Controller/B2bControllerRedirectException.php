<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Controller;

class B2bControllerRedirectException extends \BadMethodCallException implements B2bControllerRoutingException
{
    /**
     * @var string
     */
    private $action;

    /**
     * @var string
     */
    private $controller;

    /**
     * @var string
     */
    private $module;

    /**
     * @var array
     */
    private $params;

    /**
     * @param string $action
     * @param string|null $controller
     * @param string|null $module
     * @param array $params
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct(
        string $action,
        string $controller = null,
        string $module = null,
        array $params = [],
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct(
            sprintf('Requesting redirect to %s/%s/%s with %s', $module, $controller, $action, print_r($params, true)),
            $code,
            $previous
        );
        $this->action = $action;
        $this->controller = $controller;
        $this->module = $module;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
