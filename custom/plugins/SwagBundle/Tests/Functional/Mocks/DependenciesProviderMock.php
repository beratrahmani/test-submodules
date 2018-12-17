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

namespace SwagBundle\Tests\Functional\Mocks;

use Shopware\Components\DependencyInjection\Container;
use SwagBundle\Services\Dependencies\Provider;

class DependenciesProviderMock extends Provider
{
    /**
     * @var bool
     */
    private $noSession;

    /**
     * @var bool
     */
    private $noShop;

    /**
     * @param Container $container
     * @param bool      $noSession
     * @param bool      $noShop
     */
    public function __construct(Container $container, $noSession = false, $noShop = false)
    {
        parent::__construct($container);
        $this->noSession = $noSession;
        $this->noShop = $noShop;
    }

    /**
     * {@inheritdoc}
     */
    public function getSession()
    {
        if ($this->noSession) {
            return null;
        }

        return parent::getSession();
    }

    /**
     * {@inheritdoc}
     */
    public function getShop()
    {
        if ($this->noShop) {
            return null;
        }

        return parent::getShop();
    }
}
