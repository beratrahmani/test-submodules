<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Frontend;

use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleRepository;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleService;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class TimeRestrictionController
{
    /**
     * @var ContingentRuleRepository
     */
    private $contingentRulesRepository;

    /**
     * @var ContingentRuleService
     */
    private $contingentRuleService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param ContingentRuleRepository $contingentRuleRepository
     * @param ContingentRuleService $contingentRuleService
     * @param CurrencyService $currencyService
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        ContingentRuleRepository $contingentRuleRepository,
        ContingentRuleService $contingentRuleService,
        CurrencyService $currencyService,
        AuthenticationService $authenticationService
    ) {
        $this->contingentRulesRepository = $contingentRuleRepository;
        $this->contingentRuleService = $contingentRuleService;
        $this->currencyService = $currencyService;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @return array
     */
    public function newAction(): array
    {
        return ['timeUnits' => $this->contingentRuleService->getTimeRestrictions()];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function editAction(Request $request): array
    {
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();
        $id = (int) $request->requireParam('id');
        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        return [
            'rule' => $this->contingentRulesRepository->fetchOneById($id, $currencyContext, $ownershipContext),
            'timeUnits' => $this->contingentRuleService->getTimeRestrictions(),
        ];
    }
}
