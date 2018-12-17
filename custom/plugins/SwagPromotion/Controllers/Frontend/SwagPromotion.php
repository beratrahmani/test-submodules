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
class Shopware_Controllers_Frontend_SwagPromotion extends Enlight_Controller_Action
{
    /**
     * preDispatch to set NoRenderer
     */
    public function preDispatch()
    {
        if ($this->Request()->getActionName() === 'addFreeGoodToCart') {
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        }
    }

    public function deletePromotionVoucherAction()
    {
        $voucherId = $this->Request()->getParam('voucherId');

        if ($voucherId) {
            unset($this->get('session')->promotionVouchers[$voucherId]);
        }

        $action = $this->Request()->getParam('sTargetAction', 'cart');
        $this->redirect(['controller' => 'checkout', 'action' => $action]);
    }

    /**
     * adds product to the basket as free good
     */
    public function addFreeGoodToCartAction()
    {
        $orderNumber = $this->Request()->getParam('orderNumber');
        $noXhr = (bool) $this->Request()->getParam('noXhr');
        $promotionId = (int) $this->Request()->getParam('promotionId');

        if (!$orderNumber) {
            if ($noXhr) {
                $this->redirect(['controller' => 'checkout', 'action' => 'cart']);
            } else {
                return;
            }
        }

        $this->get('swag_promotion.service.free_goods_service')->addArticleAsFreeGood($orderNumber, $promotionId);

        if ($noXhr) {
            $this->redirect(['controller' => 'checkout', 'action' => 'cart']);
        } else {
            echo json_encode(['success' => true]);
        }
    }
}
