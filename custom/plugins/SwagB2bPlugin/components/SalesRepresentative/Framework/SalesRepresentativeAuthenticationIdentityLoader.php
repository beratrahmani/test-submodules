<?php declare(strict_types=1);

namespace Shopware\B2B\SalesRepresentative\Framework;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationIdentityLoaderInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\CredentialsEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\StoreFrontAuthenticationEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\StoreFrontAuthenticationRepository;

class SalesRepresentativeAuthenticationIdentityLoader implements AuthenticationIdentityLoaderInterface
{
    /**
     * @var SalesRepresentativeRepository
     */
    private $salesRepresentativeRepository;

    /**
     * @param SalesRepresentativeRepository $salesRepresentativeRepository
     */
    public function __construct(SalesRepresentativeRepository $salesRepresentativeRepository)
    {
        $this->salesRepresentativeRepository = $salesRepresentativeRepository;
    }

    /**
     * @inheritdoc
     */
    public function fetchIdentityByEmail(string $email, LoginContextService $contextService, bool $isApi = false): Identity
    {
        $entity = $this->salesRepresentativeRepository->fetchOneByEmail($email);

        return $this->getIdentity($entity, $contextService);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchIdentityByAuthentication(StoreFrontAuthenticationEntity $authentication, LoginContextService $contextService, bool $isApi = false): Identity
    {
        if (!is_a(SalesRepresentativeRepository::class, $authentication->providerKey, true)) {
            throw new NotFoundException('The given authentication, does not belong to this loader');
        }

        $entity = $this->salesRepresentativeRepository
            ->fetchOneById($authentication->providerContext);

        return $this->getIdentity($entity, $contextService);
    }

    /**
     * @internal
     * @param SalesRepresentativeEntity $entity
     * @param LoginContextService $contextService
     * @return SalesRepresentativeIdentity
     */
    protected function getIdentity(SalesRepresentativeEntity $entity, LoginContextService $contextService)
    {
        $authId = $contextService->getAuthId(SalesRepresentativeRepository::class, $entity->id);

        return new SalesRepresentativeIdentity(
            $authId,
            (int) $entity->id,
            SalesRepresentativeRepository::TABLE_NAME,
            $entity,
            $contextService->getAvatar($authId)
        );
    }

    /**
     * @inheritdoc
     */
    public function fetchIdentityByCredentials(CredentialsEntity $credentialsEntity, LoginContextService $contextService, bool $isApi = false): Identity
    {
        if (!$credentialsEntity->email) {
            throw new NotFoundException('Unable to handle context');
        }

        return $this->fetchIdentityByEmail($credentialsEntity->email, $contextService);
    }

    /**
     * {@inheritdoc}
     */
    public function addSubSelect(QueryBuilder $query)
    {
        $query->leftJoin(
            StoreFrontAuthenticationRepository::TABLE_ALIAS,
            '(SELECT 
                        s_user.id,
                        s_user.firstname,
                        s_user.lastname,
                        s_user.salutation,
                        s_user.email,
                        s_user.active,
                        address.`phone`
                        FROM s_user 
                        INNER JOIN s_user_addresses address 
                        ON s_user.default_billing_address_id = address.id)',
            'salesRepresentative',
            StoreFrontAuthenticationRepository::TABLE_ALIAS . '.provider_context = salesRepresentative.id AND '
            . StoreFrontAuthenticationRepository::TABLE_ALIAS . '.provider_key = \'' . addslashes(SalesRepresentativeRepository::class) . '\''
        );
    }
}
