<?php declare(strict_types=1);

namespace Shopware\B2B\SalesRepresentative\Frontend;

use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\B2bControllerRedirectException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\SalesRepresentative\Framework\SalesRepresentativeClientRepository;
use Shopware\B2B\SalesRepresentative\Framework\SalesRepresentativeDebtorIdentity;
use Shopware\B2B\SalesRepresentative\Framework\SalesRepresentativeEntity;
use Shopware\B2B\SalesRepresentative\Framework\SalesRepresentativeIdentity;
use Shopware\B2B\SalesRepresentative\Framework\SalesRepresentativeSearchStruct;
use Shopware\B2B\SalesRepresentative\Framework\SalesRepresentativeService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class SalesRepresentativeController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var SalesRepresentativeService
     */
    private $salesRepresentativeService;

    /**
     * @var SalesRepresentativeClientRepository
     */
    private $clientRepository;

    /**
     * @param AuthenticationService $authenticationService
     * @param GridHelper $gridHelper
     * @param SalesRepresentativeClientRepository $clientRepository
     * @param SalesRepresentativeService $salesRepresentativeService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        GridHelper $gridHelper,
        SalesRepresentativeClientRepository $clientRepository,
        SalesRepresentativeService $salesRepresentativeService
    ) {
        $this->authenticationService = $authenticationService;
        $this->gridHelper = $gridHelper;
        $this->salesRepresentativeService = $salesRepresentativeService;
        $this->clientRepository = $clientRepository;
    }

    public function indexAction()
    {
        $return = $this->gridHelper->getValidationResponse('client');

        return $return;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function gridAction(Request $request): array
    {
        $searchStruct = new SalesRepresentativeSearchStruct();

        $this->gridHelper
            ->extractSearchDataInStoreFront($request, $searchStruct);

        $identity = $this->authenticationService->getIdentity();

        /** @var SalesRepresentativeEntity $entity */
        $entity = $identity->getEntity();

        $clients = $this->clientRepository->fetchClientsList($searchStruct, $entity);

        $totalCount = $this->clientRepository->fetchTotalCount($entity, $searchStruct);

        $maxPage = $this->gridHelper
            ->getMaxPage($totalCount);

        $currentPage = (int) $request->getParam('page', 1);

        $salesRepresentativeGridState = $this->gridHelper
            ->getGridState($request, $searchStruct, $clients, $maxPage, $currentPage);

        return [
            'salesRepresentativeGrid' => $salesRepresentativeGridState,
        ];
    }

    /**
     * @param Request $request
     * @throws B2bControllerRedirectException
     */
    public function clientLoginAction(Request $request)
    {
        $id = (int) $request->requireParam('id');

        /** @var SalesRepresentativeIdentity $identity */
        $identity = $this->authenticationService->getIdentity();

        if (!$this->salesRepresentativeService->isSalesRepresentativeClient($identity, $id)) {
            throw new B2bControllerRedirectException('index', 'b2bsalesrepresentative');
        }

        try {
            $this->salesRepresentativeService->loginByAuthId($identity, $id);
        } catch (ValidationException $e) {
            $this->gridHelper->pushValidationException($e);

            throw new B2bControllerForwardException('index');
        }

        $clientIdentity = $this->authenticationService->getIdentity();

        $this->salesRepresentativeService->setClientIdentity($clientIdentity, $identity);

        throw new B2bControllerRedirectException('index', 'b2bdashboard');
    }

    /**
     * @throws B2bControllerRedirectException
     */
    public function salesRepresentativeLoginAction()
    {
        /** @var SalesRepresentativeDebtorIdentity $identity */
        $identity = $this->authenticationService->getIdentity();

        $this->salesRepresentativeService->loginByAuthId($identity, $identity->getSalesRepresentativeId());

        throw new B2bControllerRedirectException('index', 'b2bsalesrepresentative');
    }
}
