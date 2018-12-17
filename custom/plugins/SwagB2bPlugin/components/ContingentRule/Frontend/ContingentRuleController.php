<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Frontend;

use Shopware\B2B\Common\Controller\B2bControllerForwardException;

use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\DummyEntity;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Service\CrudServiceRequest;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleCrudService;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleRepository;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleSearchStruct;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ContingentRuleController
{
    /**
     * @var ContingentRuleRepository
     */
    private $contingentRuleRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var ContingentRuleCrudService
     */
    private $contingentRuleCrudService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var array
     */
    private $supportedTypes;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param ContingentRuleRepository $contingentRuleRepository
     * @param GridHelper $gridHelper
     * @param ContingentRuleCrudService $contingentRuleCrudService
     * @param AuthenticationService $authenticationService
     * @param CurrencyService $currencyService
     * @param array $supportedTypes
     */
    public function __construct(
        ContingentRuleRepository $contingentRuleRepository,
        GridHelper $gridHelper,
        ContingentRuleCrudService $contingentRuleCrudService,
        AuthenticationService $authenticationService,
        CurrencyService $currencyService,
        array $supportedTypes
    ) {
        $this->contingentRuleRepository = $contingentRuleRepository;
        $this->gridHelper = $gridHelper;
        $this->contingentRuleCrudService = $contingentRuleCrudService;
        $this->authenticationService = $authenticationService;
        $this->currencyService = $currencyService;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function gridAction(Request $request): array
    {
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();
        $contingentGroupId = (int) $request->requireParam('id');

        $searchStruct = new ContingentRuleSearchStruct();
        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $this->gridHelper->extractSearchDataInStoreFront($request, $searchStruct);

        $contingentRules = $this->contingentRuleRepository
            ->fetchList($this->supportedTypes, $contingentGroupId, $searchStruct, $currencyContext, $ownershipContext);

        $count = $this->contingentRuleRepository
            ->fetchTotalCount($this->supportedTypes, $contingentGroupId, $searchStruct, $ownershipContext);

        $maxPage = $this->gridHelper->getMaxPage($count);
        $currentPage = $this->gridHelper->getCurrentPage($request);

        return [
            'gridState' => $this->gridHelper->getGridState($request, $searchStruct, $contingentRules, $maxPage, $currentPage),
            'additionalFormValues' => ['id' => $contingentGroupId],
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function detailAction(Request $request): array
    {
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $id = (int) $request->requireParam('id');
        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $rule = $this->contingentRuleRepository->fetchOneById($id, $currencyContext, $ownershipContext);

        $validationResponse = $this->gridHelper->getValidationResponse('rule');

        return array_merge(
            [
            'registeredRules' => $this->supportedTypes,
            'rule' => $rule,
            'contingentGroupId' => $rule->contingentGroupId,
        ],
            $validationResponse
        );
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function updateAction(Request $request)
    {
        $request->checkPost('grid', ['id' => $request->requireParam('contingentGroupId')]);

        $post = $request->getPost();

        $serviceRequest = $this->contingentRuleCrudService
            ->createExistingRecordRequest($post);

        $ownershipContext = $this->authenticationService->getIdentity()
            ->getOwnershipContext();
        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $this->validateType($serviceRequest);

        try {
            $this->contingentRuleCrudService
                ->update($serviceRequest, $ownershipContext, $currencyContext);
        } catch (ValidationException $e) {
            $this->gridHelper->pushValidationException($e);
        }

        throw new B2bControllerForwardException('grid', null, null, ['id' => $post['contingentGroupId']]);
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function removeAction(Request $request)
    {
        $request->checkPost('grid', ['id' => $request->requireParam('contingentGroupId')]);

        $ownershipContext = $this->authenticationService->getIdentity()
            ->getOwnershipContext();
        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $this->contingentRuleCrudService
            ->remove((int) $request->requireParam('id'), $ownershipContext, $currencyContext);

        throw new B2bControllerForwardException('grid', null, null, ['id' => $request->requireParam('contingentGroupId')]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function newAction(Request $request): array
    {
        $contingentGroupId = (int) $request->requireParam('contingentGroupId');

        $validationResponse = $this->gridHelper->getValidationResponse('rule');

        return array_merge(
            [
            'registeredRules' => $this->supportedTypes,
            'contingentGroupId' => $contingentGroupId,
        ],
            $validationResponse
        );
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function createAction(Request $request)
    {
        $request->checkPost('grid', ['id' => $request->requireParam('contingentGroupId')]);

        $post = $request->getPost();

        try {
            $serviceRequest = $this->contingentRuleCrudService
                ->createNewRecordRequest($post);
        } catch (\InvalidArgumentException $e) {
            $this->gridHelper->pushValidationException($this->createNoTypeValidationError());

            throw new B2bControllerForwardException('new', null, null, ['contingentGroupId' => $request->requireParam('contingentGroupId')]);
        }

        $ownershipContext = $this->authenticationService->getIdentity()
            ->getOwnershipContext();
        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $this->validateType($serviceRequest);

        try {
            $rule = $this->contingentRuleCrudService
                ->create($serviceRequest, $ownershipContext, $currencyContext);
        } catch (ValidationException $e) {
            $this->gridHelper->pushValidationException($e);

            throw new B2bControllerForwardException('new', null, null, ['contingentGroupId' => $request->requireParam('contingentGroupId')]);
        }

        throw new B2bControllerForwardException('grid', null, null, ['id' => $rule->contingentGroupId]);
    }

    /**
     * @internal
     * @param CrudServiceRequest $request
     * @throws \InvalidArgumentException
     */
    protected function validateType(CrudServiceRequest $request)
    {
        $type = $request->requireParam('type');

        if (!in_array($type, $this->supportedTypes, true)) {
            throw new \InvalidArgumentException('Can not handle entity of type "' . $type . '"');
        }
    }

    /**
     * @internal
     * @return ValidationException
     */
    protected function createNoTypeValidationError(): ValidationException
    {
        $violation = new ConstraintViolation(
            'No valid type selected.',
            'No valid type selected.',
            [],
            '',
            'Type',
            '',
            '',
            null
        );

        $violationList = new ConstraintViolationList([$violation]);

        return new ValidationException(
            new DummyEntity(),
            $violationList,
            'Validation violations detected, can not proceed:',
            400
        );
    }
}
