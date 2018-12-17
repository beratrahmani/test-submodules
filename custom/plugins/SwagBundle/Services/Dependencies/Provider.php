<?php
/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

namespace SwagBundle\Services\Dependencies;

use Shopware\Components\DependencyInjection\Container;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;

class Provider implements ProviderInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getArticlesModule()
    {
        /** @var \Shopware_Components_Modules $modules */
        $modules = $this->container->get('modules');

        return $modules->Articles();
    }

    /**
     * {@inheritdoc}
     */
    public function getBasketModule()
    {
        /** @var \Shopware_Components_Modules $modules */
        $modules = $this->container->get('modules');

        return $modules->Basket();
    }

    /**
     * {@inheritdoc}
     */
    public function getShop()
    {
        if ($this->hasShop()) {
            return $this->container->get('shop');
        }

        /** @var ShopRepository $shopRepository */
        $shopRepository = $this->container->get('models')->getRepository(Shop::class);

        return $shopRepository->getActiveDefault();
    }

    /**
     * {@inheritdoc}
     */
    public function hasShop()
    {
        return $this->container->has('shop');
    }

    /**
     * {@inheritdoc}
     */
    public function getSession()
    {
        return $this->container->get('session');
    }
}
