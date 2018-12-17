<?php declare(strict_types=1);

namespace Shopware\B2B\SalesRepresentative\Bridge;

use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Contact\Framework\ContactEntity;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\Contact\Framework\ContactSearchStruct;
use Shopware\B2B\Debtor\Framework\DebtorEntity;
use Shopware\B2B\Debtor\Framework\DebtorIdentity;
use Shopware\B2B\Debtor\Framework\DebtorRepository;
use Shopware\B2B\SalesRepresentative\Framework\SalesRepresentativeClientRepository;
use Shopware\B2B\SalesRepresentative\Framework\SalesRepresentativeEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\StoreFrontAuthenticationRepository;

class SalesRepresentativeBackendExtension
{
    /**
     * @var DebtorRepository
     */
    private $debtorRepository;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var SalesRepresentativeClientRepository
     */
    private $clientRepository;

    /**
     * @var StoreFrontAuthenticationRepository
     */
    private $authenticationRepository;

    /**
     * @param DebtorRepository $debtorRepository
     * @param ContactRepository $contactRepository
     * @param SalesRepresentativeClientRepository $clientRepository
     * @param StoreFrontAuthenticationRepository $authenticationRepository
     */
    public function __construct(
        DebtorRepository $debtorRepository,
        ContactRepository $contactRepository,
        SalesRepresentativeClientRepository $clientRepository,
        StoreFrontAuthenticationRepository $authenticationRepository
    ) {
        $this->debtorRepository = $debtorRepository;
        $this->contactRepository = $contactRepository;
        $this->clientRepository = $clientRepository;
        $this->authenticationRepository = $authenticationRepository;
    }

    /**
     * @param int $salesRepresentativeId
     * @return array
     */
    public function clientList(int $salesRepresentativeId): array
    {
        $debtors = $this->debtorRepository->fetchAllDebtors();

        $clients = [];
        foreach ($debtors as $debtor) {
            try {
                $debtorClient = $this->getDebtorClientData($debtor);
                $clients[] = $debtorClient;
            } catch (NotFoundException $e) {
                continue;
            }

            try {
                $clients = array_merge($clients, $this->getContactsClientData(
                    $this->contactRepository->fetchList(
                        $this->createDebtorOwnerShipContext($debtor, $debtorClient['id']),
                        new ContactSearchStruct()
                    )
                ));
            } catch (NotFoundException $e) {
                // nth
            }
        }

        if (count($clients) === 0) {
            return [];
        }

        $salesRepresentative = new SalesRepresentativeEntity();
        $salesRepresentative->id = $salesRepresentativeId;

        $this->clientRepository->fetchClients($salesRepresentative);

        $this->setClientStatus($clients, $salesRepresentative);

        return $clients;
    }

    /**
     * @internal
     * @param array $clients
     * @param SalesRepresentativeEntity $salesRepresentative
     */
    protected function setClientStatus(array &$clients, SalesRepresentativeEntity $salesRepresentative)
    {
        $clientIds = [];
        foreach ($salesRepresentative->clients as $activeClient) {
            $clientIds[] = $activeClient->authId;
        }

        foreach ($clients as &$client) {
            if (in_array($client['id'], $clientIds, true)) {
                $client['client'] = true;
            }
        }
    }

    /**
     * @internal
     * @param DebtorEntity $debtor
     * @throws NotFoundException
     * @return array
     */
    protected function getDebtorClientData(DebtorEntity $debtor): array
    {
        $authId = $this->getAuthData($debtor->id, DebtorRepository::class);

        if ($authId === 0) {
            throw new NotFoundException('No Auth Data found');
        }

        return [
            'id' => $authId,
            'name' => $debtor->firstName . ' ' . $debtor->lastName,
            'email' => $debtor->email,
            'client' => false,
        ];
    }

    /**
     * @internal
     * @param ContactEntity[] $contacts
     * @throws NotFoundException
     * @return array
     */
    protected function getContactsClientData(array $contacts): array
    {
        $clientData = [];
        foreach ($contacts as $contact) {
            if (!$contact->authId) {
                continue;
            }

            $clientData[] = [
                'id' => $contact->authId,
                'name' => $contact->firstName . ' ' . $contact->lastName,
                'email' => $contact->email,
                'client' => false,
            ];
        }

        if (count($clientData) === 0) {
            throw new NotFoundException('No contact data found');
        }

        return $clientData;
    }

    /**
     * @internal
     * @param DebtorEntity $debtor
     * @param int $authId
     * @return OwnershipContext
     */
    protected function createDebtorOwnerShipContext(DebtorEntity $debtor, int $authId): OwnershipContext
    {
        return new OwnershipContext(
            $authId,
            $authId,
            $debtor->email,
            $debtor->id,
            $debtor->id,
            DebtorIdentity::class
        );
    }

    /**
     * @internal
     * @param int $id
     * @param string $type
     * @return int
     */
    protected function getAuthData(int $id, string $type): int
    {
        /** @var StoreFrontAuthenticationRepository $authService */
        $authService = $this->authenticationRepository;

        return $authService->fetchIdByProviderData($type, $id);
    }

    /**
     * @param int $salesRepresentativeId
     * @param int[] $clientIds
     */
    public function saveClients(int $salesRepresentativeId, array $clientIds)
    {
        $this->clientRepository->deleteClientsBySalesRepresentativeId($salesRepresentativeId);
        $this->clientRepository->addClientsToSalesRepresentative($clientIds, $salesRepresentativeId);
    }
}
