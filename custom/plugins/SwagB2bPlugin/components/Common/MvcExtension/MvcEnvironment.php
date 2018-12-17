<?php declare(strict_types=1);

namespace Shopware\B2B\Common\MvcExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class MvcEnvironment
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getPathinfo(): string
    {
        /** @var \Enlight_Controller_Request_RequestHttp $request */
        $request = $this->container
            ->get('front')
            ->Request();

        return $request->getScheme()
            . '://'
            . $request->getHttpHost()
            . $request->getBaseUrl();
    }
}
