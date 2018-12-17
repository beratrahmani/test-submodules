<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroupContact\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class ContingentGroupContactApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/contact/{email}/contingentgroup',
                'b2b_contingent_group_contact.api_contingent_group_contact_controller',
                'getList',
                ['debtorEmail', 'email'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/contact/{email}/contingentgroup',
                'b2b_contingent_group_contact.api_contingent_group_contact_controller',
                'create',
                ['debtorEmail', 'email'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/contact/{email}/contingentgroup',
                'b2b_contingent_group_contact.api_contingent_group_contact_controller',
                'remove',
                ['debtorEmail', 'email'],
            ],
        ];
    }
}
