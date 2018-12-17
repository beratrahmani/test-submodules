<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContingentGroup\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class RoleContingentGroupApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/role/{roleId}/contingentgroup',
                'b2b_role_contingent_group.api_role_contingent_group_controller',
                'getList',
                ['debtorEmail', 'roleId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/role/{roleId}/contingentgroup',
                'b2b_role_contingent_group.api_role_contingent_group_controller',
                'create',
                ['debtorEmail', 'roleId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/role/{roleId}/contingentgroup',
                'b2b_role_contingent_group.api_role_contingent_group_controller',
                'remove',
                ['debtorEmail', 'roleId'],
            ],
        ];
    }
}
