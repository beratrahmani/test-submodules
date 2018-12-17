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

use Shopware\Models\Shop\Shop;
use Shopware_Components_Modules;

interface DependencyProviderInterface
{
    /**
     * @return \Enlight_Components_Session_Namespace
     */
    public function getSession();

    /**
     * @return Shopware_Components_Modules
     */
    public function getModules();

    /**
     * @return array|false
     */
    public function getUserData();

    /**
     * @return Shop|null
     */
    public function getShop();

    /**
     * @return \Enlight_Controller_Front|mixed|null
     */
    public function getFront();
}
