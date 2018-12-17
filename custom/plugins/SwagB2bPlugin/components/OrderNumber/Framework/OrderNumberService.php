<?php declare(strict_types=1);

namespace Shopware\B2B\OrderNumber\Framework;

use Shopware\B2B\Common\File\CsvReader;
use Shopware\B2B\Common\File\CsvWriter;
use Shopware\B2B\Common\File\XlsReader;
use Shopware\B2B\Common\File\XlsWriter;
use Shopware\B2B\Shop\Framework\ProductServiceInterface;
use Shopware\B2B\Shop\Framework\TranslationServiceInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use Symfony\Component\HttpFoundation\File\File;

class OrderNumberService
{
    /**
     * @var CsvReader
     */
    private $csvReader;

    /**
     * @var OrderNumberRepositoryInterface
     */
    private $orderNumberRepository;

    /**
     * @var ProductServiceInterface
     */
    private $productService;

    /**
     * @var XlsReader
     */
    private $xlsReader;

    /**
     * @var CsvWriter
     */
    private $csvWriter;

    /**
     * @var XlsWriter
     */
    private $xlsWriter;

    /**
     * @var TranslationServiceInterface
     */
    private $translationService;

    /**
     * @var OrderNumberCrudService
     */
    private $orderNumberCrudService;

    /**
     * @param CsvReader $csvReader
     * @param XlsReader $xlsReader
     * @param OrderNumberRepositoryInterface $orderNumberRepository
     * @param ProductServiceInterface $productService
     * @param CsvWriter $csvWriter
     * @param XlsWriter $xlsWriter
     * @param TranslationServiceInterface $translationService
     * @param OrderNumberCrudService $orderNumberCrudService
     */
    public function __construct(
        CsvReader $csvReader,
        XlsReader $xlsReader,
        OrderNumberRepositoryInterface $orderNumberRepository,
        ProductServiceInterface $productService,
        CsvWriter $csvWriter,
        XlsWriter $xlsWriter,
        TranslationServiceInterface $translationService,
        OrderNumberCrudService $orderNumberCrudService
    ) {
        $this->csvReader = $csvReader;
        $this->orderNumberRepository = $orderNumberRepository;
        $this->productService = $productService;
        $this->xlsReader = $xlsReader;
        $this->csvWriter = $csvWriter;
        $this->xlsWriter = $xlsWriter;
        $this->translationService = $translationService;
        $this->orderNumberCrudService = $orderNumberCrudService;
    }

    /**
     * @param array $uploadedFile
     * @param OrderNumberContext $orderNumberContext
     * @param OwnershipContext $ownershipContext
     */
    public function processOrderNumberFile(
        array $uploadedFile,
        OrderNumberContext $orderNumberContext,
        OwnershipContext $ownershipContext
    ) {
        $fileObject = $this->getFileObject($uploadedFile);
        $orderNumberFileEntities = $this->extractOrderNumbersFromFile($fileObject, $orderNumberContext);

        $this->orderNumberCrudService->replace($orderNumberFileEntities, $ownershipContext);
    }

    /**
     * @param File $fileObject
     * @param OrderNumberContext $orderNumberContext
     * @return OrderNumberFileEntity[]
     * @internal
     */
    protected function extractOrderNumbersFromFile(File $fileObject, OrderNumberContext $orderNumberContext): array
    {
        $filePath = $fileObject->getPath() . '/' . $fileObject->getFilename();
        $fileExtension = strtolower($fileObject->getExtension());

        if ($fileExtension === 'csv') {
            $orderNumberData = $this->csvReader
                ->read($filePath, $orderNumberContext);
        } elseif (in_array($fileExtension, ['xls', 'xlsx'], true)) {
            $orderNumberData = $this->xlsReader
                ->read($filePath, $orderNumberContext);
        } else {
            throw new UnsupportedFileException($fileExtension);
        }

        return $this->mapDataToOrderNumberEntity($orderNumberData, $orderNumberContext);
    }

    /**
     * @internal
     * @param array $file
     * @return File
     */
    protected function getFileObject(array $file): File
    {
        $fileObject = new File($file['tmp_name']);
        $fileName = uniqid('ShopwareB2bOrderNumber_', true) . '_' . $file['name'];

        $fileObject = $fileObject->move($fileObject->getPath(), $fileName);

        return $fileObject;
    }

