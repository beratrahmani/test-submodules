<?php declare(strict_types=1);

namespace Shopware\B2B\Price\Framework;

use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Service\CrudServiceRequest;

class PriceCrudService extends AbstractCrudService
{
    /**
     * @var PriceRepository
     */
    private $priceRepository;

    /**
     * @var PriceValidationService
     */
    private $validationService;

    /**
     * @param PriceRepository $priceRepository
     * @param PriceValidationService $validationService
     */
    public function __construct(
        PriceRepository $priceRepository,
        PriceValidationService $validationService
    ) {
        $this->priceRepository = $priceRepository;
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
                'debtorId',
                'articlesDetailsId',
                'price',
                'from',
                'to',
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
                'debtorId',
                'articlesDetailsId',
                'price',
                'from',
                'to',
            ]
        );
    }

    /**
     * @param CrudServiceRequest $request
     * @return PriceEntity
     */
    public function create(CrudServiceRequest $request): PriceEntity
    {
        $data = $request->getFilteredData();

        $priceEntity = new PriceEntity();
        $priceEntity->setData($data);

        $validation = $this->validationService
            ->createInsertValidation($priceEntity);

        $this->testValidation($priceEntity, $validation);

        $this->priceRepository->addPrice($priceEntity);

        return $priceEntity;
    }

    /**
     * @param CrudServiceRequest $request
     * @return PriceEntity
     */
    public function update(CrudServiceRequest $request): PriceEntity
    {
        $data = $request->getFilteredData();

        $price = new PriceEntity();
        $price->setData($data);

        $validation = $this->validationService
            ->createUpdateValidation($price);

        $this->testValidation($price, $validation);

        return $this->priceRepository
            ->updatePrice($price);
    }

    /**
     * @param int $id
     * @param int $debtorId
     * @return PriceEntity
     */
    public function remove(int $id, int $debtorId): PriceEntity
    {
        $price = $this->priceRepository->fetchOneById($id, $debtorId);

        $this->priceRepository
            ->removePrice($price);

        return $price;
    }
}
