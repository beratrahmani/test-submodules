<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Frontend;

use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleRepository;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class ProductOrderNumberController
{
    /**
     * @var ContingentRuleRepository
     */
    private $contingentRuleRepository;

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
     * @param CurrencyService $currencyService
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        ContingentRuleRepository $contingentRuleRepository,
        CurrencyService $currencyService,
        AuthenticationService $authenticationService
    ) {
        $this->contingentRuleRepository = $contingentRuleRepository;
        $this->currencyService = $currencyService;
        $this->authenticationService = $authenticationService;
    }

    public function newAction()
    {
        //nth
    }

    /**
     * @param Request $request
     * @return array
     */
    public function editAction(Request $request): array
    {
        $id = (int) $request->requireParam('id');
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();
        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        return ['rule' => $this->contingentRuleRepository->fetchOneById($id, $currencyContext, $ownershipContext)];
    }
}
