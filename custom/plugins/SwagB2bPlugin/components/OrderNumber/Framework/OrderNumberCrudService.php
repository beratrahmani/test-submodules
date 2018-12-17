<?php declare(strict_types=1);

namespace Shopware\B2B\OrderNumber\Framework;

use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Service\CrudServiceRequest;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderNumberCrudService extends AbstractCrudService
{
    /**
     * @var OrderNumberValidationService
     */
    private $validationService;

    /**
     * @var OrderNumberRepositoryInterface
     */
    private $orderNumberRepository;

    /**
     * @param OrderNumberValidationService $validationService
     * @param OrderNumberRepositoryInterface $orderNumberRepository
     */
    public function __construct(
        OrderNumberValidationService $validationService,
        OrderNumberRepositoryInterface $orderNumberRepository
    ) {
        $this->validationService = $validationService;
        $this->orderNumberRepository = $orderNumberRepository;
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
                'customOrderNumber',
                'orderNumber',
                'productDetailsId',
                'name',
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
                'customOrderNumber',
                'orderNumber',
                'productDetailsId',
                'name',
            ]
        );
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @return OrderNumberEntity
     */
    public function create(CrudServiceRequest $request, OwnershipContext $ownershipContext): OrderNumberEntity
    {
        $data = $request->getFilteredData();
        $data['contextOwnerId'] = $ownershipContext->contextOwnerId;

        $orderNumber = new OrderNumberEntity();

        $orderNumber->setData($data);

        try {
            $orderNumber->productDetailsId = $this->orderNumberRepository->fetchDetailsId($orderNumber);
        } catch (NotFoundException $e) {
            // nth
        }

        $validation = $this->validationService
            ->createInsertValidation($orderNumber);

        $this->testValidation($orderNumber, $validation);

        $orderNumber = $this->orderNumberRepository->createOrderNumber($orderNumber);

        return $orderNumber;
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @return OrderNumberEntity
     */
    public function update(CrudServiceRequest $request, OwnershipContext $ownershipContext): OrderNumberEntity
    {
        $data = $request->getFilteredData();
        $data['contextOwnerId'] = $ownershipContext->contextOwnerId;

        $orderNumber = new OrderNumberEntity();
        $orderNumber->setData($data);
        $orderNumber->id = (int) $orderNumber->id;
        try {
            $orderNumber->productDetailsId = $this->orderNumberRepository->fetchDetailsId($orderNumber);
        } catch (NotFoundException $e) {
            // nth
        }

        $validation = $this->validationService
            ->createUpdateValidation($orderNumber);

        $this->testValidation($orderNumber, $validation);

        return $this->orderNumberRepository
            ->updateOrderNumber($orderNumber);
    }

    /**
     * @param int $id
     * @param OwnershipContext $ownershipContext
     */
    public function remove(int $id, OwnershipContext $ownershipContext)
    {
        $orderNumber = new OrderNumberEntity();
        $orderNumber->id = $id;
        $orderNumber->contextOwnerId = $ownershipContext->contextOwnerId;

        $this->orderNumberRepository
            ->removeOrderNumber($orderNumber);
    }

    /**
     * @param OrderNumberFileEntity[] $orderNumbers
     * @param OwnershipContext $ownershipContext
     * @throws ValidationException
     */
    public function replace(array $orderNumbers, OwnershipContext $ownershipContext)
    {
        $this->orderNumberRepository->clearOrderNumbers($ownershipContext);

        $violations = new OrderNumberFileValidationException();

        foreach ($orderNumbers as $orderNumberEntity) {
            try {
                $this->createCsvImport($orderNumberEntity, $ownershipContext, $orderNumbers);
            } catch (ValidationException $exception) {
                $violations->addViolations($exception);
            }
        }

        if ($violations->count()) {
            throw $violations;
        }
    }

    /**
     * @param OrderNumberFileEntity $orderNumberEntity
     * @param OwnershipContext $ownershipContext
     * @param OrderNumberFileEntity[] $orderNumberEntities
     */
    protected function createCsvImport(
        OrderNumberFileEntity $orderNumberEntity,
        OwnershipContext $ownershipContext,
        array $orderNumberEntities
    ) {
        $orderNumberEntity->contextOwnerId = $ownershipContext->contextOwnerId;

        try {
            $orderNumberEntity->productDetailsId = $this->orderNumberRepository->fetchDetailsId($orderNumberEntity);
        } catch (NotFoundException $e) {
            // nth
        }

        $validator = $this->validationService->createCsvImportValidation($orderNumberEntity, $orderNumberEntities);

        $this->testValidation($orderNumberEntity, $validator);
        $this->orderNumberRepository->createOrderNumber($orderNumberEntity);
    }
}
