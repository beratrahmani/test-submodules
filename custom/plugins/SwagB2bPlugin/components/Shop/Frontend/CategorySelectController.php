<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Frontend;

use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Shop\Framework\CategoryRepositoryInterface;
use Shopware\B2B\Shop\Framework\ShopServiceInterface;

class CategorySelectController
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var ShopServiceInterface
     */
    private $shop;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ShopServiceInterface $shop
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        ShopServiceInterface $shop
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->shop = $shop;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function gridAction(Request $request): array
    {
        $selectedCategory = (int) $request->getParam('selectedId');
        $parentId = (int) $request->getParam('parentId');

        if (!$parentId) {
            $parentId = $this->shop->getRootCategoryId();
        }

        $rows = $this->categoryRepository
            ->fetchChildren($parentId);

        return [
            'nodes' => $rows,
            'selectedId' => $selectedCategory,
        ];
    }
}
