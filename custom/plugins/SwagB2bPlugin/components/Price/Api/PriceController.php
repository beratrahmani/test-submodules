<?php declare(strict_types=1);

namespace Shopware\B2B\Price\Api;

use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Debtor\Framework\DebtorAuthenticationIdentityLoader;
use Shopware\B2B\Price\Framework\PriceCrudService;
use Shopware\B2B\Price\Framework\PriceRepository;
use Shopware\B2B\Price\Framework\PriceSearchStruct;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;

class PriceController
{
    /**
     * @var PriceRepository
     */
    private $priceRepository;

    /**
     * @var GridHelper
     */
    private $requestHelper;

    /**
     * @var PriceCrudService
     */
    private $priceCrudService;

    /**
     * @var DebtorAuthenticationIdentityLoader
     */
    private $debtorRepository;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @param PriceRepository $priceRepository
     * @param GridHelper $requestHelper
     * @param PriceCrudService $priceCrudService
     * @param DebtorAuthenticationIdentityLoader $debtorRepository
     * @param LoginContextService $loginContextService
     */
    public function __construct(
        PriceRepository $priceRepository,
        GridHelper $requestHelper,
        PriceCrudService $priceCrudService,
        DebtorAuthenticationIdentityLoader $debtorRepository,
        LoginContextService $loginContextService
    ) {
        $this->priceRepository = $priceRepository;
        $this->requestHelper = $requestHelper;
        $this->priceCrudService = $priceCrudService;
        $this->debtorRepository = $debtorRepository;
        $this->loginContextService = $loginContextService;
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @return array
     */
    public function getListAction(string $debtorEmail, Request $request): array
    {
        $search = new PriceSearchStruct();

        $this->requestHelper
            ->extractSearchDataInRestApi($request, $search);

        $debtorId = $this->debtorRepository->fetchIdentityByEmail($debtorEmail, $this->loginContextService)->getOwnershipContext()->shopOwnerUserId;

        $prices = $this->priceRepository->fetchPricesByDebtorId($debtorId, $search);

        $totalCount = $this->priceRepository
            ->fetchTotalCount($debtorId, $search);

        return ['success' => true, 'prices' => $prices, 'totalCount' => $totalCount];
    }

    /**
     * @param string $debtorEmail
     * @param int $priceId
     * @return array
     */
    public function getAction(string $debtorEmail, int $priceId): array
    {
        $debtorId = $this->debtorRepository->fetchIdentityByEmail($debtorEmail, $this->loginContextService)->getOwnershipContext()->shopOwnerUserId;

        $price = $this->priceRepository
            ->fetchOneById($priceId, $debtorId);

        return ['success' => true, 'price' => $price];
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @return array
     */
    public function createAction(string $debtorEmail, Request $request): array
    {
        $id = $this->debtorRepository->fetchIdentityByEmail($debtorEmail, $this->loginContextService)->getOwnershipContext()->shopOwnerUserId;

        $data = $request->getPost();
        $data['debtorId'] = $id;

        $newRecord = $this->priceCrudService
            ->createNewRecordRequest($data);

        $price = $this->priceCrudService
            ->create($newRecord);

        return ['success' => true, 'price' => $price];
    }

    /**
     * @param string $debtorEmail
     * @param int $priceId
     * @param Request $request
     * @return array
     */
    public function updateAction(string $debtorEmail, int $priceId, Request $request): array
    {
        $id = $this->debtorRepository->fetchIdentityByEmail($debtorEmail, $this->loginContextService)->getOwnershipContext()->shopOwnerUserId;

        $data = $request->getPost();
        $data['debtorId'] = $id;
        $data['id'] = $priceId;

        $existingRecord = $this->priceCrudService
            ->createExistingRecordRequest($data);

        $price = $this->priceCrudService
            ->update($existingRecord);

        return ['success' => true, 'price' => $price];
    }

    /**
     * @param string $debtorEmail
     * @param int $priceId
     * @return array
     */
    public function removeAction(string $debtorEmail, int $priceId): array
    {
        $debtorId = $this->debtorRepository->fetchIdentityByEmail($debtorEmail, $this->loginContextService)->getOwnershipContext()->shopOwnerUserId;

        $price = $this->priceCrudService
            ->remove($priceId, $debtorId);

        return ['success' => true, 'price' => $price];
    }
}
