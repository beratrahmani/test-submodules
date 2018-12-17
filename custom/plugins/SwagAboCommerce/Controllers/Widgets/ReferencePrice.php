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
class Shopware_Controllers_Widgets_ReferencePrice extends Enlight_Controller_Action
{
    public function indexAction()
    {
        $this->get('front')->Plugins()->ViewRenderer()->setNoRender();
        $this->get('front')->Plugins()->Json()->setRenderer();
        $this->view->setTemplate();

        $referencePrice = (float) $this->Request()->get('referencePrice');
        $discountPercentage = (float) $this->Request()->get('discountPercentage');

        if ($referencePrice * $discountPercentage !== 0.0) {
            $aboReferencePrice = $this->get('currency')->toCurrency($referencePrice * $discountPercentage);
            $this->view->assign('aboReferencePrice', $aboReferencePrice);
        } else {
            $this->view->assign('aboReferencePrice', 0);
        }

        $referencePrice = $this->get('currency')->toCurrency($referencePrice);
        $this->view->assign('regularReferencePrice', $referencePrice);
    }
}
