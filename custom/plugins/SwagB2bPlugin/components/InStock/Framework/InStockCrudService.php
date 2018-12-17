<?php declare(strict_types=1);

namespace Shopware\B2B\InStock\Framework;

use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Service\CrudServiceRequest;

class InStockCrudService extends AbstractCrudService
{
    /**
     * @var InStockRepository
     */
    private $inStockRepository;

    /**
     * @var InStockValidationService
     */
    private $validationService;

    /**
     * @param InStockRepository $inStockRepository
     * @param InStockValidationService $validationService
     */
    public function __construct(
        InStockRepository $inStockRepository,
        InStockValidationService $validationService
    ) {
        $this->inStockRepository = $inStockRepository;
        $this->validationService = $validationService;
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
                'authId',
                'articlesDetailsId',
                'inStock',
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
                'authId',
                'articlesDetailsId',
                'inStock',
            ]
        );
    }

    /**
     * @param CrudServiceRequest $request
     * @return InStockEntity
     */
    public function create(CrudServiceRequest $request): InStockEntity
    {
        $data = $request->getFilteredData();

        $entity = new InStockEntity();
        $entity->setData($data);

        $validation = $this->validationService
            ->createInsertValidation($entity);

        $this->testValidation($entity, $validation);

        $this->inStockRepository->addInStock($entity);

        return $entity;
    }

    /**
     * @param CrudServiceRequest $request
     * @return InStockEntity
     */
    public function update(CrudServiceRequest $request): InStockEntity
    {
        $data = $request->getFilteredData();

        $entity = new InStockEntity();
        $entity->setData($data);

        $validation = $this->validationService
            ->createUpdateValidation($entity);

        $this->testValidation($entity, $validation);

        return $this->inStockRepository
            ->updateInStock($entity);
    }

    /**
     * @param int $id
     * @return InStockEntity
     */
    public function remove(int $id): InStockEntity
    {
        $entity = $this->inStockRepository->fetchOneById($id);

        $this->inStockRepository->removeInStock($entity);

        return $entity;
    }
}
