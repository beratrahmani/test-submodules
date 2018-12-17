<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroup\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class ContingentRoleApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/contingent_role/{roleId}',
                'b2b_contingent_group.api_contingent_role_controller',
                'getAllAllowed',
                ['debtorEmail', 'roleId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/contingent_role/{roleId}/grant',
                'b2b_contingent_group.api_contingent_role_controller',
                'getAllGrant',
                ['debtorEmail', 'roleId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/contingent_role/{roleId}/contingent/{contingentId}',
                'b2b_contingent_group.api_contingent_role_controller',
                'getAllowed',
                ['debtorEmail', 'roleId', 'contingentId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/contingent_role/{roleId}/contingent/{contingentId}',
                'b2b_contingent_group.api_contingent_role_controller',
                'allow',
                ['debtorEmail', 'roleId', 'contingentId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/contingent_role/{roleId}/contingent/{contingentId}/grant',
                'b2b_contingent_group.api_contingent_role_controller',
                'allowGrant',
                ['debtorEmail', 'roleId', 'contingentId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/contingent_role/{roleId}/allow',
                'b2b_contingent_group.api_contingent_role_controller',
                'multipleAllow',
                ['debtorEmail', 'roleId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/contingent_role/{roleId}/deny',
                'b2b_contingent_group.api_contingent_role_controller',
                'multipleDeny',
                ['debtorEmail', 'roleId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/contingent_role/{roleId}/contingent/{contingentId}',
                'b2b_contingent_group.api_contingent_role_controller',
                'deny',
                ['debtorEmail', 'roleId', 'contingentId'],
            ],
        ];
    }
}
