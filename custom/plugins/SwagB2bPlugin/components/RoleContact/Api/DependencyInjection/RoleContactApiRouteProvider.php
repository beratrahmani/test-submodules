<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContact\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class RoleContactApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/contact/{email}/role',
                'b2b_role_contact.api_role_contact_controller',
                'getList',
                ['debtorEmail', 'email'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/contact/{email}/role',
                'b2b_role_contact.api_role_contact_controller',
                'create',
                ['debtorEmail', 'email'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/contact/{email}/role',
                'b2b_role_contact.api_role_contact_controller',
                'remove',
                ['debtorEmail', 'email'],
            ],
        ];
    }
}
