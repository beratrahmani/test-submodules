<?php declare(strict_types=1);

namespace Shopware\B2B\AclRoute\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class PrivilegeContactApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/privilege_contact/{contactEmail}',
                'b2b_assignment.api_privilege_contact_controller',
                'getAllAllowed',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/privilege_contact/{contactEmail}/grant',
                'b2b_assignment.api_privilege_contact_controller',
                'getAllGrant',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/privilege_contact/{contactEmail}/privilege/{privilegeId}',
                'b2b_assignment.api_privilege_contact_controller',
                'getAllowed',
                ['debtorEmail', 'contactEmail', 'privilegeId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/privilege_contact/{contactEmail}/privilege/{privilegeId}',
                'b2b_assignment.api_privilege_contact_controller',
                'allow',
                ['debtorEmail', 'contactEmail', 'privilegeId'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/privilege_contact/{contactEmail}/privilege/{privilegeId}/grant',
                'b2b_assignment.api_privilege_contact_controller',
                'allowGrant',
                ['debtorEmail', 'contactEmail', 'privilegeId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/privilege_contact/{contactEmail}/allow',
                'b2b_assignment.api_privilege_contact_controller',
                'multipleAllow',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/privilege_contact/{contactEmail}/deny',
                'b2b_assignment.api_privilege_contact_controller',
                'multipleDeny',
                ['debtorEmail', 'contactEmail'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/privilege_contact/{contactEmail}/privilege/{privilegeId}',
                'b2b_assignment.api_privilege_contact_controller',
                'deny',
                ['debtorEmail', 'contactEmail', 'privilegeId'],
            ],
        ];
    }
}
