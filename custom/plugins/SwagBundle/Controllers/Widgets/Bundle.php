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
use SwagBundle\Components\BundleComponentInterface;
use SwagBundle\Models\Article as BundleProduct;
use SwagBundle\Models\Bundle;
use SwagBundle\Services\BundleAvailableServiceInterface;

/**
 * Shopware Widget Controller
 *
 * @category Shopware
 *
 * @copyright Copyright (c), shopware AG (http://en.shopware.com)
 */
class Shopware_Controllers_Widgets_Bundle extends Enlight_Controller_Action
{
    /**
     * Configures the productVariants which are selected and set it to the bundleConfiguration session
     */
    public function configureProductsAction()
    {
        $bundleId = (int) $this->Request()->getParam('bundleId');

        $excludedVariantsService = $this->container->get('swag_bundle.products.excluded_variants_service');
        $excludedVariants = $excludedVariantsService->getExcludedVariantIds($bundleId);

        /** @var array[] $bundleProductConfigurations */
        $bundleProductConfigurations = $this->Request()->getParam('productConfiguration', []);

        $session = $this->get('session');
        /** @var array $bundleConfiguration */
        $bundleConfiguration = $session->get('bundleConfiguration', []);

        foreach ($bundleProductConfigurations as $bundleProductId => $configurations) {
            foreach ($configurations as $groupName => $groupValue) {
                $groupId = str_replace('group-', '', $groupName);
                if ($excludedVariantsService->isVariantInactive($groupValue, $excludedVariants)) {
                    continue;
                }

                $bundleConfiguration[$bundleId][$bundleProductId][$groupId] = $groupValue;
            }
        }

        $session->offsetSet('bundleConfiguration', $bundleConfiguration);
        $this->View()->setTemplate();
    }

    /**
     * Checks if the current product is available for the bundle
     */
    public function isBundleAvailableAction()
    {
        $this->get('front')->Plugins()->ViewRenderer()->setNoRender();
        $this->get('front')->Plugins()->Json()->setRenderer();

        $orderNumber = $this->Request()->get('number');
        $bundleId = (int) $this->Request()->get('bundleId');
        $mainProductId = (int) $this->Request()->get('mainProductId');

        /** @var BundleAvailableServiceInterface $bundleAvailableService */
        $bundleAvailableService = $this->get('swag_bundle.available_service');
        $this->View()->assign('data', [
            'isAvailable' => $bundleAvailableService->isBundleAvailable($mainProductId, $bundleId, $orderNumber),
            'isVariantProduct' => $this->get('swag_bundle.products.repository')->isNumberFromVariantProduct($orderNumber),
        ]);
    }

    /**
     * Global interface to add a single bundle to the basket.
     */
    public function addBundleToBasketAction()
    {
        $bundleId = (int) $this->Request()->getParam('bundleId');

        if ($bundleId <= 0) {
            return;
        }

        /** @var Bundle $bundle */
        $bundle = $this->get('models')->find(Bundle::class, $bundleId);

        //If the bundle doesn't exist or isn't active,
        //we can redirect to the same page again to display an error message for the customer.
        if ($bundle === null || !$bundle->getActive()) {
            $productId = (int) $this->Request()->getParam('productId');
            $this->redirect(['controller' => 'detail', 'sArticle' => $productId, 'bundleMessage' => 'notAvailable']);

            return;
        }

        $selection = [];

        if ($bundle->getType() === BundleComponentInterface::SELECTABLE_BUNDLE) {
            /** @var BundleProduct $bundleProduct */
            foreach ($bundle->getArticles() as $bundleProduct) {
                if ($this->Request()->has('bundle-product-' . $bundleProduct->getId())) {
                    $selection[] = $bundleProduct;
                }
            }

            if (count($selection) === 0) {
                $this->redirect(['controller' => 'detail', 'sArticle' => $bundle->getArticle()->getId()]);

                return;
            }
        }

        $result = $this->get('swag_bundle.bundle_component')->addBundleToBasket(
            $bundleId,
            $selection
        );

        $this->get('events')->notify('bundleAddToBasket', ['articleId' => $bundle->getArticleId()]);

        if ($result['success'] === false) {
            $this->redirect(['controller' => 'detail', 'sArticle' => $bundle->getArticle()->getId()]);

            return;
        }

        $this->redirect(['controller' => 'checkout', 'action' => 'cart']);
    }
}
