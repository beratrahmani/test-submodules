<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class OrderListContactApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/order_list_contact/{contactEmail}',
                'b2b_order_list.api_order_list_contact_controller',
                'getAllAllowed',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/order_list_contact/{contactEmail}/grant',
                'b2b_order_list.api_order_list_contact_controller',
                'getAllGrant',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/order_list_contact/{contactEmail}/order_list/{orderListId}',
                'b2b_order_list.api_order_list_contact_controller',
                'getAllowed',
                ['debtorEmail', 'contactEmail', 'orderListId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/order_list_contact/{contactEmail}/order_list/{orderListId}',
                'b2b_order_list.api_order_list_contact_controller',
                'allow',
                ['debtorEmail', 'contactEmail', 'orderListId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/order_list_contact/{contactEmail}/order_list/{orderListId}/grant',
                'b2b_order_list.api_order_list_contact_controller',
                'allowGrant',
                ['debtorEmail', 'contactEmail', 'orderListId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/order_list_contact/{contactEmail}/allow',
                'b2b_order_list.api_order_list_contact_controller',
                'multipleAllow',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/order_list_contact/{contactEmail}/deny',
                'b2b_order_list.api_order_list_contact_controller',
                'multipleDeny',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/order_list_contact/{contactEmail}/order_list/{orderListId}',
                'b2b_order_list.api_order_list_contact_controller',
                'deny',
                ['debtorEmail', 'contactEmail', 'orderListId'],
            ],
        ];
    }
}
