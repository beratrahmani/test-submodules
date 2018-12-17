<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Common\Repository\NotFoundException;

class IdentityChainIdentityLoader implements AuthenticationIdentityLoaderInterface
{
    /**
     * @var AuthenticationIdentityLoaderInterface[]
     */
    private $authenticationRepositories;

    /**
     * @param AuthenticationIdentityLoaderInterface[] $authenticationRepositories
     */
    public function __construct(array $authenticationRepositories)
    {
        $this->setRepositories($authenticationRepositories);
    }

    /**
     * @param AuthenticationIdentityLoaderInterface[] $authenticationRepositories
     */
    public function setRepositories(array $authenticationRepositories)
    {
        $this->authenticationRepositories = [];

        foreach ($authenticationRepositories as $repository) {
            $this->addRepository($repository);
        }
    }

    /**
     * @param AuthenticationIdentityLoaderInterface $repository
     */
    public function addRepository(AuthenticationIdentityLoaderInterface $repository)
    {
        $this->authenticationRepositories[] = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchIdentityByEmail(string $email, LoginContextService $contextService, bool $isApi = false): Identity
    {
        foreach ($this->authenticationRepositories as $repository) {
            try {
                return $repository->fetchIdentityByEmail($email, $contextService);
            } catch (NotFoundException $e) {
                continue;
            }
        }

        throw new NotFoundException(sprintf('No identity found with email %s', $email));
    }

    /**
     * {@inheritdoc}
     */
    public function fetchIdentityByAuthentication(StoreFrontAuthenticationEntity $authentication, LoginContextService $contextService, bool $isApi = false): Identity
    {
        foreach ($this->authenticationRepositories as $repository) {
            try {
                return $repository->fetchIdentityByAuthentication($authentication, $contextService);
            } catch (NotFoundException $e) {
                continue;
            }
        }

        throw new NotFoundException(sprintf('No identity found from %s and id %s', $authentication->providerKey, $authentication->providerContext));
    }

    /**
     * {@inheritdoc}
     */
    public function fetchIdentityByCredentials(CredentialsEntity $credentialsEntity, LoginContextService $contextService, bool $isApi = false): Identity
    {
        foreach ($this->authenticationRepositories as $repository) {
            try {
                return $repository->fetchIdentityByCredentials($credentialsEntity, $contextService);
            } catch (NotFoundException $e) {
                continue;
            }
        }

        throw new NotFoundException('No identity found with the credentials');
    }

    /**
     * {@inheritdoc}
     */
    public function addSubSelect(QueryBuilder $query)
    {
        foreach ($this->authenticationRepositories as $repository) {
            $repository->addSubSelect($query);
        }
    }
}
