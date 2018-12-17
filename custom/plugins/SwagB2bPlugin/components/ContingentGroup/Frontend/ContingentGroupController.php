<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroup\Frontend;

use Shopware\B2B\Acl\Framework\AclGrantContextProviderChain;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\EmptyForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Company\Frontend\CompanyFilterResolver;
use Shopware\B2B\ContingentGroup\Framework\ContingentGroupCrudService;
use Shopware\B2B\ContingentGroup\Framework\ContingentGroupRepository;
use Shopware\B2B\ContingentGroup\Framework\ContingentGroupSearchStruct;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class ContingentGroupController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var ContingentGroupRepository
     */
    private $groupRepository;

    /**
     * @var ContingentGroupCrudService
     */
    private $groupCrudService;

    /**
     * @var GridHelper
     */
    private $contingentGroupGridHelper;

    /**
     * @var CompanyFilterResolver
     */
    private $companyFilterResolver;

    /**
     * @var AclGrantContextProviderChain
     */
    private $grantContextProviderChain;

    /**
     * @param AuthenticationService $authenticationService
     * @param ContingentGroupRepository $groupRepository
     * @param ContingentGroupCrudService $groupCrudService
     * @param GridHelper $gridHelper
     * @param CompanyFilterResolver $companyFilterResolver
     * @param AclGrantContextProviderChain $grantContextProviderChain
     */
    public function __construct(
        AuthenticationService $authenticationService,
        ContingentGroupRepository $groupRepository,
        ContingentGroupCrudService $groupCrudService,
        GridHelper $gridHelper,
        CompanyFilterResolver $companyFilterResolver,
        AclGrantContextProviderChain $grantContextProviderChain
    ) {
        $this->authenticationService = $authenticationService;
        $this->groupRepository = $groupRepository;
        $this->groupCrudService = $groupCrudService;
        $this->contingentGroupGridHelper = $gridHelper;
        $this->companyFilterResolver = $companyFilterResolver;
        $this->grantContextProviderChain = $grantContextProviderChain;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request): array
    {
        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        $searchStruct = $this->createSearchStruct($request, $ownershipContext);

        $contingentGroups = $this->groupRepository
            ->fetchList($ownershipContext, $searchStruct);

        $totalCount = $this->groupRepository
            ->fetchTotalCount($ownershipContext, $searchStruct);

        $maxPage = $this->contingentGroupGridHelper
            ->getMaxPage($totalCount);

        $currentPage = (int) $request->getParam('page', 1);

        $gridState = $this->contingentGroupGridHelper
            ->getGridState($request, $searchStruct, $contingentGroups, $maxPage, $currentPage);

        return array_merge(
            [
                'gridState' => $gridState,
                'grantContext' => $searchStruct->aclGrantContext->getIdentifier(),
            ],
            $this->companyFilterResolver->getViewFilterVariables($searchStruct)
        );
    }

    /**
     * @param Request $request
     * @return array
     */
    public function newAction(Request $request): array
    {
        return array_merge(
            $this->contingentGroupGridHelper->getValidationResponse('contingentGroup'),
            [
                'grantContext' => $request->requireParam('grantContext'),
            ]
        );
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function createAction(Request $request)
    {
        $request->checkPost();

        $post = $request->getPost();

        $serviceRequest = $this->groupCrudService
            ->createNewRecordRequest($post);

        $identity = $this->authenticationService
            ->getIdentity();

        $grantContext = $this->grantContextProviderChain
            ->fetchOneByIdentifier($request->requireParam('grantContext'), $identity->getOwnershipContext());

        try {
            $contingentGroup = $this->groupCrudService
                ->create($serviceRequest, $identity->getOwnershipContext(), $grantContext);
        } catch (ValidationException $e) {
            $this->contingentGroupGridHelper->pushValidationException($e);
            throw new B2bControllerForwardException('new');
        }

        throw new B2bControllerForwardException('detail', null, null, ['id' => $contingentGroup->id]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function detailAction(Request $request): array
    {
        return [
            'id' => (int) $request->requireParam('id'),
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function editAction(Request $request): array
    {
        $id = (int) $request->requireParam('id');
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $validationResponse = $this->contingentGroupGridHelper->getValidationResponse('contingentGroup');

        return array_merge(
            ['contingentGroup' => $this->groupRepository->fetchOneById((int) $id, $ownershipContext)],
            $validationResponse
        );
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function updateAction(Request $request)
    {
        $request->checkPost();

        $post = $request->getPost();

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();
        $serviceRequest = $this->groupCrudService->createExistingRecordRequest($post);

        try {
            $this->groupCrudService
                ->update($serviceRequest, $ownershipContext);
        } catch (ValidationException $e) {
            $this->contingentGroupGridHelper
                ->pushValidationException($e);
        }

        throw new B2bControllerForwardException('edit', null, null, ['id' => $serviceRequest->requireParam('id')]);
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function removeAction(Request $request)
    {
        $request->checkPost();

        $id = (int) $request->requireParam('id');

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $this->groupCrudService->remove($id, $ownershipContext);

        throw new EmptyForwardException();
    }

    /**
     * @internal
     * @param Request $request
     * @param OwnershipContext $ownershipContext
     * @return ContingentGroupSearchStruct
     */
    protected function createSearchStruct(Request $request, OwnershipContext $ownershipContext): ContingentGroupSearchStruct
    {
        $searchStruct = new ContingentGroupSearchStruct();

        $this->companyFilterResolver
            ->extractGrantContextFromRequest($request, $searchStruct, $ownershipContext);

        $this->contingentGroupGridHelper
            ->extractSearchDataInStoreFront($request, $searchStruct);

        return $searchStruct;
    }
}
