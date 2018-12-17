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

class Shopware_Controllers_Widgets_SwagEmotionAdvanced extends Enlight_Controller_Action
{
    /**
     * Pre dispatch method which sets the scope
     */
    public function preDispatch()
    {
        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);
    }

    /**
     * assigns the product data to the side view element
     */
    public function indexAction()
    {
        $orderNumber = $this->Request()->getParam('sOrderNumber');
        $productId = Shopware()->Modules()->Articles()->sGetArticleIdByOrderNumber($orderNumber);

        return $this->forward('index', 'detail', 'frontend', [
            'sArticle' => $productId,
            'number' => $orderNumber,
            'isEmotionAdvancedQuickView' => true,
        ]);
    }
}
