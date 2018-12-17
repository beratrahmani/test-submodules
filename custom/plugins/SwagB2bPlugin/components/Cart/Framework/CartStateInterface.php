<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Framework;

interface CartStateInterface
{
    /**
     * @param string $state
     */
    public function setState(string $state);

    public function resetState();

    public function resetOldState();

    /**
     * @param string $state
     * @return bool
     */
    public function isState(string $state): bool;

    /**
     * @param int $id
     */
    public function setStateId(int $id);

    /**
     * @return int
     */
    public function getStateId(): int;

    public function resetStateId();

    /**
     * @return bool
     */
    public function hasStateId(): bool;

    /**
     * @return bool
     */
    public function hasState(): bool;

    /**
     * @return bool
     */
    public function hasOldState(): bool;

    /**
     * @return string
     */
    public function getOldState(): string;
}
