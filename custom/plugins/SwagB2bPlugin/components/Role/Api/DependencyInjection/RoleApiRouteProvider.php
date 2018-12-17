<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class RoleApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/role',
                'b2b_role.api_role_controller',
                'getList',
                ['debtorEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/role/{parentId}/children',
                'b2b_role.api_role_controller',
                'getChildren',
                ['debtorEmail', 'parentId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/role/{roleId}',
                'b2b_role.api_role_controller',
                'get',
                ['debtorEmail', 'roleId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/role',
                'b2b_role.api_role_controller',
                'create',
                ['debtorEmail'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/role/{roleId}',
                'b2b_role.api_role_controller',
                'update',
                ['debtorEmail', 'roleId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/role/{roleId}/move',
                'b2b_role.api_role_controller',
                'move',
                ['debtorEmail', 'roleId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/role/{roleId}',
                'b2b_role.api_role_controller',
                'remove',
                ['debtorEmail', 'roleId'],
            ],
        ];
    }
}