    /**
     * @internal
     * @param array $data
     * @param OrderNumberContext $orderNumberContext
     * @return OrderNumberFileEntity[]
     */
    protected function mapDataToOrderNumberEntity(array $data, OrderNumberContext $orderNumberContext): array
    {
        $orderNumberEntities = [];
        $rowOffset = 1 + (int) $orderNumberContext->headline;

        foreach ($data as $row => $value) {
            $columnCount = count($value);

            $orderNumberEntity = new OrderNumberFileEntity();
            if ($columnCount > $orderNumberContext->orderNumberColumn) {
                $orderNumberEntity->orderNumber = trim((string) $value[$orderNumberContext->orderNumberColumn]);
            } else {
                $orderNumberEntity->orderNumber = '';
            }
            if ($columnCount > $orderNumberContext->customOrderNumberColumn) {
                $orderNumberEntity->customOrderNumber = trim((string) $value[$orderNumberContext->customOrderNumberColumn]);
            } else {
                $orderNumberEntity->customOrderNumber = '';
            }

            $orderNumberEntity->row = $row + $rowOffset;
            $orderNumberEntities[] = $orderNumberEntity;
        }

        return $orderNumberEntities;
    }

    /**
     * @param OwnershipContext $ownerShip
     * @return string
     */
    public function getCsvExportData(OwnershipContext $ownerShip): string
    {
        $exportData = $this->fetchExportData($ownerShip);

        $name = tempnam('/tmp', 'csv');

        $this->csvWriter->write($exportData, $name);
        $csv = file_get_contents($name);

        unlink($name);

        return $csv;
    }

    /**
     * @param OwnershipContext $ownerShip
     * @return string
     */
    public function getXlsExportData(OwnershipContext $ownerShip): string
    {
        $exportData = $this->fetchExportData($ownerShip);

        $name = tempnam('/tmp', 'xls');

        $this->xlsWriter->write($exportData, $name);
        $xls = file_get_contents($name);

        unlink($name);

        return $xls;
    }

    /**
     * @param OwnershipContext $context
     * @return array
     * @internal
     */
    protected function fetchExportData(OwnershipContext $context): array
    {
        $products = $this->orderNumberRepository->fetchAllProductsForExport($context);
        $products = $this->fetchOrderNumberProductNames($products);

        return $this->parseExportData(...$products);
    }

    /**
     * @internal
     * @param OrderNumberEntity[] $orderNumberEntities
     * @return array
     */
    protected function parseExportData(OrderNumberEntity ... $orderNumberEntities): array
    {
        $product = $this->translationService->get('Product', 'frontend/plugins/b2b_debtor_plugin', 'Product');
        $productOrderNumber = $this->translationService->get('ProductOrderNumber', 'frontend/plugins/b2b_debtor_plugin', 'Productnumber');
        $customProductOrderNumber = $this->translationService->get('CustomProductOrderNumber', 'frontend/plugins/b2b_debtor_plugin', 'Custom productnumber');

        $return[] = [$product, $productOrderNumber, $customProductOrderNumber];

        foreach ($orderNumberEntities as $orderNumberEntity) {
            $orderNumberArray = [];
            $orderNumberArray[$product] = $orderNumberEntity->name;
            $orderNumberArray[$productOrderNumber] = $orderNumberEntity->orderNumber;
            $orderNumberArray[$customProductOrderNumber] = $orderNumberEntity->customOrderNumber;

            $return[] = $orderNumberArray;
        }

        return $return;
    }

    /**
     * @param OrderNumberEntity[] $orderNumberEntities
     * @return OrderNumberEntity[]
     */
    public function fetchOrderNumberProductNames(array $orderNumberEntities): array
    {
        $orderNumbers = array_map(
            function (OrderNumberEntity $orderNumberEntity) {
                return $orderNumberEntity->orderNumber;
            },
            $orderNumberEntities
        );
        $productNames =  $this->productService->fetchProductNamesByOrderNumbers($orderNumbers);

        foreach ($orderNumberEntities as $orderNumberEntity) {
            if (isset($productNames[$orderNumberEntity->customOrderNumber])) {
                $productName = $productNames[$orderNumberEntity->customOrderNumber];

                $orderNumberEntity->name = $productName;
            }
        }

        return $orderNumberEntities;
    }
}
