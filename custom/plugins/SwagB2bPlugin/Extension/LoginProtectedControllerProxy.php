<?php declare(strict_types=1);

namespace SwagB2bPlugin\Extension;

use Shopware\B2B\Common\Frontend\ControllerProxy;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

abstract class LoginProtectedControllerProxy extends ControllerProxy
{
    public function preDispatch()
    {
        /** @var AuthenticationService $authenticationService */
        $authenticationService = $this->get('b2b_front_auth.authentication_service');
        if (!$authenticationService->isB2b()) {
            $this->forward('index', 'account');
        }
    }
}
