<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Api;

use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleCrudService;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleRepository;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleSearchStruct;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Debtor\Framework\DebtorAuthenticationIdentityLoader;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class ContingentRuleController
{
    /**
     * @var GridHelper
     */
    private $requestHelper;

    /**
     * @var DebtorAuthenticationIdentityLoader
     */
    private $debtorRepository;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @var ContingentRuleRepository
     */
    private $contingentRuleRepository;

    /**
     * @var ContingentRuleCrudService
     */
    private $contingentRuleCrudService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param ContingentRuleRepository $contingentRuleRepository
     * @param GridHelper $requestHelper
     * @param ContingentRuleCrudService $contingentRuleCrudService
     * @param DebtorAuthenticationIdentityLoader $debtorRepository
     * @param LoginContextService $loginContextService
     * @param CurrencyService $currencyService
     */
    public function __construct(
        ContingentRuleRepository $contingentRuleRepository,
        GridHelper $requestHelper,
        ContingentRuleCrudService $contingentRuleCrudService,
        DebtorAuthenticationIdentityLoader $debtorRepository,
        LoginContextService $loginContextService,
        CurrencyService $currencyService
    ) {
        $this->requestHelper = $requestHelper;
        $this->debtorRepository = $debtorRepository;
        $this->loginContextService = $loginContextService;
        $this->contingentRuleRepository = $contingentRuleRepository;
        $this->contingentRuleCrudService = $contingentRuleCrudService;
        $this->currencyService = $currencyService;
    }

    /**
     * @param string $debtorEmail
     * @param int $contingentGroupId
     * @param Request $request
     * @return array
     */
    public function getListAction(
        string $debtorEmail,
        int $contingentGroupId,
        Request $request
    ): array {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);

        $search = new ContingentRuleSearchStruct();
        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $this->requestHelper
            ->extractSearchDataInRestApi($request, $search);

        $rules = $this->contingentRuleRepository
            ->fetchListByContingentGroupId($contingentGroupId, $search, $currencyContext, $ownershipContext);

        $totalCount = $this->contingentRuleRepository
            ->fetchTotalCountByContingentGroupId($contingentGroupId, $search, $ownershipContext);

        return ['success' => true, 'contingentRules' => $rules, 'totalCount' => $totalCount];
    }

    /**
     * @param string $debtorEmail
     * @param int $contingentRuleId
     * @return array
     */
    public function getAction(string $debtorEmail, int $contingentRuleId): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $rule = $this->contingentRuleRepository
            ->fetchOneById($contingentRuleId, $currencyContext, $ownershipContext);

        return ['success' => true, 'contingentRule' => $rule];
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @return array
     */
    public function createAction(string $debtorEmail, Request $request): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $data = $request->getPost();

        $newRecord = $this->contingentRuleCrudService
            ->createNewRecordRequest($data);

        $rule = $this->contingentRuleCrudService
            ->create($newRecord, $ownershipContext, $currencyContext);

        return ['success' => true, 'contingentRule' => $rule];
    }

    /**
     * @param string $debtorEmail
     * @param int $contingentRuleId
     * @param Request $request
     * @return array
     */
    public function updateAction(string $debtorEmail, int $contingentRuleId, Request $request): array
    {
        $ownerShipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);

        $data = $request->getPost();
        $data['id'] = $contingentRuleId;

        $existingRecord = $this->contingentRuleCrudService
            ->createExistingRecordRequest($data);

        $currencyContext = $this->currencyService->createCurrencyContext();

        $rule = $this->contingentRuleCrudService
            ->update($existingRecord, $ownerShipContext, $currencyContext);

        return ['success' => true, 'contingentRule' => $rule];
    }

    /**
     * @param string $debtorEmail
     * @param int $contingentRuleId
     * @return array
     */
    public function removeAction(string $debtorEmail, int $contingentRuleId): array
    {
        $ownerShipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        $currencyContext = $this->currencyService->createCurrencyContext();

        $rule = $this->contingentRuleCrudService
            ->remove($contingentRuleId, $ownerShipContext, $currencyContext);

        return ['success' => true, 'contingentRule' => $rule];
    }

    /**
     * @internal
     * @param string $debtorEmail
     * @return OwnershipContext
     */
    protected function getDebtorOwnershipContextByEmail(string $debtorEmail): OwnershipContext
    {
        return $this->debtorRepository
            ->fetchIdentityByEmail(
                $debtorEmail,
                $this->loginContextService
            )
            ->getOwnershipContext();
    }
}
