<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Api\DependencyInjection;

use Shopware\B2B\Common\Routing\RouteProvider;

class ContingentRuleApiRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            [
                'GET',
                '/debtor/{debtorEmail}/contingent_rule/contingent_group/{contingentGroupId}',
                'b2b_contingent_rule.api_rule_controller',
                'getList',
                ['debtorEmail', 'contingentGroupId'],
            ],
            [
                'GET',
                '/debtor/{debtorEmail}/contingent_rule/{contingentRuleId}',
                'b2b_contingent_rule.api_rule_controller',
                'get',
                ['debtorEmail', 'contingentRuleId'],
            ],
            [
                'POST',
                '/debtor/{debtorEmail}/contingent_rule',
                'b2b_contingent_rule.api_rule_controller',
                'create',
                ['debtorEmail'],
            ],
            [
                'PUT',
                '/debtor/{debtorEmail}/contingent_rule/{contingentRuleId}',
                'b2b_contingent_rule.api_rule_controller',
                'update',
                ['debtorEmail', 'contingentRuleId'],
            ],
            [
                'DELETE',
                '/debtor/{debtorEmail}/contingent_rule/{contingentRuleId}',
                'b2b_contingent_rule.api_rule_controller',
                'remove',
                ['debtorEmail', 'contingentRuleId'],
            ],
        ];
    }
}
