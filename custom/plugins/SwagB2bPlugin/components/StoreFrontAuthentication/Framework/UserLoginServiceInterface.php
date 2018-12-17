<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

interface UserLoginServiceInterface
{
    /**
     * @param string $email
     */
    public function loginByMail(string $email);

    /**
     * @param int $authId
     */
    public function loginByAuthId(int $authId);
}
