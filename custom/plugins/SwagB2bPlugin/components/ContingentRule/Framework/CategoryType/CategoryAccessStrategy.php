<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\CategoryType;

use Shopware\B2B\Cart\Framework\CartAccessContext;
use Shopware\B2B\Cart\Framework\CartAccessResult;
use Shopware\B2B\Cart\Framework\CartAccessStrategyInterface;
use Shopware\B2B\LineItemList\Framework\LineItemReference;
use Shopware\B2B\Shop\Framework\CategoryRepositoryInterface;

class CategoryAccessStrategy implements CartAccessStrategyInterface
{
    /**
     * @var int
     */
    private $categoryId;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param int $categoryId
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(int $categoryId, CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryId = $categoryId;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAccess(CartAccessContext $context, CartAccessResult $cartAccessResult)
    {
        $products = $context->orderClearanceEntity
            ->list
            ->references;

        $categoryErrors = array_filter($products, function (LineItemReference $lineItem) {
            return $this->categoryRepository->hasProduct($this->categoryId, $lineItem->referenceNumber);
        });

        if (!count($categoryErrors)) {
            return;
        }

        $category = $this->categoryRepository
            ->fetchNodeById($this->categoryId);

        $cartAccessResult->addError(
            __CLASS__,
            'CategoryError',
            [
                'allowedValue' => $category->name,
                'identifier' => spl_object_hash($this),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addInformation(CartAccessResult $cartAccessResult)
    {
        $category = $this->categoryRepository->fetchNodeById($this->categoryId);

        $cartAccessResult->addInformation(
            __CLASS__,
            'CategoryError',
            [
                'allowedValue' => $category->name,
                'identifier' => spl_object_hash($this),
            ]
        );
    }
}
