<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Common\Repository\NotFoundException;

interface AuthenticationIdentityLoaderInterface
{
    /**
     * @param string $email
     * @param LoginContextService $contextService
     * @param bool $isApi
     * @throws NotFoundException
     * @return Identity
     */
    public function fetchIdentityByEmail(string $email, LoginContextService $contextService, bool $isApi = false): Identity;

    /**
     * @param StoreFrontAuthenticationEntity $authentication
     * @param LoginContextService $contextService
     * @param bool $isApi
     * @throws NotFoundException
     * @return Identity
     */
    public function fetchIdentityByAuthentication(StoreFrontAuthenticationEntity $authentication, LoginContextService $contextService, bool $isApi = false): Identity;

    /**
     * @param CredentialsEntity $credentialsEntity
     * @param LoginContextService $contextService
     * @param bool $isApi
     * @throws NotFoundException
     * @return Identity
     */
    public function fetchIdentityByCredentials(CredentialsEntity $credentialsEntity, LoginContextService $contextService, bool $isApi = false): Identity;

    /**
     * @param QueryBuilder $query
     * @return
     */
    public function addSubSelect(QueryBuilder $query);
}
