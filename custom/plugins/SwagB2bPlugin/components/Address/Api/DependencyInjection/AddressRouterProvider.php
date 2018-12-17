<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class AddressRouterProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/address/type/{addressType}',
                'b2b_address.api_address_controller',
                'getList',
                ['debtorEmail', 'addressType'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/address/{addressId}',
                'b2b_address.api_address_controller',
                'get',
                ['debtorEmail', 'addressId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/address/type/{addressType}',
                'b2b_address.api_address_controller',
                'create',
                ['debtorEmail', 'addressType'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/address/{addressId}/type/{addressType}',
                'b2b_address.api_address_controller',
                'update',
                ['debtorEmail', 'addressId', 'addressType'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/address/{addressId}',
                'b2b_address.api_address_controller',
                'remove',
                ['debtorEmail', 'addressId'],
            ],
        ];
    }
}
