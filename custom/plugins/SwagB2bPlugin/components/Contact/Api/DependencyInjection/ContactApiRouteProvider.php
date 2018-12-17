<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class ContactApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/contact',
                'b2b_debtor.api_contact_controller',
                'getList',
                ['debtorEmail'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/contact',
                'b2b_debtor.api_contact_controller',
                'create',
                ['debtorEmail'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/contact/{contactIdentifier}',
                'b2b_debtor.api_contact_controller',
                'update',
                ['debtorEmail', 'contactIdentifier'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/contact/{contactIdentifier}',
                'b2b_debtor.api_contact_controller',
                'remove',
                ['debtorEmail', 'contactIdentifier'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/contact/{contactIdentifier}',
                'b2b_debtor.api_contact_controller',
                'get',
                ['debtorEmail', 'contactIdentifier'],
            ],
        ];
    }
}
