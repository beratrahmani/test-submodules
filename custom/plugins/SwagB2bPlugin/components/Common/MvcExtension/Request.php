<?php declare(strict_types=1);

namespace Shopware\B2B\Common\MvcExtension;

use Shopware\B2B\Common\Controller\B2bControllerRedirectException;

interface Request
{
    /**
     * @param $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getParam($key, $default = null);

    /**
     * @param $key
     * @return array
     */
    public function requireFileParam($key);

    /**
     * @param $key
     * @return mixed
     */
    public function requireParam($key);

    /**
     * @return bool
     */
    public function isPost(): bool;

    /**
     * @return array
     */
    public function getPost(): array;

    /**
     * @return array
     */
    public function getFiles(): array;

    /**
     * @param string $key
     * @return bool
     */
    public function hasParam(string $key): bool;

    /**
     * @throws B2bControllerRedirectException
     */
    public function checkPost(string $action = 'index', array $params = [], string $controller = null);
}
