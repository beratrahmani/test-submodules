<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\TimeRestrictionType;

use Shopware\B2B\Cart\Framework\CartAccessContext;
use Shopware\B2B\Cart\Framework\CartAccessResult;
use Shopware\B2B\Cart\Framework\CartAccessStrategyInterface;
use Shopware\B2B\Cart\Framework\CartHistory;

class OrderAmountAccessStrategy implements CartAccessStrategyInterface
{
    /**
     * @var CartHistory
     */
    private $cartHistory;

    /**
     * @var float
     */
    private $value;

    /**
     * @param CartHistory $cartHistory
     * @param float $value
     */
    public function __construct(CartHistory $cartHistory, float $value)
    {
        $this->cartHistory = $cartHistory;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAccess(CartAccessContext $context, CartAccessResult $cartAccessResult)
    {
        $history = $this->cartHistory->orderAmount + (float) $context->orderClearanceEntity->list->amountNet;

        if ($history <= $this->value) {
            return;
        }

        $cartAccessResult->addError(
            __CLASS__,
            'OrderAmountError',
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
            'OrderAmountError',
            [
                'allowedValue' => $this->value,
                'cartHistory' => $this->cartHistory,
                'identifier' => spl_object_hash($this),
            ]
        );
    }
}
