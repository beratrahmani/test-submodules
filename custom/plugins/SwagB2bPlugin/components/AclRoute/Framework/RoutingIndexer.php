<?php declare(strict_types=1);

namespace Shopware\B2B\AclRoute\Framework;

use Shopware\B2B\Common\Controller\ControllerProxyInterface;

class RoutingIndexer
{
    const TYPE_NOT_TAGGED = '_NOT_TAGGED_';
    /**
     * Privilege to create assignments between with this record as a subresource
     */
    const PRIVILEGE_ASSIGN = 'assign';

    /**
     * Privilege to allow this action always
     */
    const PRIVILEGE_FREE = 'free';

    /**
     * @param string $fullControllerClassName
     * @param string $configFilePath
     * @param string|null $resourceName
     * @return array
     */
    public function generate(string $fullControllerClassName, string $configFilePath, string $resourceName = null): array
    {
        $config = $this->loadConfig($configFilePath);

        $controllerName = $this->determineControllerName($fullControllerClassName);
        $resourceName = $this->determineResourceName($controllerName, $config, $resourceName);

        $config = $this->prepareConfig($resourceName, $config, $controllerName);
        $config = $this->updateConfig($fullControllerClassName, $resourceName, $config, $controllerName);

        $this->writeConfig($configFilePath, $config);

        return $config;
    }

    /**
     * @internal
     * @param string $controllerName
     * @param array $config
     * @param string|null $resourceName
     * @return string
     */
    protected function determineResourceName(string $controllerName, array $config, string $resourceName = null): string
    {
        if ($resourceName) {
            return $resourceName;
        }

        foreach ($config as $possibleResourceName => $controllers) {
            if (array_key_exists($controllerName, $config[$possibleResourceName])) {
                return $possibleResourceName;
            }
        }

        return self::TYPE_NOT_TAGGED;
    }

    /**
     * @internal
     * @param string $fullControllerClassName
     * @return string
     */
    protected function determineControllerName(string $fullControllerClassName): string
    {
        return str_replace('Shopware_Controllers_Frontend_', '', $fullControllerClassName);
    }

    /**
     * @internal
     * @param string $name
     * @return bool
     */
    protected function nameEndsWithAction(string $name): bool
    {
        return substr($name, 0 - strlen('Action')) === 'Action';
    }

    /**
     * @internal
     * @param string $resourceName
     * @param $config
     * @param $controllerName
     * @return mixed
     */
    protected function prepareConfig(string $resourceName, array $config, string $controllerName): array
    {
        if (!isset($config[$resourceName])) {
            $config[$resourceName] = [];
        }

        if (!isset($config[$resourceName][$controllerName])) {
            $config[$resourceName][$controllerName] = [];

            return $config;
        }

        return $config;
    }

    /**
     * @internal
     * @param string $configFilePath
     * @return array
     */
    protected function loadConfig(string $configFilePath): array
    {
        $config = [];

        if (file_exists($configFilePath)) {
            $config = include $configFilePath;
        }

        if (!is_array($config)) {
            throw new \RuntimeException('Unable to load file "' . $configFilePath . '" it does not contain an array');
        }

        return $config;
    }

    /**
     * @internal
     * @param string $fullControllerClassName
     * @param string $resourceName
     * @param array $config
     * @param string $controllerName
     * @return array
     */
    protected function updateConfig(string $fullControllerClassName, string $resourceName, array $config, string $controllerName): array
    {
        if (is_subclass_of($fullControllerClassName, ControllerProxyInterface::class, true)) {
            $baseClass = new \ReflectionClass($fullControllerClassName);
            $comment = $baseClass->getMethod('getControllerDiKey')->getDocComment();

            $start = strpos($comment, '@see ');

            if (!$start) {
                return $config;
            }

            $stop = strpos($comment, "\n", $start);

            $realControllerName = trim(substr($comment, $start + 6, $stop - $start));

            $reflection = new \ReflectionClass($realControllerName);
        } else {
            $reflection = new \ReflectionClass($fullControllerClassName);
        }

        $actions = $config[$resourceName][$controllerName];

        foreach ($reflection->getMethods() as $method) {
            if (!$method->isPublic()) {
                continue;
            }

            $methodName = $method->getName();

            if (!$this->nameEndsWithAction($methodName)) {
                continue;
            }

            $methodName = str_replace('Action', '', $methodName);

            if (array_key_exists($methodName, $actions)) {
                continue;
            }

            $actions[$methodName] = self::TYPE_NOT_TAGGED;
        }

        $config[$resourceName][$controllerName] = $actions;

        return $config;
    }

    /**
     * @internal
     * @param string $configFilePath
     * @param $config
     */
    protected function writeConfig(string $configFilePath, array $config)
    {
        file_put_contents(
            $configFilePath,
            '<?php declare (strict_types = 1); return ' . var_export($config, true) . ';'
        );
    }
}
