<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Framework;

class NotEmptyWhiteListCartAccess extends WhiteListCartAccess
{
    /**
     * @var bool
     */
    private $empty = false;

    /**
     * @var string
     */
    private $defaultErrorMessageKey;

    /**
     * @param string $defaultErrorMessageKey
     * @param CartAccessStrategyInterface[] $strategies , ...
     */
    public function __construct(string $defaultErrorMessageKey, CartAccessStrategyInterface ... $strategies)
    {
        $this->defaultErrorMessageKey = $defaultErrorMessageKey;

        if (!$strategies) {
            $this->empty = true;
        }

        parent::__construct(... $strategies);
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
        if (!$this->empty) {
            parent::checkAccess($context, $cartAccessResult);

            return;
        }

        $cartAccessResult->addError(
            __CLASS__,
            $this->defaultErrorMessageKey
        );
    }
}
