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

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use SwagAboCommerce\Services\AboCommerceServiceInterface;

class CustomProducts implements SubscriberInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var AboCommerceServiceInterface
     */
    private $aboCommerceService;

    /**
     * @param ModelManager                $modelManager
     * @param AboCommerceServiceInterface $aboCommerceService
     */
    public function __construct(
        ModelManager $modelManager,
        AboCommerceServiceInterface $aboCommerceService
    ) {
        $this->modelManager = $modelManager;
        $this->aboCommerceService = $aboCommerceService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Widgets_SwagCustomProducts' => 'onSwagCustomProducts',
        ];
    }

    /**
     * @param ActionEventArgs $arguments
     */
    public function onSwagCustomProducts(ActionEventArgs $arguments)
    {
        /** @var \Shopware_Controllers_Widgets_SwagCustomProducts $controller */
        $controller = $arguments->getSubject();
        $request = $controller->Request();

        if ($request->getActionName() !== 'overviewCalculation') {
            return;
        }

        $duration = $request->getParam('swagAboCommerceDuration');
        if ($duration === null) {
            return;
        }

        $orderNumber = $request->getParam('number');
        /** @var Detail $selectedVariant */
        $selectedVariant = $this->modelManager->getRepository(Detail::class)->findOneBy(['number' => $orderNumber]);
        if (!$selectedVariant) {
            return;
        }

        $aboCommerceData = $this->aboCommerceService->getAboCommerceDataSelectedProduct($selectedVariant);
        if (empty($aboCommerceData)) {
            return;
        }

        $durationPrice = null;
        foreach ($aboCommerceData['prices'] as $aboPrice) {
            if ($aboPrice['duration'] <= $duration) {
                $durationPrice = $aboPrice;
            }
        }
        if ($durationPrice === null) {
            return;
        }

        $aboDiscountAbsolute = (int) $durationPrice['discountAbsolute'];
        if ($aboDiscountAbsolute === 0) {
            return;
        }

        $view = $controller->View();
        $customProductsData = $view->getAssign('data');
        $productQuantity = (int) $request->getParam('sQuantity', 1);

        $newCustomProductsBasePrice = $customProductsData['basePrice'] - $aboDiscountAbsolute;
        $newCustomProductsTotalUnitPrice = $customProductsData['totalUnitPrice'] - $aboDiscountAbsolute;
        $newCustomProductsTotalPrice = $customProductsData['total'] - $productQuantity * $aboDiscountAbsolute;

        $customProductsData['basePrice'] = $newCustomProductsBasePrice;
        $customProductsData['totalUnitPrice'] = $newCustomProductsTotalUnitPrice;
        $customProductsData['total'] = $newCustomProductsTotalPrice;

        $view->assign('data', $customProductsData);
    }
}
