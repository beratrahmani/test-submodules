<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

use Enlight\Event\SubscriberInterface;

class BackendSalesRepresentativeExtender implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Customer' => 'extendCustomerModule',
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
            if ($filter['property'] === 'attribute.b2bIsSalesRepresentative' && $filter['value'] === false) {
                $filter['expression'] = 'IS NULL OR attribute.b2bIsSalesRepresentative =';
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
        $fields['b2bIsSalesRepresentative'] = ['alias' => 'attribute.b2bIsSalesRepresentative', 'type' => 'bool'];

        $args->setReturn($fields);

        return $args->getReturn();
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
            $view->extendsTemplate('backend/customer/sales_representative/app.js');
        }

        if ($request->getActionName() === 'load') {
            $view->extendsTemplate('backend/customer/sales_representative/view/detail/window.js');
            $view->extendsTemplate('backend/customer/sales_representative/view/detail/base.js');
            $view->extendsTemplate('backend/customer/sales_representative/controller/detail.js');
            $view->extendsTemplate('backend/customer/sales_representative/view/main/customer_list_filter.js');
            $view->extendsTemplate('backend/customer/sales_representative/model/quick_view.js');
        }
    }
}
