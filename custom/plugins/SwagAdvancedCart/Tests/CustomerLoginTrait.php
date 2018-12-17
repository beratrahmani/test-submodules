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

namespace SwagAdvancedCart\Tests;

use Doctrine\DBAL\Connection;
use Enlight_Components_Session_Namespace;

trait CustomerLoginTrait
{
    /**
     * Logged in a customer
     */
    public function loginCustomer()
    {
        /** @var Enlight_Components_Session_Namespace $session */
        $session = Shopware()->Container()->get('session');
        $session->offsetSet('sUserId', 1);
        $session->offsetSet('sessionId', 'sessionId');
        $session->offsetSet('sUserPassword', 'a256a310bc1e5db755fd392c524028a8');
        $session->offsetSet('sUserMail', 'test@example.com');

        /** @var Connection $connection */
        $connection = Shopware()->Container()->get('dbal_connection');
        $connection->executeQuery('UPDATE s_user SET lastlogin = now() WHERE id=1');

        $isCustomerLoggedIn = Shopware()->Modules()->Admin()->sCheckUser();
        $this->assertTrue($isCustomerLoggedIn);
    }
}
