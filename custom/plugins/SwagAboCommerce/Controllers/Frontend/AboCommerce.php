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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\StoreFrontBundle\Service\CustomSortingServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Shopware\Models\Customer\Address;
use Shopware\Models\Partner\Partner;
use SwagAboCommerce\Bundle\SearchBundle\Condition\AboCommerceCondition;
use SwagAboCommerce\Models\Order;

class Shopware_Controllers_Frontend_AboCommerce extends Enlight_Controller_Action
{
    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $this->View()->assign('userInfo', $this->get('shopware_account.store_front_greeting_service')->fetch());
    }

    /**
     * Renders the AboCommerce landing page
     */
    public function indexAction()
    {
        $page = (int) $this->Request()->getQuery('sPage', 1);
        $layout = $this->Request()->getQuery('sTemplate', 'table');
        $perPage = (int) $this->container->get('config')->get('contentPerPage', 12);
        $data = $this->getAboDataResponsive($perPage, $page, $layout);

        // Pass the data to the view
        $this->View()->assign($data);
    }

    /**
     * Discount-orders action
     */
    public function ordersAction()
    {
        if (($customerId = $this->getCustomerId()) === null) {
            $this->forward('login', 'account');

            return;
        }

        $session = $this->container->get('session');

        if ($this->Request()->has('sAboTerminationSuccess')) {
            $aboTerminationSuccess = (bool) $this->Request()->getParam('sAboTerminationSuccess');
            $this->View()->assign('sAboTerminationSuccess', $aboTerminationSuccess);

            $mailSent = (bool) $this->Request()->getParam('sAboTerminationMailSent');
            $this->View()->assign('sAboTerminationMailSent', $mailSent);
        }

        // show partner statistic menu
        $partnerModel = $this->container->get('models')->getRepository(Partner::class)->findOneBy([
            'customerId' => $session->offsetGet('sUserId'),
        ]);

        if (!empty($partnerModel)) {
            $session->offsetSet('partnerId', $partnerModel->getId());
            $this->View()->assign('partnerId', $partnerModel->getId());
        }

        /** @var Doctrine\ORM\Query $query */
        $query = $this->container->get('models')->getRepository(Order::class)
            ->getOpenOrderByCustomerIdQuery($customerId);
        $orders = $query->getArrayResult();
        $orderIds = array_column($orders, 'id');

        $aboAddressService = $this->container->get('swag_abo_commerce.abo_address_service');
        $shippingAddresses = $aboAddressService->getOrdersShippingAddresses($orderIds);
        $billingAddresses = $aboAddressService->getOrdersBillingAddresses($orderIds);
        $paymentMeans = $this->get('modules')->getModule('Admin')->sGetPaymentMeans();
        $paymentMeansIndexed = array_column($paymentMeans, 'description', 'id');

        foreach ($orders as $i => $order) {
            if (isset($shippingAddresses[$order['id']])) {
                $shipping = $shippingAddresses[$order['id']];

                $orders[$i]['shipping']['id'] = $shipping['id'];
                $orders[$i]['shipping']['company'] = $shipping['company'];
                $orders[$i]['shipping']['firstName'] = $shipping['firstname'];
                $orders[$i]['shipping']['lastName'] = $shipping['lastname'];
                $orders[$i]['shipping']['zip'] = $shipping['zipcode'];
                $orders[$i]['shipping']['city'] = $shipping['city'];
                $orders[$i]['shipping']['street'] = $shipping['street'];
                $orders[$i]['shipping']['additionalAddressLine1'] = $shipping['additional_address_line1'];
                $orders[$i]['shipping']['additionalAddressLine2'] = $shipping['additional_address_line2'];
                $orders[$i]['shipping']['country'] = $shipping['countryName'];
            }

            if (isset($billingAddresses[$order['id']])) {
                $billing = $billingAddresses[$order['id']];

                $orders[$i]['billing']['id'] = $billing['id'];
                $orders[$i]['billing']['company'] = $billing['company'];
                $orders[$i]['billing']['firstName'] = $billing['firstname'];
                $orders[$i]['billing']['lastName'] = $billing['lastname'];
                $orders[$i]['billing']['zip'] = $billing['zipcode'];
                $orders[$i]['billing']['city'] = $billing['city'];
                $orders[$i]['billing']['street'] = $billing['street'];
                $orders[$i]['billing']['additionalAddressLine1'] = $billing['additional_address_line1'];
                $orders[$i]['billing']['additionalAddressLine2'] = $billing['additional_address_line2'];
                $orders[$i]['billing']['country'] = $billing['countryName'];
            }
        }

        $pluginName = $this->container->getParameter('swag_abo_commerce.plugin_name');
        $config = $this->container->get('shopware.plugin.config_reader')->getByPluginName($pluginName);

        $this->View()->assign('allowPaymentChange', $config['allowPaymentChange']);
        $this->View()->assign('orders', $orders);
        $this->View()->assign('paymentMeans', $paymentMeansIndexed);
        $this->View()->assign('sUserLoggedIn', $this->container->get('modules')->Admin()->sCheckUser());
        $sAboChangeSuccess = $this->Request()->get('sAboChangeSuccess', null);

        if ($sAboChangeSuccess === null) {
            return;
        }

        $this->View()->assign('changeSuccess', (bool) $sAboChangeSuccess);
    }

    public function terminateAction()
    {
        $session = $this->container->get('session');
        if (empty($session) || ($this->getCustomerId() === null)) {
            $this->forward('login', 'account');

            return;
        }

        $orderId = (int) $this->request->get('orderId');

        if ($orderId === 0) {
            $this->redirect([
                'action' => 'orders',
                'sAboTerminationSuccess' => false,
            ]);

            return;
        }

        $manager = $this->container->get('models');
        /** @var Order $aboOrder */
        $aboOrder = $manager->find(Order::class, $orderId);

        if (!$aboOrder) {
            $this->redirect([
                'action' => 'orders',
                'sAboTerminationSuccess' => false,
            ]);

            return;
        }

        $aboData = $manager->getRepository(Order::class)->getAboWithUserData($orderId);

        if ($aboOrder->getDirectTermination() === true) {
            $endDate = new \DateTime();
            $aboOrder->setLastRun($endDate);
        } else {
            $endDate = $aboOrder->incrementDate(new \DateTime(), $aboOrder->getPeriodOfNoticeInterval(), $aboOrder->getPeriodOfNoticeUnit());
            $aboOrder->setLastRun($endDate);
        }
        $now = new \DateTime();
        $aboOrder->setTerminationDate($now);

        $manager->flush($aboOrder);

        $context = [
            'aboData' => $aboData,
            'dueDate' => $endDate,
        ];

        $mailSent = true;
        try {
            $mail = $this->container->get('templatemail')->createMail('sABOCOMMERCETERMINATION', $context);
            $mail->addTo($aboOrder->getCustomer()->getEmail());
            $mail->send();
        } catch (\Exception $e) {
            $mailSent = false;
        }

        $this->redirect([
            'action' => 'orders',
            'sAboTerminationSuccess' => true,
            'sAboTerminationMailSent' => $mailSent,
        ]);
    }

    public function ajaxSelectionAction()
    {
        $em = $this->container->get('models');
        $addressRepository = $em->getRepository(Address::class);
        $addresses = $addressRepository->getListArray($this->get('session')->get('sUserId'));
        $activeAddressId = (int) $this->Request()->getParam('selectedAddress');
        $subscriptionId = (int) $this->Request()->getParam('subscriptionId');
        $subscriptionAddressType = $this->Request()->getParam('subscriptionAddressType');

        if (empty($activeAddressId) || empty($subscriptionId)) {
            return;
        }

        if (!empty($activeAddressId)) {
            foreach ($addresses as $key => $address) {
                if ($address['id'] == $activeAddressId) {
                    unset($addresses[$key]);
                }
            }
        }

        $this->View()->assign('addresses', $addresses);
        $this->View()->assign('subscriptionId', $subscriptionId);
        $this->View()->assign('subscriptionAddressType', $subscriptionAddressType);
    }

    public function handleAddressSelectionAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $addressId = (int) $this->Request()->getPost('id');
        $subscriptionId = (int) $this->Request()->getParam('subscriptionId');
        $addressType = $this->Request()->get('subscriptionAddressType');

        if (empty($addressId) || empty($subscriptionId)) {
            $this->redirect([
                'action' => 'orders',
                'sAboChangeSuccess' => false,
            ]);

            return;
        }

        $aboAddressService = $this->get('swag_abo_commerce.abo_address_service');

        if ($addressType === 'billing') {
            $aboAddressService->updateSubscriptionBillingAddress($subscriptionId, $addressId);
        } else {
            $aboAddressService->updateSubscriptionShippingAddress($subscriptionId, $addressId);
        }

        $this->redirect([
            'action' => 'orders',
            'sAboChangeSuccess' => true,
        ]);
    }

    public function ajaxSelectionPaymentAction()
    {
        $subscriptionId = (int) $this->Request()->getParam('subscriptionId');
        $selectedPaymentId = (int) $this->Request()->get('selectedPaymentId');

        if (empty($subscriptionId) || empty($selectedPaymentId)) {
            return;
        }

        $paymentMeans = $this->container->get('modules')->getModule('Admin')->sGetPaymentMeans();
        $defaultPaymentMeanId = (int) $this->get('config')->offsetGet('paymentdefault');

        $aboCommercePaymentMeansIds = $this->get('swag_abo_commerce.abo_payment_service')->getAboCommercePaymentMeansIds();
        $activePaymentMeans = [];

        foreach ($paymentMeans as $paymentMean) {
            if (in_array($paymentMean['id'], $aboCommercePaymentMeansIds) || (int) $paymentMean['id'] === $defaultPaymentMeanId) {
                $activePaymentMeans[] = $paymentMean;
            }
        }

        /** @var sAdmin $sAdmin */
        $sAdmin = $this->container->get('modules')->getModule('sAdmin');
        $getPaymentDetails = $sAdmin->sGetPaymentMeanById($selectedPaymentId);

        $this->View()->assign('paymentMeans', $activePaymentMeans);
        $this->View()->assign('sFormData', [
            'subscriptionId' => $subscriptionId,
            'selectedPaymentId' => $selectedPaymentId
        ]);
        $this->View()->assign('subscriptionId', $subscriptionId);
        $this->View()->assign('selectedPaymentId', $selectedPaymentId);

        $paymentClass = $sAdmin->sInitiatePaymentClass($getPaymentDetails);
        if ($paymentClass instanceof \ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod) {
            $session = $this->container->get('session');
            $data = $paymentClass->getCurrentPaymentDataAsArray($session->sUserId);
            if (!empty($data)) {
                $this->View()->sFormData += $data;
            }
        }
    }

    public function handlePaymentSelectionAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        if ($this->Request()->isXmlHttpRequest() && $this->Request()->isPost()) {
            $session = $this->container->get('session');
            $db = $this->container->get('db');
            $response = ['success' => true, 'errors' => [], 'errorFlag' => false];

            $sourceIsCheckoutConfirm = $this->Request()->getParam('sourceCheckoutConfirm');
            $paymentId = $this->Request()->getPost('payment');
            $sAdmin = $this->container->get('modules')->getModule('Admin');
            $sAdmin->sSYSTEM->_POST['sPayment'] = $paymentId;
            $checkData = $sAdmin->sValidateStep3();

            if (!empty($checkData['checkPayment']['sErrorMessages']) || empty($checkData['sProcessed'])) {
                if (empty($sourceIsCheckoutConfirm)) {
                    $response['errorFlag'] = $checkData['checkPayment']['sErrorFlag'];
                    $response['errors'] = $checkData['checkPayment']['sErrorMessages'];
                }
            }
            $previousPayment = $sAdmin->sGetUserData();
            $previousPayment = $previousPayment['additional']['user']['paymentID'];

            $previousPayment = $sAdmin->sGetPaymentMeanById($previousPayment);
            if ($previousPayment['paymentTable']) {
                $deleteSQL = 'DELETE FROM ' . $previousPayment['paymentTable'] . ' WHERE userID=?';
                $db->query($deleteSQL, [$session->sUserId]);
            }

            $sAdmin->sUpdatePayment();

            if ($checkData['sPaymentObject'] instanceof \ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod) {
                $checkData['sPaymentObject']->savePaymentData($session->sUserId, $this->Request());
            }

            $response['success'] = empty($checkData['checkPayment']['sErrorMessages']);

            $this->Response()->setHeader('Content-type', 'application/json', true);
            $this->Response()->setBody(json_encode($response));
        }
    }

    public function updateAboPaymentAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        if ($this->Request()->isPost()) {
            $subscriptionId = (int) $this->Request()->getParam('subscriptionId');
            $paymentId = (int) $this->Request()->getParam('payment');
            $lastSelectedPaymentId = (int) $this->Request()->get('selectedPaymentId');

            if ($paymentId === $lastSelectedPaymentId) {
                $this->redirect([
                    'action' => 'orders',
                    'sAboChangeSuccess' => true,
                ]);

                return;
            }

            if (empty($subscriptionId) || empty($lastSelectedPaymentId)) {
                $this->redirect([
                    'action' => 'orders',
                    'sAboChangeSuccess' => false,
                ]);

                return;
            }

            $aboPaymentService = $this->get('swag_abo_commerce.abo_payment_service');

            $aboPaymentService->updateSubscriptionPaymentMethod($subscriptionId, $paymentId);

            $this->redirect([
                'action' => 'orders',
                'sAboChangeSuccess' => true,
            ]);
        }
    }

    /**
     * @return null|int
     */
    protected function getCustomerId()
    {
        /** @var sAdmin $adminModule */
        $adminModule = $this->container->get('modules')->Admin();
        if (!$adminModule->sCheckUser()) {
            return null;
        }

        return (int) $this->container->get('session')->get('sUserId');
    }

    /**
     * Creates the pagination.
     *
     * @param int    $numPages
     * @param int    $page
     * @param string $sSort
     * @param string $layout
     *
     * @return array
     */
    protected function createPagination($numPages, $page, $sSort, $layout)
    {
        $pages = [];
        for ($i = 1; $i <= $numPages; ++$i) {
            if ($i == $page) {
                $pages['numbers'][$i]['markup'] = true;
            } else {
                $pages['numbers'][$i]['markup'] = false;
            }
            $pages['numbers'][$i]['value'] = $i;
            $pages['numbers'][$i]['link'] = $this->Front()->Router()->assemble(
                [
                    'controller' => 'abo_commerce',
                    'action' => 'index',
                    'sPage' => $i,
                    'sSort' => $sSort,
                    'sTemplate' => $layout,
                ]
            );
        }

        return $pages;
    }

    /**
     * Creates the column layout by the provided layout type.
     *
     * @param string $layout
     *
     * @return array
     */
    protected function createColumnLayout($layout)
    {
        $categoryContent = [];
        if ($layout === 'table') {
            $categoryContent['layout'] = '3col';
            $categoryContent['template'] = 'article_listing_3col.tpl';
        } else {
            $categoryContent['layout'] = '1col';
            $categoryContent['template'] = 'article_listing_1col.tpl';
        }

        return $categoryContent;
    }

    /**
     * Gets the AboCommerce settings.
     *
     * @return mixed
     */
    protected function getAboCommerceSettings()
    {
        $aboCommerceSettings = $this->container->get('models')->getRepository(Order::class)
            ->getAboCommerceSettingsQuery()
            ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        return $aboCommerceSettings;
    }

    /**
     * Converts the provided ListProduct[] to a regular product array.
     *
     * @param ListProduct[] $productResults
     *
     * @return array
     */
    protected function convertToLegacyProductStructure($productResults)
    {
        /** @var LegacyStructConverter $structConvert */
        $structConvert = $this->container->get('legacy_struct_converter');

        $products = array_map(
            function ($productStruct) use ($structConvert) {
                return $structConvert->convertListProductStruct($productStruct);
            },
            $productResults
        );

        return $products;
    }

    /**
     * Helper method to collect the data for the responsive-template
     *
     * @param int    $perPage
     * @param int    $page
     * @param string $layout
     *
     * @return array
     */
    private function getAboDataResponsive($perPage, $page, $layout)
    {
        /** @var $mapper \Shopware\Components\QueryAliasMapper */
        $mapper = $this->get('query_alias_mapper');
        $mapper->replaceShortRequestQueries($this->Request());

        $context = $this->get('shopware_storefront.context_service')->getProductContext();

        /** @var \Shopware\Bundle\SearchBundle\Criteria $criteria */
        $criteria = $this->get('shopware_search.store_front_criteria_factory')
            ->createListingCriteria($this->Request(), $context);

        $criteria->addBaseCondition(new AboCommerceCondition());
        $criteria->removeFacet('abo_commerce_product');

        /** @var ProductSearchResult $searchResult */
        $searchResult = $this->get('shopware_search.product_search')->search($criteria, $context);

        /** @var ListProduct[] $productResults */
        $productResults = $searchResult->getProducts();

        $products = $this->convertToLegacyProductStructure($productResults);

        $data = $this->getListingConfiguration($searchResult, $perPage, $page, $layout);

        $aboCommerceSettings = $this->getAboCommerceSettings();

        /** @var CustomSortingServiceInterface $service */
        $service = $this->get('shopware_storefront.custom_sorting_service');
        $sortings = $service->getAllCategorySortings($context);

        // Pass the data to the view
        return array_merge($data, [
            'aboCommerceSettings' => $aboCommerceSettings,
            'facets' => $searchResult->getFacets(),
            'hasEmotion' => false,
            'sArticles' => $products,
            'sNumberArticles' => $searchResult->getTotalCount(),
            'isUserLoggedIn' => (bool) $this->container->get('session')->get('sUserId'),
            'criteria' => $criteria,
            'shortParameters' => $mapper->getQueryAliases(),
            'sortings' => $sortings,
            'ajaxCountUrlParams' => [
                'abo_base' => 1,
                'sCategory' => $context->getShop()->getCategory()->getId(),
            ],
        ]);
    }

    /**
     * Gets the listing configuration.
     *
     * @param ProductSearchResult $searchResult
     * @param int                 $perPage
     * @param int                 $page
     * @param string              $layout
     *
     * @return array
     */
    private function getListingConfiguration(ProductSearchResult $searchResult, $perPage, $page, $layout)
    {
        $sSort = (int) $this->Request()->getQuery('sSort', 1);
        $perPage = (int) $this->Request()->getQuery('sPerPage', $perPage);
        $total = $searchResult->getTotalCount();
        $numPages = ceil($total / $perPage);

        $pages = $this->createPagination($numPages, $page, $sSort, $layout);
        $categoryContent = $this->createColumnLayout($layout);

        return [
            'sCategoryContent' => $categoryContent,
            'sNumberPages' => $numPages,
            'sPages' => $pages,
            'sPage' => $page,
            'sTemplate' => $layout,
            'sSort' => $sSort,
            'total' => $total,
            'pageSizes' => explode('|', $this->container->get('config')->get('numberArticlesToShow')),
        ];
    }
}
