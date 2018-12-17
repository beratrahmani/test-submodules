<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Debtor\Framework\DebtorIdentity;
use Shopware\B2B\Debtor\Framework\DebtorRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationIdentityLoaderInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\CredentialsEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\StoreFrontAuthenticationEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\StoreFrontAuthenticationRepository;

class ContactAuthenticationIdentityLoader implements AuthenticationIdentityLoaderInterface
{
    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var DebtorRepository
     */
    private $debtorRepository;

    /**
     * @param ContactRepository $contactRepository
     * @param DebtorRepository $debtorRepository
     */
    public function __construct(ContactRepository $contactRepository, DebtorRepository $debtorRepository)
    {
        $this->contactRepository = $contactRepository;
        $this->debtorRepository = $debtorRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchIdentityByEmail(string $email, LoginContextService $contextService, bool $isApi = false): Identity
    {
        $entity = $this->contactRepository->insecureFetchOneByEmail($email);

        return $this->getIdentity($entity, $contextService);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchIdentityByAuthentication(StoreFrontAuthenticationEntity $authentication, LoginContextService $contextService, bool $isApi = false): Identity
    {
        if (!is_a(ContactRepository::class, $authentication->providerKey, true)) {
            throw new NotFoundException('The given authentication, does not belong to this loader');
        }

        $entity = $this->contactRepository
            ->insecureFetchOneById($authentication->providerContext);

        return $this->getIdentity($entity, $contextService);
    }

    /**
     * @internal
     * @param ContactEntity $entity
     * @param LoginContextService $contextService
     * @return ContactIdentity
     */
    protected function getIdentity(ContactEntity $entity, LoginContextService $contextService)
    {
        /** @var DebtorIdentity $debtorIdentity */
        $debtorIdentity = $this
            ->debtorRepository
            ->fetchIdentityById($entity->debtor->id, $contextService);

        $authId = $contextService->getAuthId(ContactRepository::class, $entity->id, $debtorIdentity->getAuthId());

        $this->contactRepository->setAuthId($entity->id, $authId, $debtorIdentity->getOwnershipContext());

        return new ContactIdentity(
            $authId,
            (int) $entity->id,
            ContactRepository::TABLE_NAME,
            $entity,
            $debtorIdentity,
            $contextService->getAvatar($authId)
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
                        contact.id,
                        contact.firstname,
                        contact.lastname,
                        contact.salutation,
                        contact.email,
                        contact.active,
                        address.phone
                        FROM b2b_debtor_contact as contact 
                        INNER JOIN b2b_store_front_auth auth 
                        ON contact.context_owner_id = auth.id
                        INNER JOIN s_user
                        ON auth.provider_context = s_user.id
                        INNER JOIN s_user_addresses address
                        ON s_user.default_billing_address_id = address.id)',
                'contact',
                StoreFrontAuthenticationRepository::TABLE_ALIAS . '.provider_context = contact.id AND '
                . StoreFrontAuthenticationRepository::TABLE_ALIAS . '.provider_key = :contactProviderKey'
        )
        ->setParameter('contactProviderKey', ContactRepository::class);
    }
}
