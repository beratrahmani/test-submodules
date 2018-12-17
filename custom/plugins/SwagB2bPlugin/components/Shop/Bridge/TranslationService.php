<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Bridge;

use Shopware\B2B\Shop\Framework\TranslationServiceInterface;
use Shopware_Components_Snippet_Manager;

class TranslationService implements TranslationServiceInterface
{
    /**
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @param Shopware_Components_Snippet_Manager $snippetManager
     */
    public function __construct(Shopware_Components_Snippet_Manager $snippetManager)
    {
        $this->snippetManager = $snippetManager;
    }

    /**
     * @param string $name
     * @param string $namespace
     * @param string $default
     *
     * @return string
     */
    public function get(string $name, string $namespace, string $default): string
    {
        $namespaceObject = $this->snippetManager->getNamespace($namespace);

        return $namespaceObject->get($name, $default) ?: $default;
    }
}
