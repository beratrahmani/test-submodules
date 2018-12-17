<?php declare(strict_types=1);

namespace Shopware\B2B\Account\Framework;

interface AccountServiceInterface
{
    /**
     * Set a new password for the current authenticated identity
     *
     * @param string $currentPassword
     * @param string $newPassword
     */
    public function savePassword(string $currentPassword, string $newPassword);
}
