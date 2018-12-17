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

namespace SwagPromotion\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs as EventArgs;
use SwagPromotion\Components\Services\DependencyProviderInterface;
use SwagPromotion\Components\Services\FreeGoodsService;

class Checkout implements SubscriberInterface
{
    /**
     * @var FreeGoodsService
     */
    private $freeGoodsService;

    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param FreeGoodsService            $freeGoodsService
     * @param DependencyProviderInterface $dependencyProvider
     * @param Connection                  $connection
     */
    public function __construct(
        FreeGoodsService $freeGoodsService,
        DependencyProviderInterface $dependencyProvider,
        Connection $connection
    ) {
        $this->freeGoodsService = $freeGoodsService;
        $this->dependencyProvider = $dependencyProvider;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Basket_UpdateArticle_Start' => 'onUpdateArticle',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onCart',
        ];
    }

    /**
     * @param EventArgs $args
     */
    public function onUpdateArticle(EventArgs $args)
    {
        $basketId = $args->get('id');
        $quantity = $args->get('quantity');

        $updateSuccess = $this->freeGoodsService->updateFreeGoodsItem($basketId, $quantity);

        if (!$updateSuccess) {
            return;
        }
    }

    /**
     * Refresh the basket
     */
    public function onCart(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->getSubject();

        if ($subject->Request()->getActionName() !== 'changeQuantity') {
            return;
        }

        if ($this->isAnyShippingFreePromotionActive()) {
            $this->dependencyProvider->getModules()->Basket()->sRefreshBasket();
        }
    }

    /**
     * @return bool
     */
    private function isAnyShippingFreePromotionActive()
    {
        $shippingFreePromotions = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('s_plugin_promotion')
            ->where('type = :type')
            ->andWhere('active = 1')
            ->setParameter('type', 'basket.shippingfree')
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        return count($shippingFreePromotions) > 0;
    }
}
