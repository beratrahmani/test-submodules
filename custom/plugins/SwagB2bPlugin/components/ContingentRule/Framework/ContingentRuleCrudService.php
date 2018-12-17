<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework;

use Shopware\B2B\Common\Repository\NotAllowedRecordException;
use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Service\CrudServiceRequest;
use Shopware\B2B\Currency\Framework\CurrencyAware;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class ContingentRuleCrudService extends AbstractCrudService
{
    /**
     * @var ContingentRuleRepository
     */
    private $contingentRuleRepository;

    /**
     * @var ContingentRuleValidationService
     */
    private $groupValidationService;

    /**
     * @var ContingentRuleTypeFactory
     */
    private $entityFactory;

    /**
     * @param ContingentRuleRepository $contingentRuleRepository
     * @param ContingentRuleValidationService $groupValidationService
     * @param ContingentRuleTypeFactory $entityFactory
     */
    public function __construct(
        ContingentRuleRepository $contingentRuleRepository,
        ContingentRuleValidationService $groupValidationService,
        ContingentRuleTypeFactory $entityFactory
    ) {
        $this->contingentRuleRepository = $contingentRuleRepository;
        $this->groupValidationService = $groupValidationService;
        $this->entityFactory = $entityFactory;
    }

    /**
     * @param array $data
     * @return CrudServiceRequest
     */
    public function createNewRecordRequest(array $data): CrudServiceRequest
    {
        $baseKeys = [
            'type',
            'contingentGroupId',
        ];

        $request = new CrudServiceRequest($data, $baseKeys);
        $typeKeys = $this->entityFactory
            ->getRequestKeys($request->requireParam('type'));

        return new CrudServiceRequest(
            $data,
            array_merge($baseKeys, $typeKeys)
        );
    }

    /**
     * @param array $data
     * @return CrudServiceRequest
     */
    public function createExistingRecordRequest(array $data): CrudServiceRequest
    {
        $baseKeys = [
            'id',
            'type',
        ];

        $request = new CrudServiceRequest($data, $baseKeys);
        $typeKeys = $this->entityFactory
            ->getRequestKeys($request->requireParam('type'));

        return new CrudServiceRequest(
            $data,
            array_merge($baseKeys, $typeKeys)
        );
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @param CurrencyContext $currencyContext
     * @return ContingentRuleEntity
     */
    public function create(CrudServiceRequest $request, OwnershipContext $ownershipContext, CurrencyContext $currencyContext): ContingentRuleEntity
    {
        $data = $request->getFilteredData();

        $data['contingentGroupId'] = (int) $data['contingentGroupId'];

        $this->checkPermission($data['contingentGroupId'], $ownershipContext);

        $contingentRule = $this->entityFactory
            ->createEntityFromServiceRequest($request);

        if ($contingentRule instanceof CurrencyAware) {
            $contingentRule->setCurrencyFactor($currencyContext->currentCurrencyFactor);
        }

        $contingentRule->setData($data);

        $validation = $this->groupValidationService
            ->createInsertValidation($contingentRule);

        $this->testValidation($contingentRule, $validation);

        $contingentRule = $this->contingentRuleRepository
            ->addContingentRule($contingentRule);

        return $contingentRule;
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @param CurrencyContext $currencyContext
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \DomainException
     * @throws \Shopware\B2B\Common\Repository\NotAllowedRecordException
     * @throws \Shopware\B2B\Common\Validator\ValidationException
     * @throws \Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException
     * @return ContingentRuleEntity
     */
    public function update(CrudServiceRequest $request, OwnershipContext $ownershipContext, CurrencyContext $currencyContext): ContingentRuleEntity
    {
        $contingentRule = $this->contingentRuleRepository->fetchOneById((int) $request->requireParam('id'), $currencyContext, $ownershipContext);

        $data = $request->getFilteredData();
        $data['contingentGroupId'] = $contingentRule->contingentGroupId;

        $contingentRule = $this->entityFactory
            ->createEntityFromServiceRequest($request);

        $contingentRule->setData($data);
        $contingentRule->id = (int) $contingentRule->id;

        if ($contingentRule instanceof CurrencyAware) {
            $this->checkUpdateCurrencyFactor($currencyContext, $contingentRule, $ownershipContext);
        }

        $validation = $this->groupValidationService->createUpdateValidation($contingentRule);

        $this->testValidation($contingentRule, $validation);

        $this->contingentRuleRepository->updateContingentRule($contingentRule);

        return $contingentRule;
    }

    /**
     * @param int $contingentRuleId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @throws \InvalidArgumentException
     * @throws \DomainException
     * @throws \Shopware\B2B\Common\Repository\NotAllowedRecordException
     * @throws \Shopware\B2B\Common\Repository\CanNotRemoveUsedRecordException
     * @throws \Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException
     * @return ContingentRuleEntity
     */
    public function remove(int $contingentRuleId, OwnershipContext $ownershipContext, CurrencyContext $currencyContext): ContingentRuleEntity
    {
        $contingentRule = $this->contingentRuleRepository->fetchOneById($contingentRuleId, $currencyContext, $ownershipContext);

        return $this->contingentRuleRepository->removeContingentRule($contingentRule);
    }

    /**
     * checks if the user is connected to the contingent group
     *
     * @internal
     * @param int $contingentGroupId
     * @param OwnershipContext $ownershipContext
     * @throws \Shopware\B2B\Common\Repository\NotAllowedRecordException
     */
    protected function checkPermission(int $contingentGroupId, OwnershipContext $ownershipContext)
    {
        $contingentGroupAccessAllowed = $this->contingentRuleRepository->isContingentGroupAllowedForOwner(
            $contingentGroupId,
            $ownershipContext
        );

        if (!$contingentGroupAccessAllowed) {
            throw new NotAllowedRecordException('You have no permission for the given contingent rule');
        }
    }

    /**
     * @internal
     * @param CurrencyContext $currencyContext
     * @param $contingentRule
     * @param OwnershipContext $ownershipContext
     */
    protected function checkUpdateCurrencyFactor(CurrencyContext $currencyContext, $contingentRule, OwnershipContext $ownershipContext)
    {
        $currentRule = $this->contingentRuleRepository->fetchOneById($contingentRule->id, $currencyContext, $ownershipContext);

        if (!$currentRule instanceof $contingentRule) {
            $contingentRule->setCurrencyFactor($currencyContext->currentCurrencyFactor);

            return;
        }

        foreach ($contingentRule->getAmountPropertyNames() as $amountPropertyName) {
            if (abs($contingentRule->{$amountPropertyName} - $currentRule->{$amountPropertyName}) > 0.001) {
                $contingentRule->setCurrencyFactor($currencyContext->currentCurrencyFactor);
            }
        }
    }
}
