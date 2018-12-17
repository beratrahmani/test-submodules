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
use Enlight_Plugin_PluginManager;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use SwagAboCommerce\Services\AboCommerceServiceInterface;

class Frontend implements SubscriberInterface
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
     * @var Enlight_Plugin_PluginManager
     */
    private $legacyPluginManager;

    /**
     * @param AboCommerceServiceInterface  $aboCommerceService
     * @param ModelManager                 $modelManager
     * @param Enlight_Plugin_PluginManager $pluginManager
     */
    public function __construct(
        AboCommerceServiceInterface $aboCommerceService,
        ModelManager $modelManager,
        Enlight_Plugin_PluginManager $pluginManager
    ) {
        $this->aboCommerceService = $aboCommerceService;
        $this->modelManager = $modelManager;
        $this->legacyPluginManager = $pluginManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => ['onPostDispatchFrontendDetail', -1],
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Search' => 'onGetProductsBySearch',
            'sAdmin::sManageRisks::after' => 'onAfterManageRisk',
            // Inject the header globally in the store
            'Enlight_Controller_Action_PostDispatchSecure' => 'onPostDispatchFrontend',
        ];
    }

    /**
     * Handles the Enlight_Controller_Action_PostDispatch_Frontend_Detail event.
     * Assigns AboCommerce specific values to the view and adds the plugin's views directory.
     *
     * @param ActionEventArgs $args
     */
    public function onPostDispatchFrontendDetail(ActionEventArgs $args)
    {
        /** @var $subject \Enlight_Controller_Action */
        $subject = $args->getSubject();
        $request = $subject->Request();
        $view = $subject->View();

        if ($request->getActionName() !== 'index') {
            return;
        }

        $product = $view->getAssign('sArticle');
        $orderNumber = $product['ordernumber'];

        /** @var Detail $selectedVariant */
        $selectedVariant = $this->modelManager->getRepository(Detail::class)->findOneBy(['number' => $orderNumber]);

        if (!$selectedVariant) {
            return;
        }

        $aboCommerceData = $this->aboCommerceService->getAboCommerceDataSelectedProduct($selectedVariant);

        if (!empty($aboCommerceData)) {
            $aboCommerceData['hasDiscount'] = false;

            $aboCommerceData['discount_prices'] = json_encode($aboCommerceData['prices']);

            foreach ($aboCommerceData['prices'] as $key => $price) {
                //We need this in order to find out the correct price-data for the template
                if ($price['duration'] <= $aboCommerceData['minDuration'] && !$aboCommerceData['selectedDuration']) {
                    $aboCommerceData['selectedDuration'] = $key;
                }

                if ($price['discountAbsolute'] !== 0) {
                    $aboCommerceData['hasDiscount'] = true;
                }
            }
        }
        $view->assign('aboCommerce', $aboCommerceData);
    }

    /**
     * Handles the sAdmin::sManageRisks::after hook.
     * It will check if paypal is available for this product.
     *
     * @param \Enlight_Hook_HookArgs $args
     */
    public function onAfterManageRisk(\Enlight_Hook_HookArgs $args)
    {
        $returnValue = $args->getReturn();

        // if payment method is disabled due to risk-management restriction we can stop here
        if ($returnValue) {
            $args->setReturn($returnValue);

            return;
        }

        // if there is no AboCommerce product in the basket we can stop here
        if (!$this->aboCommerceService->isAboCommerceProductInBasket()) {
            $args->setReturn($returnValue);

            return;
        }

        $parameters = $args->getArgs();
        $paymentId = $parameters[0];

        $isEnabled = $this->modelManager->getConnection()->createQueryBuilder()
            ->select('payment_id')
            ->from('s_plugin_swag_abo_commerce_settings_paymentmeans')
            ->where('payment_id = :payment_id')
            ->setParameter('payment_id', $paymentId)
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);

        if (!$isEnabled) {
            // disable payment mean
            $args->setReturn(true);

            return;
        }

        $isPaypal = $this->modelManager->getConnection()->createQueryBuilder()
            ->select('id')
            ->from('s_core_paymentmeans')
            ->where('id = :id')
            ->andWhere('name LIKE "paypal"')
            ->setParameter('id', $paymentId)
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);

        if (!$isPaypal) {
            // enable payment mean
            $args->setReturn(false);

            return;
        }

        try {
            $config = $this->legacyPluginManager->Frontend()->SwagPaymentPaypal()->Config();
        } catch (\Enlight_Exception $e) {
            $config = false;
            $isEnabled = false;
        }

        if ($config !== false && !$config->paypalBillingAgreement) {
            $isEnabled = false;
        }

        if ($isEnabled === false) {
            $returnValue = true;
        } else {
            $returnValue = false;
        }

        $args->setReturn($returnValue);
    }

    /**
     * Check search result products if they are active AboCommerce products
     *
     * @param ActionEventArgs $arguments
     */
    public function onGetProductsBySearch(ActionEventArgs $arguments)
    {
        $subject = $arguments->getSubject();
        $view = $subject->View();

        $searchResults = $view->getAssign('sSearchResults');
        $searchProducts = $searchResults['sArticles'] !== null ? $searchResults['sArticles'] : [];

        $products = $this->aboCommerceService->setAboCommerceFlagForProducts($searchProducts);

        $searchResults['sArticles'] = $products;
        $view->assign('sSearchResults', $searchResults);
    }

    /**
     * Handles the Enlight_Controller_Action_PostDispatch event.
     * Automatically hides the paypal express checkout button, because paypal express is not supported.
     *
     * @param ActionEventArgs $args
     */
    public function onPostDispatchFrontend(ActionEventArgs $args)
    {
        /** @var $subject \Enlight_Controller_Action */
        $subject = $args->getSubject();
        $request = $subject->Request();
        $moduleName = $request->getModuleName();

        if ($moduleName !== 'frontend' && $moduleName !== 'widgets') {
            return;
        }

        if (\Zend_Session::sessionExists() && $this->aboCommerceService->isAboCommerceProductInBasket()) {
            // Hide paypal express button
            try {
                /** @var \Shopware_Plugins_Frontend_SwagPaymentPaypal_Bootstrap $paypalPlugin */
                $paypalPlugin = $this->legacyPluginManager->Frontend()->SwagPaymentPaypal();
                $paypalPlugin->Config()->set('paypalExpressButton', false);
                $paypalPlugin->Config()->set('paypalExpressButtonLayer', false);
            } catch (\Exception $e) {
                // Intentionally left blank
            }
        }
    }
}
