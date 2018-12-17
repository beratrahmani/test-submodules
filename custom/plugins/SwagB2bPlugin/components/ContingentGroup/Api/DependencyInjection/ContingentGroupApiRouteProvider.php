<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroup\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class ContingentGroupApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/contingentgroup',
                'b2b_contingent_group.api_contingent_group_controller',
                'getList',
                ['debtorEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/contingentgroup/{contingentGroupId}',
                'b2b_contingent_group.api_contingent_group_controller',
                'get',
                ['debtorEmail', 'contingentGroupId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/contingentgroup',
                'b2b_contingent_group.api_contingent_group_controller',
                'create',
                ['debtorEmail'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/contingentgroup/{contingentGroupId}',
                'b2b_contingent_group.api_contingent_group_controller',
                'update',
                ['debtorEmail', 'contingentGroupId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/contingentgroup/{contingentGroupId}',
                'b2b_contingent_group.api_contingent_group_controller',
                'remove',
                ['debtorEmail', 'contingentGroupId'],
            ],
        ];
    }
}
