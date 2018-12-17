<?php declare(strict_types=1);

namespace Shopware\B2B\Debtor\Framework;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationIdentityLoaderInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\CredentialsEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\StoreFrontAuthenticationEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\StoreFrontAuthenticationRepository;

class DebtorAuthenticationIdentityLoader implements AuthenticationIdentityLoaderInterface
{
    /**
     * @var DebtorRepository
     */
    private $debtorRepository;

    /**
     * @param DebtorRepository $debtorRepository
     */
    public function __construct(DebtorRepository $debtorRepository)
    {
        $this->debtorRepository = $debtorRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchIdentityByEmail(string $email, LoginContextService $contextService, bool $isApi = false): Identity
    {
        $entity = $this->debtorRepository->fetchOneByEmail($email);

        return $this->getIdentity($entity, $contextService, $isApi);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchIdentityByAuthentication(StoreFrontAuthenticationEntity $authentication, LoginContextService $contextService, bool $isApi = false): Identity
    {
        if (!is_a(DebtorRepository::class, $authentication->providerKey, true)) {
            throw new NotFoundException('The given authentication, does not belong to this loader');
        }

        $entity = $this->debtorRepository
            ->fetchOneById($authentication->providerContext);

        return $this->getIdentity($entity, $contextService, $isApi);
    }

    /**
     * @internal
     * @param DebtorEntity $entity
     * @param LoginContextService $contextService
     * @param bool $isApi
     * @return DebtorIdentity
     */
    protected function getIdentity(DebtorEntity $entity, LoginContextService $contextService, bool $isApi)
    {
        $authId = $contextService->getAuthId(DebtorRepository::class, $entity->id);

        return new DebtorIdentity(
            $authId,
            (int) $entity->id,
            DebtorRepository::TABLE_NAME,
            $entity,
            $contextService->getAvatar($authId),
            $isApi
        );
    }

    /**
     * {@inheritdoc}
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
                'debtor',
                StoreFrontAuthenticationRepository::TABLE_ALIAS . '.provider_context = debtor.id AND '
                . StoreFrontAuthenticationRepository::TABLE_ALIAS . '.provider_key = :debtorProviderKey'
            )
            ->setParameter('debtorProviderKey', DebtorRepository::class);
    }
}
