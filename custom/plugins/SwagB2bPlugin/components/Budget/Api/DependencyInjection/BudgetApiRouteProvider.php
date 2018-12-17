<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class BudgetApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/budget',
                'b2b_budget.api_controller',
                'getList',
                ['debtorEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/budget/{budgetId}',
                'b2b_budget.api_controller',
                'get',
                ['debtorEmail', 'budgetId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/budget',
                'b2b_budget.api_controller',
                'create',
                ['debtorEmail'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/budget/{budgetId}',
                'b2b_budget.api_controller',
                'update',
                ['debtorEmail', 'budgetId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/budget/{budgetId}',
                'b2b_budget.api_controller',
                'remove',
                ['debtorEmail', 'budgetId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/budget/{budgetId}/status',
                'b2b_budget.api_controller',
                'getCurrentStatus',
                ['debtorEmail', 'budgetId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/budget/{budgetId}/status/{date}',
                'b2b_budget.api_controller',
                'getStatus',
                ['debtorEmail', 'budgetId', 'date'],
            ],
        ];
    }
}
