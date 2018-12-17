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

namespace SwagAdvancedCart\Services;

use Enlight_Event_EventArgs;
use Enlight_Hook_HookArgs;

interface UserServiceInterface
{
    /**
     * Helper method to get the userID from arguments or from session.
     * If no user id return null
     *
     * @param Enlight_Event_EventArgs|Enlight_Hook_HookArgs $arguments
     *
     * @return int|null
     */
    public function getUserIdByArgument($arguments);

    /**
     * Helper Method to get the userID from session.
     * If no user id return null
     *
     * @return int|null
     */
    public function getSessionUserId();

    /**
     * @param int $userId
     *
     * @return array
     */
    public function getUserData($userId);
}
