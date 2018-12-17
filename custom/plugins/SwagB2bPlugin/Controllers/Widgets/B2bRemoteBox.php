<?php declare(strict_types=1);

use Shopware\B2B\Common\Controller\B2bControllerRedirectException;

class Shopware_Controllers_Widgets_B2bRemoteBox extends Enlight_Controller_Action
{
    public function preDispatch()
    {
        /** @var Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService $authenticationService */
        $authenticationService = $this->get('b2b_front_auth.authentication_service');

        if ($authenticationService->isB2b()) {
            $this->View()->assign('b2bSuite', true);
        }
    }

    /**
     * @throws B2bControllerRedirectException
     */
    public function detailAction()
    {
        $orderNumber = $this->Request()->getParam('orderNumber');
        $quantity = $this->Request()->getParam('quantity');
        $this->View()->assign('orderNumber', $orderNumber);
        $this->View()->assign('quantity', $quantity);
    }

    /**
     * @throws B2bControllerRedirectException
     */
    public function listingAction()
    {
        // nth
    }
}
