<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Framework;

use Shopware\B2B\Acl\Framework\AclAccessWriterInterface;
use Shopware\B2B\Acl\Framework\AclGrantContext;
use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Service\CrudServiceRequest;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class AddressCrudService extends AbstractCrudService
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressValidationService
     */
    private $validationService;

    /**
     * @var AclAccessWriterInterface
     */
    private $aclAccessWriter;

    /**
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressValidationService $validationService
     * @param AclAccessWriterInterface $aclAccessWriter
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        AddressValidationService $validationService,
        AclAccessWriterInterface $aclAccessWriter
    ) {
        $this->addressRepository = $addressRepository;
        $this->validationService = $validationService;
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
                'company',
                'department',
                'salutation',
                'ustid',
                'firstname',
                'lastname',
                'street',
                'additional_address_line1',
                'additional_address_line2',
                'zipcode',
                'city',
                'country_id',
                'phone',
                'title',
                'state_id',
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
                'company',
                'department',
                'salutation',
                'ustid',
                'firstname',
                'lastname',
                'street',
                'additional_address_line1',
                'additional_address_line2',
                'zipcode',
                'city',
                'country_id',
                'phone',
            ]
        );
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @param string $type
     * @param AclGrantContext $grantContext
     * @return AddressEntity
     */
    public function create(
        CrudServiceRequest $request,
        OwnershipContext $ownershipContext,
        string $type,
        AclGrantContext $grantContext
    ): AddressEntity {
        $data = $request->getFilteredData();
        $data['user_id'] = $ownershipContext->shopOwnerUserId;

        $address = new AddressEntity();
        
        $address->setData($data);
        
        $validation = $this->validationService
            ->createInsertValidation($address);

        $this->testValidation($address, $validation);

        $address = $this->addressRepository
            ->addAddress($address, $type, $ownershipContext);

        $this->aclAccessWriter->addNewSubject(
            $ownershipContext,
            $grantContext,
            $address->id,
            true
        );

        return $address;
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @param string $type
     * @return AddressEntity
     */
    public function update(CrudServiceRequest $request, OwnershipContext $ownershipContext, string $type): AddressEntity
    {
        $data = $request->getFilteredData();
        $address = new AddressEntity();
        $address->setData($data);
        $address->id = (int) $address->id;
        $address->user_id = $ownershipContext->shopOwnerUserId;

        $this->aclAccessWriter->testUpdateAllowed($ownershipContext, $address->id);

        $validation = $this->validationService
            ->createUpdateValidation($address);

        $this->testValidation($address, $validation);

        return $this->addressRepository
            ->updateAddress($address, $ownershipContext, $type);
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @return AddressEntity
     */
    public function remove(CrudServiceRequest $request, OwnershipContext $ownershipContext): AddressEntity
    {
        $data = $request->getFilteredData();
        $address = new AddressEntity();
        $address->setData($data);
        $address->id = (int) $address->id;

        $this->aclAccessWriter->testUpdateAllowed($ownershipContext, $address->id);

        $this->addressRepository
            ->removeAddress($address, $ownershipContext);

        return $address;
    }
}
