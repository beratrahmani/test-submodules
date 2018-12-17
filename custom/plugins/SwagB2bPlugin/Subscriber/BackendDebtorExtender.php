<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\Debtor\Framework\DebtorRepository;

class BackendDebtorExtender implements SubscriberInterface
{
    /**
     * @var DebtorRepository
     */
    private $debtorRepository;

    /**
     * @param DebtorRepository $debtorRepository
     */
    public function __construct(DebtorRepository $debtorRepository)
    {
        $this->debtorRepository = $debtorRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Customer' => 'extendCustomerModule',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'extendMerchantWidget',
            'Shopware_Controllers_Backend_Widgets::sendMailToMerchantAction::after' => 'afterSendMailToMerchantAction',
            'Shopware_Controllers_Backend_CustomerQuickView::getModelFields::after' => 'afterCustomerQuickViewGetModelFields',
            'Shopware_Controllers_Backend_CustomerQuickView::getFilterConditions::after' => 'afterCustomerQuickViewGetFilterConditions',
        ];
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     * @return array
     */
    public function afterCustomerQuickViewGetFilterConditions(\Enlight_Hook_HookArgs $args): array
    {
        $filters = $args->getReturn();

        foreach ($filters as &$filter) {
            if ($filter['property'] === 'attribute.b2bIsDebtor' && $filter['value'] === false) {
                $filter['expression'] = 'IS NULL OR attribute.b2bIsDebtor =';
            }
        }

        $args->setReturn($filters);

        return $args->getReturn();
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     * @return array
     */
    public function afterCustomerQuickViewGetModelFields(\Enlight_Hook_HookArgs $args): array
    {
        $fields = $args->getReturn();
        $fields['b2bIsDebtor'] = ['alias' => 'attribute.b2bIsDebtor', 'type' => 'bool'];

        $args->setReturn($fields);

        return $args->getReturn();
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     * @return bool
     */
    public function afterSendMailToMerchantAction(\Enlight_Hook_HookArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_Widgets $subject */
        $controller = $args->getSubject();

        /** @var \Enlight_Controller_Request_Request $request */
        $request = $controller->Request();

        if ($args->getReturn() === false) {
            return false;
        }

        if ($request->getParam('status') === 'accepted'
            && $request->getParam('b2b_is_debtor') === '1'
            && $request->getParam('userId')
        ) {
            $this->debtorRepository->setUserAsDebtor((int) $request->getParam('userId'));
        }

        return $args->getReturn();
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function extendMerchantWidget(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Request_Request $request */
        $request = $args->getSubject()->Request();

        /** @var \Enlight_View_Default $view */
        $view = $args->getSubject()->View();

        // register templates
        $view->addTemplateDir(__DIR__ . '/../Resources/extendedViews');

        if ($request->getActionName() === 'load') {
            $view->extendsTemplate('backend/index/debtor/view/merchant/window.js');
        }
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function extendCustomerModule(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Enlight_Controller_Request_Request $request */
        $request = $args->getSubject()->Request();

        /** @var \Enlight_View_Default $view */
        $view = $args->getSubject()->View();

        // register templates
        $view->addTemplateDir(__DIR__ . '/../Resources/extendedViews');

        if ($request->getActionName() === 'index') {
            $view->extendsTemplate('backend/customer/debtor/app.js');
        }

        if ($request->getActionName() === 'load') {
            $view->extendsTemplate('backend/customer/debtor/view/detail/window.js');
            $view->extendsTemplate('backend/customer/debtor/view/detail/base.js');
            $view->extendsTemplate('backend/customer/debtor/controller/detail.js');
            $view->extendsTemplate('backend/customer/debtor/view/main/customer_list_filter.js');
            $view->extendsTemplate('backend/customer/debtor/model/quick_view.js');
        }
    }
}
