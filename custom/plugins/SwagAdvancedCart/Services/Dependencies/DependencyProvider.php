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

namespace SwagAdvancedCart\Services\Dependencies;

use Shopware\Components\DependencyInjection\Container;

class DependencyProvider implements DependencyProviderInterface
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
    public function getSession()
    {
        return $this->container->get('session');
    }

    /**
     * {@inheritdoc}
     */
    public function getModules()
    {
        if ($this->container->has('modules')) {
            return $this->container->get('modules');
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserData()
    {
        $modules = $this->getModules();
        if ($modules) {
            return $modules->Admin()->sGetUserData();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getShop()
    {
        if ($this->container->has('shop')) {
            return $this->container->get('shop');
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFront()
    {
        if ($this->container->has('front')) {
            return $this->container->get('front');
        }

        return null;
    }
}
