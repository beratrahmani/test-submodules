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

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_Action;
use Enlight_Event_EventArgs;
use Enlight_View_Default;
use SwagAdvancedCart\Services\BasketUtilsInterface;
use SwagAdvancedCart\Services\UserServiceInterface;

class Checkout implements SubscriberInterface
{
    /**
     * @var BasketUtilsInterface
     */
    private $basketUtils;

    /**
     * @var UserServiceInterface
     */
    private $user;

    /**
     * @param BasketUtilsInterface $basketUtils
     * @param UserServiceInterface $user
     */
    public function __construct(
        BasketUtilsInterface $basketUtils,
        UserServiceInterface $user
    ) {
        $this->basketUtils = $basketUtils;
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onCheckout',
        ];
    }

    /**
     * Adding save option on checkout process if user is logged in
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onCheckout(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Action $subject */
        $subject = $args->get('subject');

        /** @var Enlight_View_Default $view */
        $view = $subject->View();
        $action = $subject->Request()->getActionName();

        if ($action !== 'cart' && $action !== 'confirm') {
            return;
        }

        $userID = $this->user->getSessionUserId();

        $wishLists = $this->basketUtils->loadWishList($userID);
        $view->assign('wishlists', $wishLists);

        $userData = $this->user->getUserData($userID);

        $data = [
            'eMail' => $userData['email'],
            'name' => $userData['firstname'] . ' ' . $userData['lastname'],
        ];

        $view->assign($data);
    }
}
