<?php declare(strict_types=1);

namespace Shopware\B2B\FastOrder\Framework;

use Shopware\B2B\Common\File\CsvReader;
use Shopware\B2B\Common\File\XlsReader;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemReference;
use Shopware\B2B\LineItemList\Framework\LineItemShopWriterServiceInterface;
use Shopware\B2B\Order\Framework\OrderLineItemReferenceCrudService;
use Shopware\B2B\OrderList\Framework\OrderListCrudService;
use Shopware\B2B\OrderList\Framework\OrderListRepository;
use Shopware\B2B\OrderNumber\Framework\OrderNumberRepositoryInterface;
use Shopware\B2B\Shop\Framework\ProductServiceInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use Symfony\Component\HttpFoundation\File\File;

class FastOrderService
{
    /**
     * @var CsvReader
     */
    private $csvReader;

    /**
     * @var XlsReader
     */
    private $xlsReader;

    /**
     * @var ProductServiceInterface
     */
    private $productService;

    /**
     * @var OrderListCrudService
     */
    private $orderListCrudService;

    /**
     * @var OrderListRepository
     */
    private $orderListRepository;

    /**
     * @var OrderLineItemReferenceCrudService
     */
    private $lineItemReferenceCrudService;

    /**
     * @var LineItemShopWriterServiceInterface
     */
    private $lineItemShopWriterService;

    /**
     * @var OrderNumberRepositoryInterface
     */
    private $orderNumberRepository;

    /**
     * @param CsvReader $csvReader
     * @param XlsReader $xlsReader
     * @param ProductServiceInterface $productService
     * @param OrderListCrudService $orderListCrudService
     * @param OrderListRepository $orderListRepository
     * @param OrderLineItemReferenceCrudService $lineItemReferenceCrudService
     * @param LineItemShopWriterServiceInterface $lineItemShopWriterService
     * @param OrderNumberRepositoryInterface $orderNumberRepository
     */
    public function __construct(
        CsvReader $csvReader,
        XlsReader $xlsReader,
        ProductServiceInterface $productService,
        OrderListCrudService $orderListCrudService,
        OrderListRepository $orderListRepository,
        OrderLineItemReferenceCrudService $lineItemReferenceCrudService,
        LineItemShopWriterServiceInterface $lineItemShopWriterService,
        OrderNumberRepositoryInterface $orderNumberRepository
    ) {
        $this->csvReader = $csvReader;
        $this->xlsReader = $xlsReader;
        $this->productService = $productService;
        $this->orderListCrudService = $orderListCrudService;
        $this->orderListRepository = $orderListRepository;
        $this->lineItemReferenceCrudService = $lineItemReferenceCrudService;
        $this->lineItemShopWriterService = $lineItemShopWriterService;
        $this->orderNumberRepository = $orderNumberRepository;
    }

    /**
     * @param array $file
     * @param FastOrderContext $fastOrderContext
     * @param OwnershipContext $ownershipContext
     * @return array
     */
    public function processFastOrderFile(array $file, FastOrderContext $fastOrderContext, OwnershipContext $ownershipContext): array
    {
        $fileObject = $this->getFileObject($file);
        $fastOrders = $this->createLineItemReferencesFromFileObject($fileObject, $fastOrderContext, $ownershipContext);

        if (isset($fastOrders['error'])) {
            return $fastOrders;
        }

        $productOrderNumbers = array_keys($fastOrders);

        $matchingProductOrderNumbers = $this->productService
            ->fetchProductNamesByOrderNumbers($productOrderNumbers);

        $notMatchingProducts = array_diff(
            $productOrderNumbers,
            array_keys($matchingProductOrderNumbers)
        );

        $products = [];

        foreach ($matchingProductOrderNumbers as $productNumber => $productName) {
            $lineItemReference = $fastOrders[$productNumber];
            $lineItemReference->name = $productName;

            $products[] = $lineItemReference;
        }

        return [
            'matchingProducts' => $products,
            'notMatchingProducts' => $notMatchingProducts,
        ];
    }

    /**
     * @internal
     * @param array $file
     * @return File
     */
    protected function getFileObject(array $file)
    {
        $fileObject = new File($file['tmp_name']);
        $fileName = uniqid('ShopwareB2bFastOrder_', true) . '_' . $file['name'];

        $fileObject = $fileObject->move($fileObject->getPath(), $fileName);

        return $fileObject;
    }

    /**
     * @internal
     * @param File $fileObject
     * @param FastOrderContext $fastOrderContext
     * @param OwnershipContext $ownershipContext
     * @return array|LineItemReference[]
     */
    protected function createLineItemReferencesFromFileObject(File $fileObject, FastOrderContext $fastOrderContext, OwnershipContext $ownershipContext): array
    {
        $filePath = $fileObject->getPath() . '/' . $fileObject->getFilename();
        $fileExtension = $fileObject->getExtension();

        if ($fileExtension === 'csv') {
            $fastOrders = $this->csvReader
                ->read($filePath, $fastOrderContext);
        } elseif (in_array($fileExtension, ['xls', 'xlsx'], true)) {
            $fastOrders = $this->xlsReader
                ->read($filePath, $fastOrderContext);
        } else {
            return ['error' => 'file'];
        }

        return $this->mapDataToLineItemReference($fastOrders, $fastOrderContext, $ownershipContext);
    }

    /**
     * @internal
     * @param array $data
     * @param FastOrderContext $fastOrderContext
     * @param OwnershipContext $ownershipContext
     * @return array|LineItemReference[]
     */
    protected function mapDataToLineItemReference(array $data, FastOrderContext $fastOrderContext, OwnershipContext $ownershipContext)
    {
        $lineItemReferences = [];

        foreach ($data as $value) {
            $orderNumber = trim((string) $value[$fastOrderContext->orderNumberColumn]);
            $quantity = (int) ($value[$fastOrderContext->quantityColumn] ?? 0);

            if (!$orderNumber) {
                continue;
            }

            $orderNumber = $this->orderNumberRepository->fetchCustomOrderNumber($orderNumber, $ownershipContext);

            if (isset($lineItemReferences[$orderNumber])) {
                $lineItemReferences[$orderNumber]->quantity += $quantity;
            } else {
                $reference = new LineItemReference();
                $reference->referenceNumber = $orderNumber;
                $reference->quantity = $quantity;

                $lineItemReferences[$orderNumber] = $reference;
            }
        }

        if (count($lineItemReferences) === 0) {
            return ['error' => 'products'];
        }

        return $lineItemReferences;
    }

    /**
     * @param LineItemList $lineItemList
     * @param bool $clearBasket
     * @return array
     */
    public function produceCart(LineItemList $lineItemList, $clearBasket = false): array
    {
        return $this->lineItemShopWriterService
            ->triggerCart($lineItemList, $clearBasket);
    }
}
