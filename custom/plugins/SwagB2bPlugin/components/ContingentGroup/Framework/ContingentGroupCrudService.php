<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroup\Framework;

use Shopware\B2B\Acl\Framework\AclAccessWriterInterface;
use Shopware\B2B\Acl\Framework\AclGrantContext;
use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Service\CrudServiceRequest;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class ContingentGroupCrudService extends AbstractCrudService
{
    /**
     * @var ContingentGroupRepository
     */
    private $contingentGroupRepository;

    /**
     * @var ContingentGroupValidationService
     */
    private $groupValidationService;

    /**
     * @var AclAccessWriterInterface
     */
    private $aclAccessWriter;

    /**
     * @param ContingentGroupRepository $contingentGroupRepository
     * @param ContingentGroupValidationService $groupValidationService
     * @param AclAccessWriterInterface $aclAccessWriter
     */
    public function __construct(
        ContingentGroupRepository $contingentGroupRepository,
        ContingentGroupValidationService $groupValidationService,
        AclAccessWriterInterface $aclAccessWriter
    ) {
        $this->contingentGroupRepository = $contingentGroupRepository;
        $this->groupValidationService = $groupValidationService;
        $this->aclAccessWriter = $aclAccessWriter;
    }

    /**
     * @param array $data
     * @return CrudServiceRequest
     */
    public function createNewRecordRequest(array $data): CrudServiceRequest
    {
        return new CrudServiceRequest(
            $data,
            [
                'name',
                'description',
            ]
        );
    }

    /**
     * @param array $data
     * @return CrudServiceRequest
     */
    public function createExistingRecordRequest(array $data): CrudServiceRequest
    {
        return new CrudServiceRequest(
            $data,
            [
                'id',
                'name',
                'description',
            ]
        );
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @param AclGrantContext $grantContext
     * @throws \Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException
     * @return ContingentGroupEntity
     */
    public function create(
        CrudServiceRequest $request,
        OwnershipContext $ownershipContext,
        AclGrantContext $grantContext
    ): ContingentGroupEntity {
        $data = $request->getFilteredData();
        $data['contextOwnerId'] = $ownershipContext->contextOwnerId;

        $contingent = new ContingentGroupEntity();

        $contingent->setData($data);

        $validation = $this->groupValidationService
            ->createInsertValidation($contingent);

        $this->testValidation($contingent, $validation);

        $contingentGroup = $this->contingentGroupRepository
            ->addContingentGroup($contingent, $ownershipContext);

        $this->aclAccessWriter->addNewSubject(
            $ownershipContext,
            $grantContext,
            $contingentGroup->id,
            true
        );

        return $contingentGroup;
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @throws \Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException
     * @return ContingentGroupEntity
     */
    public function update(CrudServiceRequest $request, OwnershipContext $ownershipContext): ContingentGroupEntity
    {
        $data = $request->getFilteredData();
        $contingentGroup = new ContingentGroupEntity();
        $contingentGroup->setData($data);
        $contingentGroup->id = (int) $contingentGroup->id;
        $contingentGroup->contextOwnerId = $ownershipContext->contextOwnerId;

        $validation = $this->groupValidationService
            ->createUpdateValidation($contingentGroup);

        $this->testValidation($contingentGroup, $validation);

        $this->aclAccessWriter->testUpdateAllowed($ownershipContext, $contingentGroup->id);

        $this->contingentGroupRepository
            ->updateContingentGroup($contingentGroup, $ownershipContext);

        return $contingentGroup;
    }

    /**
     * @param int $id
     * @param OwnershipContext $ownershipContext
     * @return ContingentGroupEntity
     */
    public function remove(int $id, OwnershipContext $ownershipContext): ContingentGroupEntity
    {
        $this->aclAccessWriter->testUpdateAllowed($ownershipContext, $id);

        $contingentGroup = $this->contingentGroupRepository->fetchOneById($id, $ownershipContext);

        $this->contingentGroupRepository
            ->removeContingentGroup($contingentGroup, $ownershipContext);

        return $contingentGroup;
    }
}
