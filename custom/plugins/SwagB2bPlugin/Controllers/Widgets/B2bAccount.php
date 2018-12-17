<?php declare(strict_types=1);

class Shopware_Controllers_Widgets_B2bAccount extends Enlight_Controller_Action
{
    public function indexAction()
    {
        /** @var Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService $authenticationService */
        $authenticationService = $this->get('b2b_front_auth.authentication_service');

        if (!$authenticationService->isB2b()) {
            return;
        }

        $this->view->assign(
            [
                'identity' => $authenticationService->getIdentity()->getEntity(),
            ]
        );
    }
}
