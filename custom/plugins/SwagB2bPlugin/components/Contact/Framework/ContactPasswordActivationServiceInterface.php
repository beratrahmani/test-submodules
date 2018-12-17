<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework;

interface ContactPasswordActivationServiceInterface
{
    /**
     * @param ContactEntity $contact
     */
    public function sendPasswordActivationEmail(ContactEntity $contact);

    /**
     * @param string $hash
     * @return void|ContactPasswordActivationEntity
     */
    public function getValidActivationByHash(string $hash);

    /**
     * @param ContactPasswordActivationEntity $activationEntity
     * @return ContactPasswordActivationEntity
     */
    public function removeActivation(
        ContactPasswordActivationEntity $activationEntity
    ): ContactPasswordActivationEntity;
}
