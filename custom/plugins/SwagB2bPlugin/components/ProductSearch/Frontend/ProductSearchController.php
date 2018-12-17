<?php declare(strict_types=1);

namespace Shopware\B2B\ProductSearch\Frontend;

use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Shop\Framework\ProductServiceInterface;

class ProductSearchController
{
    /**
     * @var ProductServiceInterface
     */
    private $productService;

    /**
     * @param ProductServiceInterface $productService
     */
    public function __construct(
        ProductServiceInterface $productService
    ) {
        $this->productService = $productService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function searchProductAction(Request $request): array
    {
        try {
            $term = $request->requireParam('term');
        } catch (\InvalidArgumentException $exception) {
            return [];
        }

        $limit = $request->getParam('limit', 50);

        $result = $this->productService
            ->searchProductsByNameOrOrderNumber($term, $limit);

        return [
            'products' => $result,
        ];
    }
}
