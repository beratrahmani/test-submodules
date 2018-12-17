<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContact\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class ContactRoleVisibilityApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/contact_role/{contactEmail}',
                'b2b_role_contact.api_contact_role_visibility_controller',
                'getAllAllowed',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/contact_role/{contactEmail}/grant',
                'b2b_role_contact.api_contact_role_visibility_controller',
                'getAllGrant',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/contact_role/{contactEmail}/role/{roleId}',
                'b2b_role_contact.api_contact_role_visibility_controller',
                'getAllowed',
                ['debtorEmail', 'contactEmail', 'roleId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/contact_role/{contactEmail}/role/{roleId}',
                'b2b_role_contact.api_contact_role_visibility_controller',
                'allow',
                ['debtorEmail', 'contactEmail', 'roleId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/contact_role/{contactEmail}/role/{roleId}/grant',
                'b2b_role_contact.api_contact_role_visibility_controller',
                'allowGrant',
                ['debtorEmail', 'contactEmail', 'roleId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/contact_role/{contactEmail}/allow',
                'b2b_role_contact.api_contact_role_visibility_controller',
                'multipleAllow',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/contact_role/{contactEmail}/deny',
                'b2b_role_contact.api_contact_role_visibility_controller',
                'multipleDeny',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/contact_role/{contactEmail}/role/{roleId}',
                'b2b_role_contact.api_contact_role_visibility_controller',
                'deny',
                ['debtorEmail', 'contactEmail', 'roleId'],
            ],
        ];
    }
}
