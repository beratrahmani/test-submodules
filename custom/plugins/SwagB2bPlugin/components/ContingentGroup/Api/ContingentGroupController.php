<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroup\Api;

use Shopware\B2B\Acl\Framework\AclGrantContext;
use Shopware\B2B\Acl\Framework\AclGrantContextProviderChain;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\ContingentGroup\Framework\ContingentGroupCrudService;
use Shopware\B2B\ContingentGroup\Framework\ContingentGroupRepository;
use Shopware\B2B\ContingentGroup\Framework\ContingentGroupSearchStruct;
use Shopware\B2B\Debtor\Framework\DebtorAuthenticationIdentityLoader;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class ContingentGroupController
{
    /**
     * @var ContingentGroupRepository
     */
    private $contingentGroupRepository;

    /**
     * @var GridHelper
     */
    private $requestHelper;

    /**
     * @var ContingentGroupCrudService
     */
    private $contingentGroupCrudService;

    /**
     * @var DebtorAuthenticationIdentityLoader
     */
    private $contextIdentityLoader;
    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @var AclGrantContextProviderChain
     */
    private $grantContextProviderChain;

    /**
     * @param ContingentGroupRepository $contingentGroupRepository
     * @param GridHelper $requestHelper
     * @param ContingentGroupCrudService $contingentGroupCrudService
     * @param DebtorAuthenticationIdentityLoader $contextIdentityLoader
     * @param LoginContextService $loginContextService
     * @param AclGrantContextProviderChain $grantContextProviderChain
     */
    public function __construct(
        ContingentGroupRepository $contingentGroupRepository,
        GridHelper $requestHelper,
        ContingentGroupCrudService $contingentGroupCrudService,
        DebtorAuthenticationIdentityLoader $contextIdentityLoader,
        LoginContextService $loginContextService,
        AclGrantContextProviderChain $grantContextProviderChain
    ) {
        $this->contingentGroupRepository = $contingentGroupRepository;
        $this->requestHelper = $requestHelper;
        $this->contingentGroupCrudService = $contingentGroupCrudService;
        $this->contextIdentityLoader = $contextIdentityLoader;
        $this->loginContextService = $loginContextService;
        $this->grantContextProviderChain = $grantContextProviderChain;
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @return array
     */
    public function getListAction(string $debtorEmail, Request $request): array
    {
        $search = new ContingentGroupSearchStruct();

        $this->requestHelper
            ->extractSearchDataInRestApi($request, $search);

        $context = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $contingentGroups = $this->contingentGroupRepository
            ->fetchList($context, $search);

        $totalCount = $this->contingentGroupRepository
            ->fetchTotalCount($context, $search);

        return ['success' => true, 'contingentGroups' => $contingentGroups, 'totalCount' => $totalCount];
    }

    /**
     * @param string $debtorEmail
     * @param int $contingentGroupId
     * @return array
     */
    public function getAction(string $debtorEmail, int $contingentGroupId): array
    {
        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $contingentGroup = $this->contingentGroupRepository
            ->fetchOneById($contingentGroupId, $ownershipContext);

        return ['success' => true, 'contingentGroup' => $contingentGroup];
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @return array
     */
    public function createAction(string $debtorEmail, Request $request): array
    {
        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $aclGrantContext = $this->extractGrantContext($request, $ownershipContext);

        $data = [
            'name' => $request->getParam('name'),
            'description' => $request->getParam('description'),
        ];

        $newRecord = $this->contingentGroupCrudService
            ->createNewRecordRequest($data);

        $contingentGroup = $this->contingentGroupCrudService
            ->create($newRecord, $ownershipContext, $aclGrantContext);

        return ['success' => true, 'contingentGroup' => $contingentGroup];
    }

    /**
     * @param string $debtorEmail
     * @param int $contingentGroupId
     * @param Request $request
     * @return array
     */
    public function updateAction(string $debtorEmail, int $contingentGroupId, Request $request): array
    {
        $context = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $data = [
            'id' => $contingentGroupId,
            'name' => $request->getParam('name'),
            'description' => $request->getParam('description'),
        ];

        $existingRecord = $this->contingentGroupCrudService
            ->createExistingRecordRequest($data);

        $contingentGroup = $this->contingentGroupCrudService
            ->update($existingRecord, $context);

        return ['success' => true, 'contingentGroup' => $contingentGroup];
    }

    /**
     * @param string $debtorEmail
     * @param int $contingentGroupId
     * @param Request $request
     * @return array
     */
    public function removeAction(string $debtorEmail, int $contingentGroupId, Request $request): array
    {
        $context = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $contingentGroup = $this->contingentGroupCrudService
            ->remove($contingentGroupId, $context);

        return ['success' => true, 'contingentGroup' => $contingentGroup];
    }

    /**
     * @internal
     * @param Request $request
     * @param OwnershipContext $ownershipContext
     * @throws \InvalidArgumentException
     * @return AclGrantContext
     */
    protected function extractGrantContext(Request $request, OwnershipContext $ownershipContext): AclGrantContext
    {
        $grantContextIdentifier = $request->requireParam('grantContextIdentifier');

        return $this->grantContextProviderChain->fetchOneByIdentifier($grantContextIdentifier, $ownershipContext);
    }
}
