<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Framework;

class BlackListCartAccess implements CartAccessStrategyInterface
{
    /**
     * @var CartAccessStrategyInterface[]
     */
    private $strategies;

    /**
     * @param CartAccessStrategyInterface[] $strategies, ...
     */
    public function __construct(CartAccessStrategyInterface ... $strategies)
    {
        $this->strategies = $strategies;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAccess(CartAccessContext $context, CartAccessResult $cartAccessResult)
    {
        foreach ($this->strategies as $strategy) {
            $strategy->checkAccess($context, $cartAccessResult);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addInformation(CartAccessResult $cartAccessResult)
    {
        foreach ($this->strategies as $strategy) {
            $strategy->addInformation($cartAccessResult);
        }
    }
}
