<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\ProductPriceType;

use Shopware\B2B\Cart\Framework\CartAccessContext;
use Shopware\B2B\Cart\Framework\CartAccessResult;
use Shopware\B2B\Cart\Framework\CartAccessStrategyInterface;
use Shopware\B2B\LineItemList\Framework\LineItemReference;

class ProductPriceAccessStrategy implements CartAccessStrategyInterface
{
    /**
     * @var float
     */
    private $productPrice;

    /**
     * @param float $productPrice
     */
    public function __construct(float $productPrice)
    {
        $this->productPrice = $productPrice;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAccess(CartAccessContext $context, CartAccessResult $cartAccessResult)
    {
        $products = $context->orderClearanceEntity
            ->list
            ->references;

        $productErrors = array_filter($products, function (LineItemReference $lineItem) {
            return $lineItem->amountNet > $this->productPrice;
        });

        if (!count($productErrors)) {
            return;
        }

        $cartAccessResult->addError(
            __CLASS__,
            'ProductPriceError',
            [
                'allowedValue' => $this->productPrice,
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
            'ProductPriceError',
            [
                'allowedValue' => $this->productPrice,
                'identifier' => spl_object_hash($this),
            ]
        );
    }
}
