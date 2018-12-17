<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class BudgetContactApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/budget_contact/{contactEmail}',
                'b2b_budget.api_budget_contact_controller',
                'getAllAllowed',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/budget_contact/{contactEmail}/grant',
                'b2b_budget.api_budget_contact_controller',
                'getAllGrant',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/budget_contact/{contactEmail}/budget/{budgetId}',
                'b2b_budget.api_budget_contact_controller',
                'getAllowed',
                ['debtorEmail', 'contactEmail', 'budgetId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/budget_contact/{contactEmail}/budget/{budgetId}',
                'b2b_budget.api_budget_contact_controller',
                'allow',
                ['debtorEmail', 'contactEmail', 'budgetId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/budget_contact/{contactEmail}/budget/{budgetId}/grant',
                'b2b_budget.api_budget_contact_controller',
                'allowGrant',
                ['debtorEmail', 'contactEmail', 'budgetId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/budget_contact/{contactEmail}/allow',
                'b2b_budget.api_budget_contact_controller',
                'multipleAllow',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/budget_contact/{contactEmail}/deny',
                'b2b_budget.api_budget_contact_controller',
                'multipleDeny',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/budget_contact/{contactEmail}/budget/{budgetId}',
                'b2b_budget.api_budget_contact_controller',
                'deny',
                ['debtorEmail', 'contactEmail', 'budgetId'],
            ],
        ];
    }
}
