<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

interface UserRepositoryInterface
{
    /**
     * @param string $mail
     * @return bool
     */
    public function isMailAvailable(string $mail): bool;

    /**
     * @param string $originalMail
     * @param string $newMail
     */
    public function updateEmail(string $originalMail, string $newMail);
}
