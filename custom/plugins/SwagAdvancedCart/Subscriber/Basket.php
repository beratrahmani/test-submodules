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
use Enlight_Hook_HookArgs;
use SwagAdvancedCart\Components\CookieProvider;
use SwagAdvancedCart\Components\StaticSavedItemUpdater;
use SwagAdvancedCart\Services\BasketUtilsInterface;
use SwagAdvancedCart\Services\Dependencies\DependencyProviderInterface;
use SwagAdvancedCart\Services\UserServiceInterface;

/**
 * Class Basket
 */
class Basket implements SubscriberInterface
{
    const EVENT_PLACE_ITEM_INTO_SAVED_BASKET = __CLASS__ . '::saved';

    const EVENT_REMOVE_ITEM_FROM_SAVED_BASKET = __CLASS__ . '::removed';

    const EVENT_UPDATE_ITEM_IN_BASKET = __CLASS__ . '::update';

    /**
     * @var BasketUtilsInterface
     */
    private $basketUtils;

    /**
     * @var UserServiceInterface
     */
    private $user;

    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * Basket constructor.
     *
     * @param BasketUtilsInterface        $basketUtils
     * @param UserServiceInterface        $user
     * @param DependencyProviderInterface $dependencyProvider
     * @param \Enlight_Event_EventManager $eventManager
     */
    public function __construct(
        BasketUtilsInterface $basketUtils,
        UserServiceInterface $user,
        DependencyProviderInterface $dependencyProvider,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->basketUtils = $basketUtils;
        $this->user = $user;
        $this->dependencyProvider = $dependencyProvider;
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'sBasket::sAddArticle::after' => 'afterAddArticle',
            'sBasket::sDeleteArticle::before' => 'beforeDeleteArticle',
            'Shopware_Modules_Basket_UpdateArticle_FilterSqlDefault' => 'onUpdateArticleQuantity',
        ];
    }

    /**
     * deletes item from saved basket
     *
     * @param Enlight_Hook_HookArgs $args
     */
    public function beforeDeleteArticle(Enlight_Hook_HookArgs $args)
    {
        if ($this->eventManager->notifyUntil(self::EVENT_REMOVE_ITEM_FROM_SAVED_BASKET)) {
            return;
        }

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
        if (!$cookieValue) {
            return;
        }

        $originalBasketId = $args->get('id');

        $basketId = $this->basketUtils->getSavedBasketId($cookieValue);
        $orderNumber = $this->basketUtils->getArticleOrderNumberFromOriginalBasket($originalBasketId);

        $this->basketUtils->deleteBasketSavedItem($basketId, $orderNumber);
    }

    /**
     * updates item in saved basket
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onUpdateArticleQuantity(Enlight_Event_EventArgs $args)
    {
        if ($this->eventManager->notifyUntil(self::EVENT_UPDATE_ITEM_IN_BASKET)) {
            return;
        }

        $userId = $this->user->getUserIdByArgument($args);

        if (!$userId) {
            return;
        }

        $productNumber = $args->get('id');
        $productQuantity = $args->get('quantity');

        if (!StaticSavedItemUpdater::issetRequirements()) {
            $this->setRequirements($userId);
        }

        if (StaticSavedItemUpdater::isProductUpdated($productNumber)) {
            return;
        }

        StaticSavedItemUpdater::updateSavedBasketItemQuantity($productNumber, $productQuantity);
    }

    /**
     * adds new item to the saved basket
     *
     * @param Enlight_Hook_HookArgs $args
     */
    public function afterAddArticle(Enlight_Hook_HookArgs $args)
    {
        if ($this->eventManager->notifyUntil(self::EVENT_PLACE_ITEM_INTO_SAVED_BASKET)) {
            return;
        }

        if ($this->dependencyProvider->getSession()->get('Bot')) {
            return;
        }

        if (StaticSavedItemUpdater::isProductUpdated($args->getReturn())) {
            return;
        }

        $userId = $this->user->getUserIdByArgument($args);
        if (!$userId) {
            $this->addUnregisteredCustomerBasket($args);

            return;
        }

        $cookieProvider = new CookieProvider(
            $this->basketUtils,
            $this->dependencyProvider->getFront()->Request(),
            $this->dependencyProvider->getFront()->Response(),
            $userId
        );

        $cookieValue = $cookieProvider->getCookieValueFromRequest();

        if (!$cookieValue) {
            $cookieProvider->setCookie();
            $cookieValue = $cookieProvider->getCookie()->getCookieValue();
        }

        $basketId = $this->basketUtils->getSavedBasketId($cookieValue);

        if (!$basketId) {
            $this->basketUtils->createBasket($cookieValue, $userId, $cookieProvider->getCookie()->getExpireTime());
            $basketId = $this->basketUtils->getSavedBasketId($cookieValue);
        }

        $orderNumber = $args->get('id');
        $quantity = $this->getQuantityFromArguments($args);

        $this->saveBasketItem($basketId, $orderNumber, $quantity);
    }

    /**
     * @param Enlight_Hook_HookArgs $args
     */
    private function addUnregisteredCustomerBasket(Enlight_Hook_HookArgs $args)
    {
        /** @var \Enlight_Components_Session_Namespace $session */
        $session = $this->dependencyProvider->getSession();
        if (!$session) {
            return;
        }

        $dateTime = new \DateTime();
        $expires = $dateTime->getTimestamp();

        $orderNumber = $args->get('id');
        $quantity = $this->getQuantityFromArguments($args);

        $basketId = $this->basketUtils->getSavedBasketId($session->get('sessionId'));

        if (!$basketId) {
            $this->basketUtils->createBasket($session->get('sessionId'), -1, $expires);
            $basketId = $this->basketUtils->getSavedBasketId($session->get('sessionId'));
        }

        $this->saveBasketItem($basketId, $orderNumber, $quantity);
    }

    /**
     * @param Enlight_Hook_HookArgs $args
     *
     * @return int
     */
    private function getQuantityFromArguments(Enlight_Hook_HookArgs $args)
    {
        $quantity = $args->get('quantity');
        if ($quantity === null) {
            $quantity = 1;
        }

        return (int) $quantity;
    }

    /**
     * saves the given item to the saved basket, if already exists, increase the quantity
     *
     * @param $basketId
     * @param $orderNumber
     * @param $quantity
     */
    private function saveBasketItem($basketId, $orderNumber, $quantity)
    {
        // Check if product is already in remote basket
        if (!$this->basketUtils->checkIfOrderNumberExists($basketId, $orderNumber)) {
            // product not stored in remote basket
            $this->basketUtils->createBasketItem($basketId, $orderNumber, $quantity);
        } else {
            // Product is in basket -> quantity + 1
            $this->basketUtils->increasingArticleQuantity($quantity, $basketId, $orderNumber);
        }
    }

    /**
     * @param $userId
     */
    private function setRequirements($userId)
    {
        StaticSavedItemUpdater::setRequirements($this->basketUtils, $userId);
    }
}
