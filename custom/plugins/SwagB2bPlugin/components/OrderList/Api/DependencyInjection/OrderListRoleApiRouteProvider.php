<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class OrderListRoleApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/order_list_role/{roleId}',
                'b2b_order_list.api_order_list_role_controller',
                'getAllAllowed',
                ['debtorEmail', 'roleId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/order_list_role/{roleId}/grant',
                'b2b_order_list.api_order_list_role_controller',
                'getAllGrant',
                ['debtorEmail', 'roleId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/order_list_role/{roleId}/order_list/{orderListId}',
                'b2b_order_list.api_order_list_role_controller',
                'getAllowed',
                ['debtorEmail', 'roleId', 'orderListId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/order_list_role/{roleId}/order_list/{orderListId}',
                'b2b_order_list.api_order_list_role_controller',
                'allow',
                ['debtorEmail', 'roleId', 'orderListId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/order_list_role/{roleId}/order_list/{orderListId}/grant',
                'b2b_order_list.api_order_list_role_controller',
                'allowGrant',
                ['debtorEmail', 'roleId', 'orderListId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/order_list_role/{roleId}/allow',
                'b2b_order_list.api_order_list_role_controller',
                'multipleAllow',
                ['debtorEmail', 'roleId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/order_list_role/{roleId}/deny',
                'b2b_order_list.api_order_list_role_controller',
                'multipleDeny',
                ['debtorEmail', 'roleId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/order_list_role/{roleId}/order_list/{orderListId}',
                'b2b_order_list.api_order_list_role_controller',
                'deny',
                ['debtorEmail', 'roleId', 'orderListId'],
            ],
        ];
    }
}
