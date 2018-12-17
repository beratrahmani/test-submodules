<?php declare(strict_types=1);

namespace Shopware\B2B\Common\MvcExtension;

use Shopware\B2B\Common\Controller\B2bControllerRedirectException;

class EnlightRequest implements Request
{
    /**
     * @var \Enlight_Controller_Request_Request
     */
    private $request;

    /**
     * @var array wrapper for
     */
    private $files;

    /**
     * @param \Enlight_Controller_Request_Request $request
     * @param array $files
     */
    public function __construct(\Enlight_Controller_Request_Request $request, array $files = null)
    {
        $this->request = $request;
        $this->files = $files ?? $_FILES;
    }

    /**
     * @param $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        return $this->request->getParam($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function requireFileParam($key)
    {
        if (isset($this->files[$key])) {
            return $this->files[$key];
        }

        throw new \InvalidArgumentException('Missing required parameter "' . $key . '"');
    }

    /**
     * @param $key
     * @return mixed
     */
    public function requireParam($key)
    {
        $value = $this->getParam($key);

        if (!$value) {
            throw new \InvalidArgumentException('Missing required parameter "' . $key . '"');
        }

        return $value;
    }

    /**
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->request->isPost();
    }

    /**
     * @return array
     */
    public function getPost(): array
    {
        return $this->request->getPost();
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $_FILES;
    }

    /**
     * {@inheritdoc}
     */
    public function hasParam(string $key): bool
    {
        return $this->request->has($key);
    }

    /**
     * @throws B2bControllerRedirectException
     */
    public function checkPost(string $action = 'index', array $params = [], string $controller = null)
    {
        if ($this->isPost()) {
            return;
        }

        throw new B2bControllerRedirectException($action, $controller, null, $params);
    }
}
