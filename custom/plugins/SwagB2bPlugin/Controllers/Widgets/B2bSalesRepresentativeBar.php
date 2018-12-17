<?php declare(strict_types=1);

use Shopware\B2B\SalesRepresentative\Framework\SalesRepresentativeIdentity;
use Shopware\B2B\SalesRepresentative\Framework\SalesRepresentativeIdentityInterface;

class Shopware_Controllers_Widgets_B2bSalesRepresentativeBar extends Enlight_Controller_Action
{
    public function indexAction()
    {
        /** @var Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService $authenticationService */
        $authenticationService = $this->get('b2b_front_auth.authentication_service');

        if (!$authenticationService->isB2b()) {
            return;
        }

        $identity = $authenticationService
            ->getIdentity();

        if (!($identity instanceof SalesRepresentativeIdentityInterface)
            || ($identity instanceof SalesRepresentativeIdentity)
        ) {
            return;
        }

        $this->view->assign([
            'debtorEntity' => $identity->getEntity(),
        ]);
    }
}
