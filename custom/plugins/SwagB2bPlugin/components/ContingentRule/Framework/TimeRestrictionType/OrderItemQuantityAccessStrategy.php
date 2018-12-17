<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\TimeRestrictionType;

use Shopware\B2B\Cart\Framework\CartAccessContext;
use Shopware\B2B\Cart\Framework\CartAccessResult;
use Shopware\B2B\Cart\Framework\CartAccessStrategyInterface;
use Shopware\B2B\Cart\Framework\CartHistory;

class OrderItemQuantityAccessStrategy implements CartAccessStrategyInterface
{
    /**
     * @var CartHistory
     */
    private $cartHistory;

    /**
     * @var int
     */
    private $value;

    /**
     * @param CartHistory $cartHistory
     * @param int $value
     */
    public function __construct(CartHistory $cartHistory, int $value)
    {
        $this->cartHistory = $cartHistory;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAccess(CartAccessContext $context, CartAccessResult $cartAccessResult)
    {
        $history = $this->cartHistory->orderItemQuantity + count($context->orderClearanceEntity->list->references);

        if ($history <= $this->value) {
            return;
        }

        $cartAccessResult->addError(
            __CLASS__,
            'OrderItemQuantityError',
            [
                'allowedValue' => $this->value,
                'appliedValue' => $history,
                'cartHistory' => $this->cartHistory,
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
            'OrderItemQuantityError',
            [
                'allowedValue' => $this->value,
                'cartHistory' => $this->cartHistory,
                'identifier' => spl_object_hash($this),
            ]
        );
    }
}
