<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\ProductOrderNumberType;

use Shopware\B2B\Cart\Framework\CartAccessContext;
use Shopware\B2B\Cart\Framework\CartAccessResult;
use Shopware\B2B\Cart\Framework\CartAccessStrategyInterface;
use Shopware\B2B\LineItemList\Framework\LineItemReference;

class ProductOrderNumberAccessStrategy implements CartAccessStrategyInterface
{
    /**
     * @var string
     */
    private $productOrderNumber;

    /**
     * @param string $productOrderNumber
     */
    public function __construct(string $productOrderNumber)
    {
        $this->productOrderNumber = $productOrderNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAccess(CartAccessContext $context, CartAccessResult $cartAccessResult)
    {
        $products = $context->orderClearanceEntity
            ->list
            ->references;

        $articleErrors = array_filter($products, function (LineItemReference $lineItem) {
            return $lineItem->referenceNumber === $this->productOrderNumber;
        });

        if (!count($articleErrors)) {
            return;
        }

        $cartAccessResult->addError(
            __CLASS__,
            'ProductOrderNumberError',
            [
                'allowedValue' => $this->productOrderNumber,
                'identifier' => spl_object_hash($this),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addInformation(CartAccessResult $cartAccessResult)
    {
        $cartAccessResult->addInformation(
            __CLASS__,
            'ProductOrderNumberError',
            [
                'allowedValue' => $this->productOrderNumber,
                'identifier' => spl_object_hash($this),
            ]
        );
    }
}
