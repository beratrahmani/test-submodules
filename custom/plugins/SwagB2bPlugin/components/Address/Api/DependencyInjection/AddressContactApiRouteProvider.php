<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class AddressContactApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/address_contact/{contactEmail}',
                'b2b_address.api_address_contact_controller',
                'getAllAllowed',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/address_contact/{contactEmail}/grant',
                'b2b_address.api_address_contact_controller',
                'getAllGrant',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/address_contact/{contactEmail}/address/{addressId}',
                'b2b_address.api_address_contact_controller',
                'getAllowed',
                ['debtorEmail', 'contactEmail', 'addressId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/address_contact/{contactEmail}/address/{addressId}',
                'b2b_address.api_address_contact_controller',
                'allow',
                ['debtorEmail', 'contactEmail', 'addressId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/address_contact/{contactEmail}/address/{addressId}/grant',
                'b2b_address.api_address_contact_controller',
                'allowGrant',
                ['debtorEmail', 'contactEmail', 'addressId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/address_contact/{contactEmail}/allow',
                'b2b_address.api_address_contact_controller',
                'multipleAllow',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/address_contact/{contactEmail}/deny',
                'b2b_address.api_address_contact_controller',
                'multipleDeny',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/address_contact/{contactEmail}/address/{addressId}',
                'b2b_address.api_address_contact_controller',
                'deny',
                ['debtorEmail', 'contactEmail', 'addressId'],
            ],
        ];
    }
}
