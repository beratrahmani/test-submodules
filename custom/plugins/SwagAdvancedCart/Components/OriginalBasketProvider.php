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

namespace SwagAdvancedCart\Components;

use Doctrine\DBAL\Connection;
use Enlight_Components_Session_Namespace as Session;
use sBasket;
use SwagAdvancedCart\Services\BasketUtilsInterface;
use SwagAdvancedCart\Services\Dependencies\DependencyProviderInterface;

/**
 * Class OriginalBasketProvider
 *
 * provides operations for the original basket
 */
class OriginalBasketProvider
{
    /**
     * @var sBasket
     */
    private $sBasket;

    /**
     * @var BasketUtilsInterface
     */
    private $basketUtils;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Session
     */
    private $session;

    /**
     * initialise the original basket provider
     *
     * @param BasketUtilsInterface        $basketUtils
     * @param Connection                  $connection
     * @param Session                     $session
     * @param DependencyProviderInterface $dependencyProvider
     */
    public function __construct(
        BasketUtilsInterface $basketUtils,
        Connection $connection,
        Session $session,
        DependencyProviderInterface $dependencyProvider
    ) {
        $this->basketUtils = $basketUtils;
        $this->connection = $connection;
        $this->session = $session;
        $this->sBasket = $dependencyProvider->getModules()->Basket();
    }

    /**
     * deletes the current basket if the user logs out. his basket will be saved by AdvancedCart
     */
    public function onUserLogout()
    {
        $this->sBasket->sDeleteBasket();
    }

    /**
     * restores the saved basket on user login, if there is a saved basket
     *
     * @param $cookieValue
     *
     * @throws \Enlight_Exception
     */
    public function onUserLogin($cookieValue)
    {
        $basketID = $this->basketUtils->getSavedBasketId($cookieValue);
        if (!$basketID) {
            return;
        }

        $products = $this->basketUtils->getSavedBasketItems($basketID);

        foreach ($products as $product) {
            if (!$this->isArticleInBasket($product['article_ordernumber'])) {
                $this->sBasket->sAddArticle($product['article_ordernumber'], $product['quantity']);
            }
        }

        $this->sBasket->sRefreshBasket();
    }

    /**
     * @param string $productOrderNumber
     *
     * @return bool
     */
    private function isArticleInBasket($productOrderNumber)
    {
        $builder = $this->connection->createQueryBuilder();

        return (bool) $builder->select('quantity')
            ->from('s_order_basket', 'basket')
            ->andWhere('sessionID = :sessionId')
            ->andWhere('ordernumber = :ordernumber')
            ->andWhere('modus != 1')
            ->setParameter('sessionId', $this->session->offsetGet('sessionId'))
            ->setParameter('ordernumber', $productOrderNumber)
            ->execute()
            ->fetchColumn();
    }
}
