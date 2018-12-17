<?php declare(strict_types=1);

namespace Shopware\B2B\SalesRepresentative\Framework;

use Shopware\B2B\Contact\Framework\ContactEntity;
use Shopware\B2B\Contact\Framework\ContactIdentity;
use Shopware\B2B\Debtor\Framework\DebtorEntity;
use Shopware\B2B\Debtor\Framework\DebtorIdentity;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthStorageAdapterInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserLoginServiceInterface;

class SalesRepresentativeService
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var AuthStorageAdapterInterface
     */
    private $authStorageAdapter;

    /**
     * @var UserLoginServiceInterface
     */
    private $loginService;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @param AuthStorageAdapterInterface $authStorageAdapter
     * @param UserLoginServiceInterface $loginService
     * @param LoginContextService $loginContextService
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        AuthStorageAdapterInterface $authStorageAdapter,
        UserLoginServiceInterface $loginService,
        LoginContextService $loginContextService
    ) {
        $this->authenticationService = $authenticationService;
        $this->authStorageAdapter = $authStorageAdapter;
        $this->loginService = $loginService;
        $this->loginContextService = $loginContextService;
    }

    /**
     * @param Identity $clientIdentity
     * @param SalesRepresentativeIdentity $salesRepresentativeIdentity
     */
    public function setClientIdentity(Identity $clientIdentity, SalesRepresentativeIdentity $salesRepresentativeIdentity)
    {
        $identityType = $this->getSalesRepresentativeIdentityType($clientIdentity);

        $methodName = 'get' . ucfirst($identityType) . 'SalesRepresentativeIdentity';

        $modifiedIdentity = $this->$methodName($salesRepresentativeIdentity, $clientIdentity);

        $this->authStorageAdapter->setIdentity($modifiedIdentity);
    }

    /**
     * @internal
     * @param Identity $identity
     * @return string
     */
    protected function getSalesRepresentativeIdentityType(Identity $identity): string
    {
        switch (get_class($identity)) {
            case DebtorIdentity::class:
                return 'debtor';
            case ContactIdentity::class:
            default:
                return 'contact';
        }
    }

    /**
     * @internal
     * @param SalesRepresentativeIdentity $salesRepresentativeIdentity
     * @param Identity $clientIdentity
     * @return SalesRepresentativeDebtorIdentity
     */
    protected function getDebtorSalesRepresentativeIdentity(
        SalesRepresentativeIdentity $salesRepresentativeIdentity,
        Identity $clientIdentity
    ): SalesRepresentativeDebtorIdentity {
        /** @var SalesRepresentativeEntity $entity */
        $entity = $salesRepresentativeIdentity->getEntity();

        /** @var DebtorEntity $clientEntity */
        $clientEntity = $clientIdentity->getEntity();

        return new SalesRepresentativeDebtorIdentity(
            $salesRepresentativeIdentity->getAuthId(),
            $clientIdentity->getAuthId(),
            $clientIdentity->getId(),
            $clientIdentity->getTableName(),
            $clientEntity,
            $this->loginContextService->getAvatar($clientIdentity->getAuthId()),
            $entity->mediaId,
            $clientIdentity->isApiUser()
        );
    }

    /**
     * @internal
     * @param SalesRepresentativeIdentity $salesRepresentativeIdentity
     * @param Identity $clientIdentity
     * @return SalesRepresentativeContactIdentity
     */
    protected function getContactSalesRepresentativeIdentity(
        SalesRepresentativeIdentity $salesRepresentativeIdentity,
        Identity $clientIdentity
    ): SalesRepresentativeContactIdentity {
        /** @var SalesRepresentativeEntity $entity */
        $entity = $salesRepresentativeIdentity->getEntity();

        /** @var ContactEntity $clientEntity */
        $clientEntity = $clientIdentity->getEntity();

        /** @var DebtorIdentity $debtorIdentity */
        $debtorIdentity = $this->authenticationService
            ->getIdentityByAuthId($clientIdentity->getOwnershipContext()->contextOwnerId);

        return new SalesRepresentativeContactIdentity(
            $salesRepresentativeIdentity->getAuthId(),
            $clientIdentity->getAuthId(),
            $clientIdentity->getId(),
            $clientIdentity->getTableName(),
            $clientEntity,
            $debtorIdentity,
            $this->loginContextService->getAvatar($clientIdentity->getAuthId()),
            $entity->mediaId
        );
    }

    /**
     * @param SalesRepresentativeIdentity $identity
     * @param int $clientId
     * @return bool
     */
    public function isSalesRepresentativeClient(SalesRepresentativeIdentity $identity, int $clientId): bool
    {
        /** @var SalesRepresentativeEntity $entity */
        $entity = $identity->getEntity();

        foreach ($entity->clients as $client) {
            if ((int) $client->authId === $clientId) {
                return true;
            }
        }

        return false;
    }

    /**
     * SalesRepresentativeIdentityInterface Param to prevent login without right identity
     * @param SalesRepresentativeIdentityInterface $identity
     * @param int $id
     */
    public function loginByAuthId(SalesRepresentativeIdentityInterface $identity, int $id)
    {
        $this->loginService->loginByAuthId($id);
    }
}
