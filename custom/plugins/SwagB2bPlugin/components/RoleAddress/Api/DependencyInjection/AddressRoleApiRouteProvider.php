<?php declare(strict_types=1);

namespace Shopware\B2B\RoleAddress\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class AddressRoleApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/address_role/{roleId}',
                'b2b_address.api_address_role_controller',
                'getAllAllowed',
                ['debtorEmail', 'roleId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/address_role/{roleId}/grant',
                'b2b_address.api_address_role_controller',
                'getAllGrant',
                ['debtorEmail', 'roleId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/address_role/{roleId}/address/{addressId}',
                'b2b_address.api_address_role_controller',
                'getAllowed',
                ['debtorEmail', 'roleId', 'addressId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/address_role/{roleId}/address/{addressId}',
                'b2b_address.api_address_role_controller',
                'allow',
                ['debtorEmail', 'roleId', 'addressId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/address_role/{roleId}/address/{addressId}/grant',
                'b2b_address.api_address_role_controller',
                'allowGrant',
                ['debtorEmail', 'roleId', 'addressId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/address_role/{roleId}/allow',
                'b2b_address.api_address_role_controller',
                'multipleAllow',
                ['debtorEmail', 'roleId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/address_role/{roleId}/deny',
                'b2b_address.api_address_role_controller',
                'multipleDeny',
                ['debtorEmail', 'roleId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/address_role/{roleId}/address/{addressId}',
                'b2b_address.api_address_role_controller',
                'deny',
                ['debtorEmail', 'roleId', 'addressId'],
            ],
        ];
    }
}
