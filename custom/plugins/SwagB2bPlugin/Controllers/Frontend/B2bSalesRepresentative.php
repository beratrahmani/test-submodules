<?php declare(strict_types=1);

use Shopware\B2B\SalesRepresentative\Framework\SalesRepresentativeIdentity;
use Shopware\B2B\SalesRepresentative\Framework\SalesRepresentativeIdentityInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bSalesRepresentative extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\SalesRepresentative\Frontend\SalesRepresentativeController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_sales_representative.controller';
    }

    public function preDispatch()
    {
        /** @var AuthenticationService $authenticationService */
        $authenticationService = $this->get('b2b_front_auth.authentication_service');

        if (!$authenticationService->isB2b()) {
            $this->forward('index', 'account');

            return;
        }

        $action = $this->Request()->getActionName();

        $identity = $authenticationService->getIdentity();

        if ($action === 'salesRepresentativeLogin'
            && (!($identity instanceof SalesRepresentativeIdentityInterface)
            || ($identity instanceof SalesRepresentativeIdentity))
        ) {
            $this->redirectToB2bStart();
        } elseif ($action !== 'salesRepresentativeLogin' && !($identity instanceof SalesRepresentativeIdentity)) {
            $this->redirectToB2bStart();
        }
    }

    /**
     * @internal
     */
    protected function redirectToB2bStart()
    {
        $this->redirect(['controller' => 'b2bdashboard']);
    }
}
