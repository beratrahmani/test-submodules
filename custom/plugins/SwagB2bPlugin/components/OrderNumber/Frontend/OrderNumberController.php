<?php declare(strict_types=1);

namespace Shopware\B2B\OrderNumber\Frontend;

use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Common\Repository\SearchStruct;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\OrderNumber\Framework\OrderNumberContext;
use Shopware\B2B\OrderNumber\Framework\OrderNumberCrudService;
use Shopware\B2B\OrderNumber\Framework\OrderNumberRepositoryInterface;
use Shopware\B2B\OrderNumber\Framework\OrderNumberService;
use Shopware\B2B\OrderNumber\Framework\UnsupportedFileException;
use Shopware\B2B\Shop\Framework\ProductServiceInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class OrderNumberController
{
    /**
     * @var ProductServiceInterface
     */
    private $productService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var OrderNumberRepositoryInterface
     */
    private $orderNumberRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var OrderNumberService
     */
    private $orderNumberService;

    /**
     * @var OrderNumberCrudService
     */
    private $orderNumberCrudService;

    /**
     * @param ProductServiceInterface $productService
     * @param AuthenticationService $authenticationService
     * @param OrderNumberRepositoryInterface $orderNumberRepository
     * @param GridHelper $gridHelper
     * @param OrderNumberService $orderNumberCsvService
     * @param OrderNumberCrudService $orderNumberCrudService
     */
    public function __construct(
        ProductServiceInterface $productService,
        AuthenticationService $authenticationService,
        OrderNumberRepositoryInterface $orderNumberRepository,
        GridHelper $gridHelper,
        OrderNumberService $orderNumberCsvService,
        OrderNumberCrudService $orderNumberCrudService
    ) {
        $this->productService = $productService;
        $this->authenticationService = $authenticationService;
        $this->orderNumberRepository = $orderNumberRepository;
        $this->gridHelper = $gridHelper;
        $this->orderNumberService = $orderNumberCsvService;
        $this->orderNumberCrudService = $orderNumberCrudService;
    }

    public function indexAction()
    {
        // nth
    }

    /**
     * @param Request $request
     * @return array
     */
    public function gridAction(Request $request): array
    {
        $ownerShip = $this->authenticationService->getIdentity()->getOwnershipContext();

        $searchStruct = new SearchStruct();
        $currentPage = (int) $request->getParam('page', 1);

        $this->gridHelper
            ->extractSearchDataInStoreFront($request, $searchStruct);

        $orderNumbers = $this->orderNumberRepository
            ->fetchList($searchStruct, $ownerShip);

        $orderNumbers = $this->orderNumberService->fetchOrderNumberProductNames($orderNumbers);

        $totalCount = $this->orderNumberRepository
            ->fetchTotalCount($searchStruct, $ownerShip);

        $maxPage = $this->gridHelper
            ->getMaxPage($totalCount);

        $gridState = $this->gridHelper
            ->getGridState($request, $searchStruct, $orderNumbers, $maxPage, $currentPage);

        $validationResponse = $this->gridHelper->getValidationResponse('orderNumber');

        $errors = $request->getParam('errors');

        if ($errors) {
            $validationResponse = array_merge($validationResponse, ['errors' => $errors]);
        }

        return array_merge(
            [
                'gridState' => $gridState,
                'message' => $request->getParam('message'),
            ],
            $validationResponse
        );
    }

    /**
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request): array
    {
        $request->checkPost();

        $post = $request->getPost();

        $serviceRequest = $this->orderNumberCrudService
            ->createNewRecordRequest($post);

        $identity = $this->authenticationService
            ->getIdentity();

        $message = null;
        try {
            $orderNumber = $this->orderNumberCrudService
                ->create($serviceRequest, $identity->getOwnershipContext());
            $message = [
                'snippetKey' => 'TheCustomOrderNumberValueWasCreated',
                'messageTemplate' => 'The custom ordernumber %value% was created.',
                'parameters' => [
                    '%value%' => $orderNumber->customOrderNumber,
                ],
            ];
        } catch (ValidationException $e) {
            $this->gridHelper->pushValidationException($e);
        }

        throw new B2bControllerForwardException('grid', null, null, ['message' => $message]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function updateAction(Request $request): array
    {
        $request->checkPost();

        $post = $request->getPost();
        $serviceRequest = $this->orderNumberCrudService
            ->createExistingRecordRequest($post);

        $ownershipContext = $this->authenticationService->getIdentity()
            ->getOwnershipContext();

        $message = null;
        try {
            $orderNumber = $this->orderNumberCrudService
                ->update($serviceRequest, $ownershipContext);
            $message = [
                'snippetKey' => 'TheCustomOrderNumberValueWasUpdated',
                'messageTemplate' => 'The custom ordernumber %value% was updated.',
                'parameters' => [
                    '%value%' => $orderNumber->customOrderNumber,
                ],
            ];
        } catch (ValidationException $e) {
            $this->gridHelper->pushValidationException($e);
        }

        throw new B2bControllerForwardException('grid', null, null, ['message' => $message]);
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function removeAction(Request $request)
    {
        $request->checkPost();
        $id = (int) $request->getParam('id');

        $ownershipContext = $this->authenticationService->getIdentity()
            ->getOwnershipContext();

        try {
            $this->orderNumberCrudService->remove($id, $ownershipContext);
        } catch (NotFoundException $e) {
            // nth
        }

        throw new B2bControllerForwardException('grid');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getProductNameAction(Request $request): array
    {
        $orderNumber = $request->getParam('orderNumber', false);

        try {
            $productName = $this->productService->fetchProductNameByOrderNumber($orderNumber);
        } catch (NotFoundException $e) {
            $productName = false;
        }

        return ['productName' => $productName];
    }

    /**
     * @return array
     */
    public function exportCsvAction(): array
    {
        $ownerShip = $this->authenticationService->getIdentity()->getOwnershipContext();

        $csv = $this->orderNumberService->getCsvExportData($ownerShip);

        $response = new Response();

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'ordernumber-export.csv'
        );

        $response->headers->set('Content-Disposition', $disposition);

        $response->sendHeaders();

        return ['csvData' => $csv];
    }

    /**
     * @return array
     */
    public function exportXlsAction(): array
    {
        $ownerShip = $this->authenticationService->getIdentity()->getOwnershipContext();

        $xls = $this->orderNumberService->getXlsExportData($ownerShip);

        $response = new Response();

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'ordernumber-export.xls'
        );

        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Cache-Control', 'max-age=0');

        $response->sendHeaders();

        return ['xlsData' => $xls];
    }
    
    public function uploadAction()
    {
        // nth
    }

    /**
     * @param Request $request
     */
    public function processUploadAction(Request $request)
    {
        $orderNumberContext = $this->createOrderNumberContextFromRequest($request);
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $uploadedFile = $request->requireFileParam('uploadedFile');
        $errors = null;
        $message = null;
        try {
            $this->orderNumberService->processOrderNumberFile($uploadedFile, $orderNumberContext, $ownershipContext);
            $message = [
                'snippetKey' => 'TheCustomOrderNumbersWereUpdated',
                'messageTemplate' => 'The custom ordernumbers were updated.',
                ];
        } catch (UnsupportedFileException $e) {
            $errors = $this->createErrorArray($e);
        } catch (ValidationException $exception) {
            $this->gridHelper->pushValidationException($exception);
        }

        throw new B2bControllerForwardException('grid', 'b2bordernumber', null, ['errors' => $errors, 'message' => $message]);
    }

    /**
     * @internal
     * @param Request $request
     * @return OrderNumberContext
     */
    protected function createOrderNumberContextFromRequest(Request $request): OrderNumberContext
    {
        $context = new OrderNumberContext();
        if ($request->hasParam('orderNumberColumn')) {
            $context->orderNumberColumn = (int) $request
                ->getParam('orderNumberColumn');
        }

        if ($request->hasParam('customOrderNumberColumn')) {
            $context->customOrderNumberColumn = (int) $request
                ->getParam('customOrderNumberColumn');
        }

        if ($request->hasParam('csvDelimiter')) {
            $context->csvDelimiter = $request
                ->getParam('csvDelimiter');
        }

        if ($request->hasParam('csvEnclosure') && ($enclosure = $request->getParam('csvEnclosure')) !== '') {
            $context->csvEnclosure = $enclosure;
        }

        if ($request->hasParam('headline')) {
            $context->headline = $request
                    ->getParam('headline') === 'true';
        }

        return $context;
    }

    /**
     * @internal
     * @param UnsupportedFileException $e
     * @return array
     */
    protected function createErrorArray(UnsupportedFileException $e): array
    {
        return [
            [
                'snippetKey' => 'FileExtensionInvalid',
                'messageTemplate' => 'The file extension %value% was invalid.',
                'parameters' => [
                    '%value%' => $e->getFileExtension(),
                ],
            ],
        ];
    }
}
