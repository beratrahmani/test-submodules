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
use Enlight_Event_EventArgs;
use SwagAdvancedCart\Components\CookieProvider;
use SwagAdvancedCart\Services\BasketUtilsInterface;
use SwagAdvancedCart\Services\Dependencies\DependencyProviderInterface;
use SwagAdvancedCart\Services\UserServiceInterface;

/**
 * Class Order
 */
class Order implements SubscriberInterface
{
    /**
     * @var UserServiceInterface
     */
    private $user;

    /**
     * @var BasketUtilsInterface
     */
    private $basketUtils;
    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @param UserServiceInterface        $user
     * @param BasketUtilsInterface        $basketUtils
     * @param DependencyProviderInterface $dependencyProvider
     */
    public function __construct(
        UserServiceInterface $user,
        BasketUtilsInterface $basketUtils,
        DependencyProviderInterface $dependencyProvider
    ) {
        $this->user = $user;
        $this->basketUtils = $basketUtils;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Order_SaveOrder_ProcessDetails' => 'onSaveOrder',
        ];
    }

    /**
     * deletes the saved basket after this items are ordered by the user
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onSaveOrder(Enlight_Event_EventArgs $args)
    {
        $userId = $this->user->getUserIdByArgument($args);
        if (!$userId) {
            return;
        }

        $cookieProvider = new CookieProvider(
            $this->basketUtils,
            $this->dependencyProvider->getFront()->Request(),
            $this->dependencyProvider->getFront()->Response(),
            $userId
        );

        $cookieValue = $cookieProvider->getCookieValueFromRequest();

        $basketId = $this->basketUtils->getSavedBasketId($cookieValue);

        $this->basketUtils->deleteBasketSavedItems($basketId);
        $this->basketUtils->deleteBasketSaved($basketId);
    }
}
