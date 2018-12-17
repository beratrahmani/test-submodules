<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework;

interface ContactPasswordProviderInterface
{
    /**
     * @param ContactEntity $contact
     * @param $newPassword
     */
    public function setPassword(ContactEntity $contact, $newPassword);
}
