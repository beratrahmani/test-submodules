<?php declare(strict_types=1);

namespace Shopware\B2B\AclRoute\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class PrivilegeRoleApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/privilege_role/{roleId}',
                'b2b_assignment.api_privilege_role_controller',
                'getAllAllowed',
                ['debtorEmail', 'roleId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/privilege_role/{roleId}/grant',
                'b2b_assignment.api_privilege_role_controller',
                'getAllGrant',
                ['debtorEmail', 'roleId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/privilege_role/{roleId}/privilege/{privilegeId}',
                'b2b_assignment.api_privilege_role_controller',
                'getAllowed',
                ['debtorEmail', 'roleId', 'privilegeId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/privilege_role/{roleId}/privilege/{privilegeId}',
                'b2b_assignment.api_privilege_role_controller',
                'allow',
                ['debtorEmail', 'roleId', 'privilegeId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/privilege_role/{roleId}/privilege/{privilegeId}/grant',
                'b2b_assignment.api_privilege_role_controller',
                'allowGrant',
                ['debtorEmail', 'roleId', 'privilegeId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/privilege_role/{roleId}/allow',
                'b2b_assignment.api_privilege_role_controller',
                'multipleAllow',
                ['debtorEmail', 'roleId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/privilege_role/{roleId}/deny',
                'b2b_assignment.api_privilege_role_controller',
                'multipleDeny',
                ['debtorEmail', 'roleId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/privilege_role/{roleId}/privilege/{privilegeId}',
                'b2b_assignment.api_privilege_role_controller',
                'deny',
                ['debtorEmail', 'roleId', 'privilegeId'],
            ],
        ];
    }
}
