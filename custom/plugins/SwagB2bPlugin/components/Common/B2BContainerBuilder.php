<?php declare(strict_types=1);

namespace Shopware\B2B\Common;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class B2BContainerBuilder
{
    /**
     * @var DependencyInjectionConfiguration[]
     */
    private $registeredConfigurations = [];

    /**
     * @return B2BContainerBuilder
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param DependencyInjectionConfiguration $configuration
     */
    public function addConfiguration(DependencyInjectionConfiguration $configuration)
    {
        $configurationClass = get_class($configuration);

        if (isset($this->registeredConfigurations[$configurationClass])) {
            return;
        }

        $this->registeredConfigurations[$configurationClass] = $configuration;

        foreach ($configuration->getDependingConfigurations() as $configurations) {
            $this->addConfiguration($configurations);
        }
    }

    /**
     * @param ContainerBuilder $containerBuilder
     */
    public function registerConfigurations(ContainerBuilder $containerBuilder)
    {
        $loader = new XmlFileLoader(
            $containerBuilder,
            new FileLocator()
        );

        foreach ($this->registeredConfigurations as $name => $configuration) {
            $paramId = 'b2b_' . $name;

            if ($containerBuilder->hasParameter($paramId)) {
                continue;
            }

            foreach ($configuration->getServiceFiles($containerBuilder) as $serviceFilePath) {
                $loader->load($serviceFilePath);
            }

            foreach ($configuration->getCompilerPasses() as $compilerPass) {
                $containerBuilder->addCompilerPass($compilerPass);
            }

            $containerBuilder->setParameter($paramId, 1);
        }

        $this->registeredConfigurations = [];
    }
}
