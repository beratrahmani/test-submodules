<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class BackendRiskManagementExtender implements SubscriberInterface
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param AuthenticationService $authenticationService
     */
    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_RiskManagement' => 'extendRiskManagementModule',
            'Shopware_Modules_Admin_Execute_Risk_Rule_sRiskB2bAccount' => 'riskB2bAccount',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @return bool
     */
    public function riskB2bAccount(\Enlight_Event_EventArgs $args)
    {
        $value = (int) (bool) $args->get('value');
        $isB2b = $this->authenticationService->isB2b();

        if (($isB2b && $value === 1) || (!$isB2b && $value === 0)) {
            return true;
        }

        return false;
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function extendRiskManagementModule(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Enlight_Controller_Request_Request $request */
        $request = $args->getSubject()->Request();

        /** @var \Enlight_View_Default $view */
        $view = $args->getSubject()->View();

        // register templates
        $view->addTemplateDir(__DIR__ . '/../Resources/extendedViews');

        if ($request->getActionName() === 'load') {
            $view->extendsTemplate('backend/risk_management_rule/store/risks.js');
            $view->extendsTemplate('backend/risk_management_rule/view/risk_management/panel.js');
        }
    }
}
