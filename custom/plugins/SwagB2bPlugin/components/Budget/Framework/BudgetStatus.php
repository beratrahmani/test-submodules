<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Framework;

use Shopware\B2B\OrderClearance\Framework\OrderItemEntity;

class BudgetStatus extends OrderItemEntity
{
    /**
     * @var float
     */
    public $usedBudget;

    /**
     * @var float
     */
    public $availableBudget;

    /**
     * @var float
     */
    public $remainingBudget;

    /**
     * @var float
     */
    public $percentage;

    /**
     * @var bool
     */
    public $isSufficient = true;

    /**
     * @var float
     */
    public $currencyFactor;

    /**
     * @param array $data
     * @return BudgetStatus
     */
    public function fromDataBaseArray(array $data): self
    {
        $this->currencyFactor = (float) $data['currency_factor'];
        $this->usedBudget = (float) $data['used_budget'];
        $this->availableBudget = (float) $data['available_budget'];
        $this->remainingBudget = (float) $data['remaining_budget'];
        $this->percentage = round((100 / $this->availableBudget) * $this->usedBudget);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
