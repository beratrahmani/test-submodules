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

use Shopware\Components\CSRFWhitelistAware;
use Shopware\Models\Shop\Shop;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_SwagDigitalPublishing extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**v
     * @var string
     */
    private $pluginDirectory;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->pluginDirectory = $this->container->getParameter('swag_digital_publishing.plugin_dir');

        $this->view->addTemplateDir($this->pluginDirectory . '/Resources/views');
    }

    /**
     * Loads the preview template for a banner.
     * Accepts complete banner data or a single banner id.
     */
    public function previewAction()
    {
        $this->setUpPreview();

        $banner = $this->Request()->getParam('banner');
        $defaultShopId = $this->container->get('models')->getRepository(Shop::class)->getActiveDefault()->getId();
        $context = $this->container->get('shopware_storefront.context_service')->createShopContext($defaultShopId);

        if ($banner === null) {
            $bannerId = $this->Request()->getParam('bannerId');

            if ($bannerId === null) {
                $this->View()->assign(['success' => false]);

                return;
            }

            $banner = $this->get('digital_publishing.content_banner_service')->get($bannerId, $context);
        } else {
            $banner = json_decode($banner, true);
            $banner = $this->get('digital_publishing.content_banner_service')->populateBanner($banner, $context);
        }

        $this->View()->addTemplateDir($this->pluginDirectory . '/Resources/views/');
        $this->View()->addTemplateDir($this->pluginDirectory . '/Resources/frontend/js');
        $this->View()->loadTemplate('widgets/swag_digital_publishing/preview.tpl');
        $this->View()->assign('banner', $banner);
    }

    /**
     * Returns a list with actions which should not be validated for CSRF protection.
     *
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'preview',
        ];
    }

    /**
     * Sets up the preview-action to run properly.
     * It needs a shop-instance and the default backend JSON-renderer has to be disabled.
     */
    private function setUpPreview()
    {
        $this->preventJsonRenderer();
        $this->emulateShop();
    }

    /**
     * Emulates a shop-instance by reading the default-active shop and registering it.
     */
    private function emulateShop()
    {
        /** @var Shopware\Models\Shop\Shop $shop */
        $shop = $this->get('models')->getRepository(Shop::class)->getActiveDefault();
        $shop->registerResources();
    }

    /**
     * Disables the JSON-renderer and re-enables the default view-renderer.
     */
    private function preventJsonRenderer()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);
        $this->Front()->Plugins()->ViewRenderer()->setNoRender(false);
    }
}
