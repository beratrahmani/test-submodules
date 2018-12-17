<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Framework;

class WhiteListCartAccess implements CartAccessStrategyInterface
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
     * Cases:
     *      * One or more are allowed => allowed, no messages
     *      * None is allowed => not allowed, all messages
     *      * No strategy => not allowed, an error is necessary
     *
     * {@inheritdoc}
     */
    public function checkAccess(CartAccessContext $context, CartAccessResult $cartAccessResult)
    {
        $localResults = [];

        foreach ($this->strategies as $strategy) {
            $localResult = new CartAccessResult();

            $strategy->checkAccess($context, $localResult);

            if (!$localResult->hasErrors()) {
                break;
            }

            $localResults[] = $localResult;
        }

        $cartAccessResult->addErrors(... $localResults);
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
