<?php declare(strict_types=1);

namespace Shopware\B2B\InStock\Api;

use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\InStock\Framework\InStockCrudService;
use Shopware\B2B\InStock\Framework\InStockRepository;
use Shopware\B2B\InStock\Framework\InStockSearchStruct;

class InStockController
{
    /**
     * @var InStockRepository
     */
    private $inStockRepository;

    /**
     * @var GridHelper
     */
    private $requestHelper;

    /**
     * @var InStockCrudService
     */
    private $inStockCrudService;

    /**
     * @param InStockRepository $inStockRepository
     * @param GridHelper $requestHelper
     * @param InStockCrudService $inStockCrudService
     */
    public function __construct(
        InStockRepository $inStockRepository,
        GridHelper $requestHelper,
        InStockCrudService $inStockCrudService
    ) {
        $this->inStockRepository = $inStockRepository;
        $this->requestHelper = $requestHelper;
        $this->inStockCrudService = $inStockCrudService;
    }

    /**
     * @param string $debtorEmail
     * @param int $authId
     * @param Request $request
     * @return array
     */
    public function getListAction(string $debtorEmail, int $authId, Request $request): array
    {
        $search = new InStockSearchStruct();

        $this->requestHelper
            ->extractSearchDataInRestApi($request, $search);

        $inStocks = $this->inStockRepository->fetchInStocksByAuthId($authId, $search);

        $totalCount = $this->inStockRepository->fetchTotalCount($authId, $search);

        return ['success' => true, 'inStocks' => $inStocks, 'totalCount' => $totalCount];
    }

    /**
     * @param string $debtorEmail
     * @param int $inStockId
     * @return array
     */
    public function getAction(string $debtorEmail, int $inStockId): array
    {
        $inStock = $this->inStockRepository
            ->fetchOneById($inStockId);

        return ['success' => true, 'inStock' => $inStock];
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @return array
     */
    public function createAction(string $debtorEmail, Request $request): array
    {
        $newRecord = $this->inStockCrudService
            ->createNewRecordRequest($request->getPost());

        $inStock = $this->inStockCrudService
            ->create($newRecord);

        return ['success' => true, 'inStock' => $inStock];
    }

    /**
     * @param string $debtorEmail
     * @param int $inStockId
     * @param Request $request
     * @return array
     */
    public function updateAction(string $debtorEmail, int $inStockId, Request $request): array
    {
        $data = $request->getPost();
        $data['id'] = $inStockId;

        $existingRecord = $this->inStockCrudService
            ->createExistingRecordRequest($data);

        $inStock = $this->inStockCrudService
            ->update($existingRecord);

        return ['success' => true, 'inStock' => $inStock];
    }

    /**
     * @param string $debtorEmail
     * @param int $inStockId
     * @return array
     */
    public function removeAction(string $debtorEmail, int $inStockId): array
    {
        $inStock = $this->inStockCrudService
            ->remove($inStockId);

        return ['success' => true, 'inStock' => $inStock];
    }
}
