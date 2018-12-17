<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Bridge;

interface CartAccessModeInterface extends CartAccessDefaultModeInterface
{
    /**
     * @return bool
     */
    public function isAvailable(): bool;
}
