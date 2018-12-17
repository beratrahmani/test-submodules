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

namespace SwagAboCommerce\Subscriber;

use Doctrine\ORM\AbstractQuery;
use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Enlight_Event_EventArgs as EventArgs;
use Enlight_Event_EventManager;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order as OrderModel;
use SwagAboCommerce\Models\Product;
use SwagAboCommerce\Services\AboCommerceBasketServiceInterface;
use SwagAboCommerce\Services\AboCommerceServiceInterface;
use SwagAboCommerce\Services\DependencyProviderInterface;

class Checkout implements SubscriberInterface
{
    /**
     * @var AboCommerceServiceInterface
     */
    private $aboCommerceService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var AboCommerceBasketServiceInterface
     */
    private $aboCommerceBasketService;

    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @var Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @param AboCommerceServiceInterface       $aboCommerceService
     * @param AboCommerceBasketServiceInterface $aboCommerceBasketService
     * @param DependencyProviderInterface       $dependencyProvider
     * @param ModelManager                      $modelManager
     * @param Enlight_Event_EventManager        $eventManager
     */
    public function __construct(
        AboCommerceServiceInterface $aboCommerceService,
        AboCommerceBasketServiceInterface $aboCommerceBasketService,
        DependencyProviderInterface $dependencyProvider,
        ModelManager $modelManager,
        Enlight_Event_EventManager $eventManager
    ) {
        $this->aboCommerceService = $aboCommerceService;
        $this->aboCommerceBasketService = $aboCommerceBasketService;
        $this->dependencyProvider = $dependencyProvider;
        $this->modelManager = $modelManager;
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_Frontend_Checkout_AddArticle' => 'onAddProduct',
            'Enlight_Controller_Action_Frontend_Checkout_AjaxAddArticleCart' => 'onAddProduct',
            'Enlight_Controller_Action_Frontend_Checkout_AboFinish' => 'onAboFinish',
            'Enlight_Controller_Action_PreDispatch_Frontend_Checkout' => 'onPreDispatchFrontendCheckout',
        ];
    }

    /**
     * If the user select the option to buy the product as abo
     * execute the internal function, otherwise call the parent function
     *
     * @param EventArgs $args
     *
     * @return bool
     */
    public function onAddProduct(EventArgs $args)
    {
        /** @var $controller \Shopware_Controllers_Frontend_Checkout */
        $controller = $args->get('subject');
        $request = $controller->Request();
        $fromCart = (bool) $request->getParam('swAboCommerceAddArticleFromCart', false);
        $deliveryInterval = (int) $request->getParam('sDeliveryInterval', 0);
        $duration = (int) $request->getParam('sDurationInterval', 0);
        $endlessSubscription = (bool) $request->getParam('sEndlessSubscription', false);

        $orderNumber = trim($request->getParam('sAdd'));
        $variant = $this->aboCommerceService->getVariantByOrderNumber($orderNumber);
        if (null === $variant) {
            return null;
        }

        $productId = $variant->getArticle()->getId();

        if ($fromCart && $this->aboCommerceService->getIsAboExclusive($productId)) {
            $controller->redirect([
                'controller' => 'detail',
                'action' => 'index',
                'sArticle' => $productId,
                'number' => $orderNumber,
            ]);

            return false;
        }

        // No AboCommerce-product. Proceed normal;
        if (0 === $deliveryInterval || (!$endlessSubscription && 0 === $duration)) {
            return null;
        }

        if (true === $endlessSubscription) {
            $duration = null;
        }

        /** @var array $aboProduct */
        $aboProduct = $this->modelManager->getRepository(Product::class)
            ->getActiveAboProductByProductIdQuery($productId)
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        if (empty($aboProduct)) {
            return null;
        }

        $view = $controller->View();
        $quantity = (int) $request->getParam('sQuantity', 1);

        // Check if AboCommerce-Configuration is already in basket
        $abonnementInBasket = $this->aboCommerceService->isAboCommerceConfigurationInBasket(
            $orderNumber
        );
        $eventName = str_replace('Enlight_Controller_Action_Frontend_Checkout_', '', $args->getName());

        if (null === $abonnementInBasket || false === $aboProduct['limited']) {
            $basketItem = $this->aboCommerceBasketService->addProduct(
                $orderNumber,
                $quantity,
                [
                    'isAboCommerce' => true,
                    'swagAboCommerceId' => $aboProduct['id'],
                    'swagAboCommerceDuration' => $duration,
                    'swagAboCommerceDeliveryInterval' => $deliveryInterval,
                ]
            );

            $this->aboCommerceService->insertDiscountForProducts(
                $variant,
                $aboProduct,
                $basketItem
            );
        } else {
            $snippetNamespace = $controller->get('snippets')->getNamespace('frontend/checkout/abo_commerce_cart');
            $hasConflict = false;

            if ($this->hasAboWithDifferentConfiguration($abonnementInBasket, $duration, $deliveryInterval)) {
                $snippet = $snippetNamespace->get('abo_already_configured');
                $hasConflict = true;
            } elseif ($this->aboLimitReached($aboProduct, $abonnementInBasket, $duration, $quantity)) {
                $snippet = $snippetNamespace->get('abo_limit_reached');
                $hasConflict = true;
            }

            if (!$hasConflict) {
                $basketItem = $this->aboCommerceBasketService->addProduct(
                    $orderNumber,
                    $quantity,
                    [
                        'isAboCommerce' => true,
                        'swagAboCommerceId' => $aboProduct['id'],
                        'swagAboCommerceDuration' => $duration,
                        'swagAboCommerceDeliveryInterval' => $deliveryInterval,
                    ]
                );
            } else {
                if ('AddArticle' === $eventName) {
                    $view = $controller->View();
                    $view->assign('sBasketInfo', $snippet);

                    $controller->forward('ajax_add_article');

                    return true;
                }
                if ('AjaxAddArticleCart' === $eventName) {
                    $view = $controller->View();
                    $view->assign('basketInfoMessage', $snippet);

                    $controller->forward('ajaxCart');

                    return true;
                }
            }
        }

        $view->assign([
            'sBasketInfo' => $controller->getInstockInfo($orderNumber, $quantity),
            'sArticleName' => $this->dependencyProvider->getModules()->Articles()->sGetArticleNameByOrderNumber(
                $orderNumber
            ),
        ]);

        $basket = $controller->getBasket();

        $this->eventManager->notify(
            'SwagAboCommerce_AfterAddArticle',
            [
                'ordernumber' => $orderNumber,
                'id' => $productId,
                'itemid' => $basketItem['id'],
            ]
        );

        foreach ($basket['content'] as $item) {
            if ((int) $item['id'] === (int) $basketItem['id']) {
                $view->assign('sArticle', $item);
                break;
            }
        }

        if ('AddArticle' === $eventName) {
            $controller->forward('ajax_add_article');

            return true;
        }

        if ('AjaxAddArticleCart' === $eventName) {
            $controller->forward('ajaxCart');

            return true;
        }

        return true;
    }

    /**
     * @param EventArgs $args
     *
     * @return bool
     */
    public function onAboFinish(EventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Checkout $controller */
        $controller = $args->get('subject');
        $session = $this->dependencyProvider->getSession();

        $sOrderVariables = $session['sOrderVariables']->getArrayCopy();

        $controller->View()->assign($sOrderVariables);
        $orderNumber = $controller->saveOrder();

        /* @var OrderModel $orderModel */
        $orderModel = $this->modelManager->getRepository(OrderModel::class)->findOneBy(['number' => $orderNumber]);
        $orderModel->setInternalComment($sOrderVariables['sInternalComment']);

        $this->modelManager->flush($orderModel);

        $controller->Front()->Plugins()->Json()->setRenderer(false);
        $controller->Front()->Plugins()->ViewRenderer()->setNoRender();

        echo \Zend_Json::encode(
            [
                'success' => true,
                'data' => [
                    'orderNumber' => $orderNumber,
                ],
            ]
        );

        return true;
    }

    /**
     * @param ActionEventArgs $args
     */
    public function onPreDispatchFrontendCheckout(ActionEventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Checkout $controller */
        $controller = $args->getSubject();
        $request = $controller->Request();
        $response = $controller->Response();
        $view = $controller->View();

        if (!$request->isDispatched() || $response->isException() || !$view->hasTemplate()) {
            return;
        }

        $this->aboCommerceService->updateBasketDiscount();
    }

    /**
     * @param array $abonnementInBasket
     * @param int   $duration
     * @param int   $deliveryInterval
     *
     * @return bool
     */
    private function hasAboWithDifferentConfiguration(array $abonnementInBasket, $duration, $deliveryInterval)
    {
        return $duration !== $abonnementInBasket['attribute']['swagAboCommerceDuration']
            || $deliveryInterval !== $abonnementInBasket['attribute']['swagAboCommerceDeliveryInterval'];
    }

    /**
     * @param array $aboProduct
     * @param array $abonnementInBasket
     * @param int   $duration
     * @param int   $quantity
     *
     * @return bool
     */
    private function aboLimitReached(array $aboProduct, array $abonnementInBasket, $duration, $quantity)
    {
        $weeks = 'weeks' === $aboProduct['durationUnit'] ? $duration : $duration * 4;
        $maxUnits = $aboProduct['maxUnitsPerWeek'] * $weeks;

        return $maxUnits < $abonnementInBasket['quantity'] + $quantity;
    }
}
