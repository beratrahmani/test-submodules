<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Bridge;

use Shopware\B2B\StoreFrontAuthentication\Framework\AuthStorageAdapterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AuthStorageAdapterFactory
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
     * @return AuthStorageAdapterInterface
     */
    public function factory(): AuthStorageAdapterInterface
    {
        if (!$this->container->has('shop')) {
            return new ApiAuthStorageAdapter();
        }

        return new ShopSessionAuthStorageAdapter(
            $this->container->get('session'),
            $this->container->get('shopware_core.s_admin')
        );
    }
}
