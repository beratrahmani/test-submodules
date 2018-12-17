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

namespace SwagAdvancedCart\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Components_Session_Namespace;
use Enlight_Controller_Request_RequestHttp;
use Enlight_Event_EventArgs;
use SwagAdvancedCart\Components\CookieProvider;
use SwagAdvancedCart\Components\OriginalBasketProvider;
use SwagAdvancedCart\Services\BasketUtilsInterface;
use SwagAdvancedCart\Services\Dependencies\DependencyProviderInterface;

/**
 * Class Account
 */
class Account implements SubscriberInterface
{
    /**
     * @var Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var BasketUtilsInterface
     */
    private $basketUtils;
    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * Account constructor.
     *
     * @param Enlight_Components_Session_Namespace $session
     * @param Connection                           $connection
     * @param BasketUtilsInterface                 $basketUtils
     * @param DependencyProviderInterface          $dependencyProvider
     */
    public function __construct(
        Enlight_Components_Session_Namespace $session,
        Connection $connection,
        BasketUtilsInterface $basketUtils,
        DependencyProviderInterface $dependencyProvider
    ) {
        $this->session = $session;
        $this->connection = $connection;
        $this->basketUtils = $basketUtils;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Admin_Login_Start' => 'onBeforeLogin',
            'Shopware_Modules_Admin_Login_Successful' => 'onLoginSuccessful',
            'Enlight_Controller_Action_PreDispatch_Frontend_Account' => 'onLogOutSuccessful',
        ];
    }

    /**
     * save old sessionId in session
     */
    public function onBeforeLogin()
    {
        $this->session->offsetSet('oldSessionId', $this->session->get('sessionId'));
    }

    /**
     * puts the saved basket to the original basket on user login
     * if already items in the original basket, the items will be merged
     *
     * @param Enlight_Event_EventArgs $arguments
     *
     * @throws \Enlight_Exception
     */
    public function onLoginSuccessful(Enlight_Event_EventArgs $arguments)
    {
        $user = $arguments->get('user');
        $userId = $user['id'];

        $cookieProvider = new CookieProvider(
            $this->basketUtils,
            $this->dependencyProvider->getFront()->Request(),
            $this->dependencyProvider->getFront()->Response(),
            $userId
        );

        $cookieProvider->setCookie();
        $cookieValue = $cookieProvider->getCookie()->getCookieValue();
        if ($cookieValue) {
            $originalBasketProvider = new OriginalBasketProvider(
                $this->basketUtils,
                $this->connection,
                $this->session,
                $this->dependencyProvider
            );

            $originalBasketProvider->onUserLogin($cookieValue);
        }

        $this->basketUtils->mergeBaskets($userId, $this->session, $cookieProvider);
    }

    /**
     * deletes the original basket on user logout
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function onLogOutSuccessful(Enlight_Event_EventArgs $arguments)
    {
        /** @var Enlight_Controller_Request_RequestHttp $request */
        $request = $arguments->get('subject')->Request();
        $actionName = $request->getActionName();
        if ($actionName !== 'logout' && $actionName !== 'ajax_logout') {
            return;
        }

        $originalBasketProvider = new OriginalBasketProvider(
            $this->basketUtils,
            $this->connection,
            $this->session,
            $this->dependencyProvider
        );

        $originalBasketProvider->onUserLogout();
    }
}
