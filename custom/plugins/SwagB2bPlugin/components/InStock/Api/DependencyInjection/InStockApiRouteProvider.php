<?php declare(strict_types=1);

namespace Shopware\B2B\InStock\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class InStockApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/in_stock/auth_id/{authId}',
                'b2b_in_stock.api_controller',
                'getList',
                ['debtorEmail', 'authId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/in_stock',
                'b2b_in_stock.api_controller',
                'create',
                ['debtorEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/in_stock/{inStockId}',
                'b2b_in_stock.api_controller',
                'get',
                ['debtorEmail', 'inStockId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/in_stock/{inStockId}',
                'b2b_in_stock.api_controller',
                'update',
                ['debtorEmail', 'inStockId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/in_stock/{inStockId}',
                'b2b_in_stock.api_controller',
                'remove',
                ['debtorEmail', 'inStockId'],
            ],
        ];
    }
}
