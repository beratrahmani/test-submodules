<?php declare(strict_types=1);

namespace Shopware\B2B\ProductName\Framework;

use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Shop\Framework\ProductServiceInterface;

class ProductNameService
{
    /**
     * @var ProductServiceInterface
     */
    private $productService;

    /**
     * @param ProductServiceInterface $productService
     */
    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @param ProductNameAware $entity
     */
    public function translateProductName(ProductNameAware $entity)
    {
        try {
            $productName = $this->productService->fetchProductNameByOrderNumber($entity->getProductOrderNumber());
            $entity->setProductName($productName);
        } catch (NotFoundException $e) {
            $entity->setProductName(null);
        }
    }
}
