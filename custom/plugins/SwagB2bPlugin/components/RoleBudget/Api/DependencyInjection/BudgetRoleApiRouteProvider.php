<?php declare(strict_types=1);

namespace Shopware\B2B\RoleBudget\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class BudgetRoleApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/budget_role/{roleId}',
                'b2b_budget.api_budget_role_controller',
                'getAllAllowed',
                ['debtorEmail', 'roleId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/budget_role/{roleId}/grant',
                'b2b_budget.api_budget_role_controller',
                'getAllGrant',
                ['debtorEmail', 'roleId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/budget_role/{roleId}/budget/{budgetId}',
                'b2b_budget.api_budget_role_controller',
                'getAllowed',
                ['debtorEmail', 'roleId', 'budgetId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/budget_role/{roleId}/budget/{budgetId}',
                'b2b_budget.api_budget_role_controller',
                'allow',
                ['debtorEmail', 'roleId', 'budgetId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/budget_role/{roleId}/budget/{budgetId}/grant',
                'b2b_budget.api_budget_role_controller',
                'allowGrant',
                ['debtorEmail', 'roleId', 'budgetId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/budget_role/{roleId}/allow',
                'b2b_budget.api_budget_role_controller',
                'multipleAllow',
                ['debtorEmail', 'roleId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/budget_role/{roleId}/deny',
                'b2b_budget.api_budget_role_controller',
                'multipleDeny',
                ['debtorEmail', 'roleId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/budget_role/{roleId}/budget/{budgetId}',
                'b2b_budget.api_budget_role_controller',
                'deny',
                ['debtorEmail', 'roleId', 'budgetId'],
            ],
        ];
    }
}
