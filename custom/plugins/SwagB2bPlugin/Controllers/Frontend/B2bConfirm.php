<?php declare(strict_types=1);

use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class Shopware_Controllers_Frontend_B2bConfirm extends \Enlight_Controller_Action
{
    public function preDispatch()
    {
        /** @var AuthenticationService $authenticationService */
        $authenticationService = $this->get('b2b_front_auth.authentication_service');

        if (!$authenticationService->isB2b()) {
            $this->forward('index', 'account');
        }
    }

    public function removeAction()
    {
        $this->View()->assign('formData', $this->Request()->getPost());
    }

    public function errorAction()
    {
        $this->View()->assign('variables', $this->Request()->getPost());
    }

    public function overrideAction()
    {
        $this->View()->assign('variables', $this->Request()->getPost());
    }
}
