<?php declare(strict_types=1);

namespace Shopware\B2B\FastOrder\Frontend;

use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\FastOrder\Framework\FastOrderContext;
use Shopware\B2B\FastOrder\Framework\FastOrderService;
use Shopware\B2B\Shop\Framework\ProductServiceInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class FastOrderController
{
    /**
     * @var FastOrderService
     */
    private $fastOrderService;

    /**
     * @var ProductServiceInterface
     */
    private $productService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param FastOrderService $fastOrderService
     * @param ProductServiceInterface $productService
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        FastOrderService $fastOrderService,
        ProductServiceInterface $productService,
        AuthenticationService $authenticationService
    ) {
        $this->fastOrderService = $fastOrderService;
        $this->productService = $productService;
        $this->authenticationService = $authenticationService;
    }

    public function indexAction()
    {
        // nth
    }

    public function uploadAction()
    {
        // nth
    }

    public function defaultListAction()
    {
        // nth
    }

    /**
     * @param Request $request
     * @return array
     */
    public function processUploadAction(Request $request): array
    {
        $fastOrderContext = $this->createFastOrderContextFromRequest($request);
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $fastOrderFile = $request->requireFileParam('uploadedFile');

        $products = $this->fastOrderService
            ->processFastOrderFile(
                $fastOrderFile,
                $fastOrderContext,
                $ownershipContext
            );

        return $products;
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
     * @internal
     * @param Request $request
     * @return FastOrderContext
     */
    protected function createFastOrderContextFromRequest(Request $request): FastOrderContext
    {
        $context = new FastOrderContext();
        if ($request->hasParam('orderNumberColumn')) {
            $context->orderNumberColumn = (int) $request
                ->getParam('orderNumberColumn');
        }

        if ($request->hasParam('quantityColumn')) {
            $context->quantityColumn = (int) $request
                ->getParam('quantityColumn');
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
}
