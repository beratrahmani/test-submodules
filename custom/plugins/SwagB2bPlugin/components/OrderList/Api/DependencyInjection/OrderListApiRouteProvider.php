<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class OrderListApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/order_list',
                'b2b_order_list.api_order_list_controller',
                'getList',
                ['debtorEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/order_list/{orderListId}',
                'b2b_order_list.api_order_list_controller',
                'get',
                ['debtorEmail', 'orderListId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/order_list',
                'b2b_order_list.api_order_list_controller',
                'create',
                ['debtorEmail'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/order_list/{orderListId}',
                'b2b_order_list.api_order_list_controller',
                'update',
                ['debtorEmail', 'orderListId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/order_list/{orderListId}',
                'b2b_order_list.api_order_list_controller',
                'remove',
                ['debtorEmail', 'orderListId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/order_list/{orderListId}/items',
                'b2b_order_list.api_order_list_controller',
                'addItems',
                ['debtorEmail', 'orderListId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/order_list/{orderListId}/items',
                'b2b_order_list.api_order_list_controller',
                'removeItems',
                ['debtorEmail', 'orderListId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/order_list/{orderListId}/items',
                'b2b_order_list.api_order_list_controller',
                'updateItems',
                ['debtorEmail', 'orderListId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/order_list/{orderListId}/items',
                'b2b_order_list.api_order_list_controller',
                'getItems',
                ['debtorEmail', 'orderListId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/order_list/{orderListId}/copy',
                'b2b_order_list.api_order_list_controller',
                'duplicate',
                ['debtorEmail', 'orderListId'],
            ],
        ];
    }
}
