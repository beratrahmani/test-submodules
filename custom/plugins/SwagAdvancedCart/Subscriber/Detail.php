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
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\ConfigReader;
use SwagAdvancedCart\Models\Cart\Cart;
use SwagAdvancedCart\Services\ProductsAlsoInListServiceInterface;
use SwagAdvancedCart\Services\UserServiceInterface;

/**
 * Class Detail
 */
class Detail implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var UserServiceInterface
     */
    private $user;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var ProductsAlsoInListServiceInterface
     */
    private $alsoService;

    /**
     * @param string                             $pluginName
     * @param UserServiceInterface               $user
     * @param ModelManager                       $modelManager
     * @param ContextServiceInterface            $contextService
     * @param ConfigReader                       $configReader
     * @param ProductsAlsoInListServiceInterface $alsoService
     */
    public function __construct(
        $pluginName,
        UserServiceInterface $user,
        ModelManager $modelManager,
        ContextServiceInterface $contextService,
        ConfigReader $configReader,
        ProductsAlsoInListServiceInterface $alsoService
    ) {
        $this->pluginName = $pluginName;
        $this->user = $user;
        $this->modelManager = $modelManager;
        $this->contextService = $contextService;
        $this->configReader = $configReader;
        $this->alsoService = $alsoService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'onDetailController',
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onDetailController(Enlight_Event_EventArgs $args)
    {
        $config = $this->configReader->getByPluginName($this->pluginName);

        /** @var Enlight_Controller_Action $subject */
        $subject = $args->get('subject');
        /** @var Enlight_View_Default $view */
        $view = $subject->View();

        // Get Article Data from view
        $sArticle = $view->getAssign('sArticle');
        $orderNumber = $sArticle['ordernumber'];

        $productList = [];

        if ($config['alsoListShow']) {
            $productList = $this->alsoService->getAlsoProductsList($orderNumber);
        }

        $this->setToView($view, $productList);
    }

    /**
     * @param Enlight_View_Default $view
     * @param array                $fullArticle
     */
    private function setToView(Enlight_View_Default $view, $fullArticle)
    {
        $customerId = $this->user->getSessionUserId();
        $view->assign('allCartsByUser', $this->getCartsByUser($customerId));
        $view->assign('userId', $customerId);
        $view->assign('wishlistArticles', $fullArticle);
        $view->assign('perPage', 4);
    }

    /**
     * @param int $customerId
     *
     * @return array
     */
    private function getCartsByUser($customerId)
    {
        $shopId = $this->contextService->getShopContext()->getShop()->getId();
        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['cart', 'items'])
            ->from(Cart::class, 'cart')
            ->leftJoin('cart.customer', 'customer')
            ->leftJoin('cart.cartItems', 'items')
            ->where('customer.id = :customerId')
            ->andWhere('cart.shopId = :shopId')
            ->andWhere('cart.name != :name')
            ->setParameter('customerId', $customerId)
            ->setParameter('name', '')
            ->setParameter('shopId', $shopId);

        return $builder->getQuery()->getArrayResult();
    }
}
