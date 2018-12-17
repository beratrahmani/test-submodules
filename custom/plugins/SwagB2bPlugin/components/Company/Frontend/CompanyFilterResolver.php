<?php declare(strict_types=1);

namespace Shopware\B2B\Company\Frontend;

use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Company\Framework\CompanyFilterStruct;
use Shopware\B2B\Role\Framework\RoleAclGrantContext;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\Shop\Framework\SessionStorageInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class CompanyFilterResolver
{
    /**
     * @var RoleRepository
     */
    private $repository;

    /**
     * @var SessionStorageInterface
     */
    private $sessionStorage;

    /**
     * @param RoleRepository $roleRepository
     * @param SessionStorageInterface $sessionStorage
     */
    public function __construct(RoleRepository $roleRepository, SessionStorageInterface $sessionStorage)
    {
        $this->repository = $roleRepository;
        $this->sessionStorage = $sessionStorage;
    }

    /**
     * @param Request $request
     * @param CompanyFilterStruct $searchStruct
     * @param OwnershipContext $ownershipContext
     */
    public function extractGrantContextFromRequest(
        Request $request,
        CompanyFilterStruct $searchStruct,
        OwnershipContext $ownershipContext
    ) {
        $id = (int) $request->requireParam('roleId');

        $role = $this->repository
            ->fetchOneById($id, $ownershipContext);

        $searchStruct->aclGrantContext = new RoleAclGrantContext($role);

        if ($role->level === 0) {
            $searchStruct->companyFilterType = $request->getParam(
                'companyFilterType',
                CompanyFilterStruct::TYPE_INHERITANCE
            );
        } else {
            $searchStruct->companyFilterType = $request->getParam(
                'companyFilterType',
                $this->sessionStorage->get('companyFilterType') ?? CompanyFilterStruct::TYPE_ASSIGNMENT
            );

            $this->sessionStorage->set('companyFilterType', $searchStruct->companyFilterType);
        }
    }

    /**
     * @param CompanyFilterStruct $companyFilterStruct
     * @return array
     */
    public function getViewFilterVariables(CompanyFilterStruct $companyFilterStruct): array
    {
        return [
            'companyFilter' => [
                'roleId' => (string) $companyFilterStruct->aclGrantContext->getEntity()->id,
                'companyFilterType' => $companyFilterStruct->companyFilterType,
                'level' => $companyFilterStruct->aclGrantContext->getEntity()->level,
            ],
        ];
    }
}
