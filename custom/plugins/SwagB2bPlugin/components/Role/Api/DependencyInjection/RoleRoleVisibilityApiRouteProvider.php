<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class RoleRoleVisibilityApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/role_role/{baseRoleId}',
                'b2b_role.api_role_role_visibility_controller',
                'getAllAllowed',
                ['debtorEmail', 'baseRoleId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/role_role/{baseRoleId}/grant',
                'b2b_role.api_role_role_visibility_controller',
                'getAllGrant',
                ['debtorEmail', 'baseRoleId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/role_role/{baseRoleId}/role/{roleId}',
                'b2b_role.api_role_role_visibility_controller',
                'getAllowed',
                ['debtorEmail', 'baseRoleId', 'roleId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/role_role/{baseRoleId}/role/{roleId}',
                'b2b_role.api_role_role_visibility_controller',
                'allow',
                ['debtorEmail', 'baseRoleId', 'roleId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/role_role/{baseRoleId}/role/{roleId}/grant',
                'b2b_role.api_role_role_visibility_controller',
                'allowGrant',
                ['debtorEmail', 'baseRoleId', 'roleId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/role_role/{baseRoleId}/allow',
                'b2b_role.api_role_role_visibility_controller',
                'multipleAllow',
                ['debtorEmail', 'baseRoleId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/role_role/{baseRoleId}/deny',
                'b2b_role.api_role_role_visibility_controller',
                'multipleDeny',
                ['debtorEmail', 'baseRoleId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/role_role/{baseRoleId}/role/{roleId}',
                'b2b_role.api_role_role_visibility_controller',
                'deny',
                ['debtorEmail', 'baseRoleId', 'roleId'],
            ],
        ];
    }
}
