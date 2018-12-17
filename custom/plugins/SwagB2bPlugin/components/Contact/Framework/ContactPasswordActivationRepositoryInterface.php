<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework;

interface ContactPasswordActivationRepositoryInterface
{
    /**
     * @param ContactPasswordActivationEntity $activation
     * @return ContactPasswordActivationEntity
     */
    public function addContactActivation(ContactPasswordActivationEntity $activation): ContactPasswordActivationEntity;

    /**
     * @param string $hash
     * @return ContactPasswordActivationEntity
     */
    public function fetchOneByHash(string $hash): ContactPasswordActivationEntity;

    /**
     * @param ContactPasswordActivationEntity $activation
     * @return ContactPasswordActivationEntity
     */
    public function removeActivation(ContactPasswordActivationEntity $activation): ContactPasswordActivationEntity;
}
