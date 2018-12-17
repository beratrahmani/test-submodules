<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

class CredentialsBuilder
{
    /**
     * @param array $parameters
     * @return CredentialsEntity
     */
    public function createCredentials(array $parameters): CredentialsEntity
    {
        $entity = new CredentialsEntity();

        $entity->email = $parameters['email'];

        return $entity;
    }
}
