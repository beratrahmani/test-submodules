<?php declare(strict_types=1);

namespace Shopware\B2B\SalesRepresentative\Framework;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationIdentityLoaderInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\CredentialsEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\StoreFrontAuthenticationEntity;

class ClientIdentityChainLoader implements AuthenticationIdentityLoaderInterface
{
    private $fieldNames = ['id', 'firstname', 'lastname', 'salutation', 'phone', 'email', 'active'];

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

        $query->addSelect(
            implode(
            ',',
            $this->getAdditionalSelect($query->getQueryPart('join')['auth'])
        )
        );
    }

    /**
     * @internal
     * @param array $authJoins
     */
    protected function getAdditionalSelect(array $authJoins): array
    {
        $additionalSelect = [];
        $countIdentities = 0;
        foreach ($authJoins as $joinTable) {
            foreach ($this->fieldNames as $fieldName) {
                if (!array_key_exists($fieldName, $additionalSelect)) {
                    $additionalSelect[$fieldName] = '';
                }
                $additionalSelect[$fieldName] .= 'IFNULL(' . $joinTable['joinAlias'] . '.' . $fieldName . ',';
            }
            $countIdentities += 1;
        }

        return $this->formatAdditionalSelect($additionalSelect, $countIdentities);
    }

    /**
     * @param array $additionalSelect
     * @param int $countIdentities
     * @return array
     */
    protected function formatAdditionalSelect(array $additionalSelect, int $countIdentities): array
    {
        return array_map(
            function (string $query, string $brackets, string $fieldName) {
                return $query . 'NULL' . $brackets . ' as clientData_' . $fieldName;
            },
            $additionalSelect,
            array_fill(0, count($additionalSelect), implode('', array_fill(0, $countIdentities, ')'))),
            array_keys($additionalSelect)
        );
    }
}
