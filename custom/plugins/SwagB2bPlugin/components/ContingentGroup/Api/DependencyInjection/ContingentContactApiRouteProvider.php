<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroup\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class ContingentContactApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/contingent_contact/{contactEmail}',
                'b2b_contingent_group.api_contingent_contact_controller',
                'getAllAllowed',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/contingent_contact/{contactEmail}/grant',
                'b2b_contingent_group.api_contingent_contact_controller',
                'getAllGrant',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/contingent_contact/{contactEmail}/contingent/{contingentId}',
                'b2b_contingent_group.api_contingent_contact_controller',
                'getAllowed',
                ['debtorEmail', 'contactEmail', 'contingentId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/contingent_contact/{contactEmail}/contingent/{contingentId}',
                'b2b_contingent_group.api_contingent_contact_controller',
                'allow',
                ['debtorEmail', 'contactEmail', 'contingentId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/contingent_contact/{contactEmail}/contingent/{contingentId}/grant',
                'b2b_contingent_group.api_contingent_contact_controller',
                'allowGrant',
                ['debtorEmail', 'contactEmail', 'contingentId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/contingent_contact/{contactEmail}/allow',
                'b2b_contingent_group.api_contingent_contact_controller',
                'multipleAllow',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/contingent_contact/{contactEmail}/deny',
                'b2b_contingent_group.api_contingent_contact_controller',
                'multipleDeny',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/contingent_contact/{contactEmail}/contingent/{contingentId}',
                'b2b_contingent_group.api_contingent_contact_controller',
                'deny',
                ['debtorEmail', 'contactEmail', 'contingentId'],
            ],
        ];
    }
}
