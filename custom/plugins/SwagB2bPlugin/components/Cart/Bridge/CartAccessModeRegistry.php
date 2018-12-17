<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Bridge;

class CartAccessModeRegistry
{
    /**
     * @var CartAccessModeInterface[]
     */
    private $cartAccessModes;

    /**
     * @var CartAccessDefaultModeInterface
     */
    private $defaultMode;

    /**
     * @param CartAccessDefaultModeInterface $defaultMode
     * @param CartAccessModeInterface[] ...$cartAccessModes
     */
    public function __construct(CartAccessDefaultModeInterface $defaultMode, CartAccessModeInterface ...$cartAccessModes)
    {
        $this->defaultMode = $defaultMode;
        $this->cartAccessModes = $cartAccessModes;
    }

    /**
     * @return CartAccessDefaultModeInterface
     */
    public function getAvailableMode(): CartAccessDefaultModeInterface
    {
        foreach ($this->cartAccessModes as $mode) {
            if ($mode->isAvailable()) {
                return $mode;
            }
        }

        return $this->defaultMode;
    }
}
