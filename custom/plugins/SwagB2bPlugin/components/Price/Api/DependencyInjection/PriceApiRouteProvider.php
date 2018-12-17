<?php declare(strict_types=1);

namespace Shopware\B2B\Price\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class PriceApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/price',
                'b2b_price.api_price_controller',
                'getList',
                ['debtorEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/price/{priceId}',
                'b2b_price.api_price_controller',
                'get',
                ['debtorEmail', 'priceId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/price',
                'b2b_price.api_price_controller',
                'create',
                ['debtorEmail'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/price/{priceId}',
                'b2b_price.api_price_controller',
                'update',
                ['debtorEmail', 'priceId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/price/{priceId}',
                'b2b_price.api_price_controller',
                'remove',
                ['debtorEmail', 'priceId'],
            ],
        ];
    }
}
