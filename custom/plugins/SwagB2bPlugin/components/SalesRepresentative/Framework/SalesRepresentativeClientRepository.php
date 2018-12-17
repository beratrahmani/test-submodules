<?php declare(strict_types=1);

namespace Shopware\B2B\SalesRepresentative\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Address\Framework\AddressRepositoryInterface;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationIdentityLoaderInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\StoreFrontAuthenticationRepository;

class SalesRepresentativeClientRepository
{
    const TABLE_NAME = 'b2b_sales_representative_clients';

    const TABLE_ALIAS = 'clients';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DbalHelper
     */
    private $dbalHelper;

    /**
     * @var AuthenticationIdentityLoaderInterface
     */
    private $authenticationIdentityLoader;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @var StoreFrontAuthenticationRepository
     */
    private $authRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param Connection $connection
     * @param AuthenticationIdentityLoaderInterface $authenticationIdentityLoader
     * @param DbalHelper $dbalHelper
     * @param LoginContextService $loginContextService
     * @param StoreFrontAuthenticationRepository $authRepository
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        Connection $connection,
        AuthenticationIdentityLoaderInterface $authenticationIdentityLoader,
        DbalHelper $dbalHelper,
        LoginContextService $loginContextService,
        StoreFrontAuthenticationRepository $authRepository,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->connection = $connection;
        $this->dbalHelper = $dbalHelper;
        $this->authenticationIdentityLoader = $authenticationIdentityLoader;
        $this->loginContextService = $loginContextService;
        $this->authRepository = $authRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @param SalesRepresentativeEntity $salesRepresentativeEntity
     * @return SalesRepresentativeEntity
     */
    public function fetchClients(SalesRepresentativeEntity $salesRepresentativeEntity): SalesRepresentativeEntity
    {
        $clientIds = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.client_id')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.sales_representative_id = :id')
            ->setParameter('id', $salesRepresentativeEntity->id)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        $salesRepresentativeEntity->clients = $this->fetchClientsByAuthIds($clientIds);

        return $salesRepresentativeEntity;
    }

    /**
     * @internal
     * @param int[] $clientIds
     * @return SalesRepresentativeClientEntity[]
     */
    protected function fetchClientsByAuthIds(array $clientIds): array
    {
        $clients = [];
        foreach ($clientIds as $authId) {
            try {
                $auth = $this->authRepository->fetchAuthenticationById((int) $authId);

                $identity = $this->authenticationIdentityLoader
                    ->fetchIdentityByAuthentication($auth, $this->loginContextService);

                $client = new SalesRepresentativeClientEntity(
                    $identity,
                    $this->addressRepository->fetchOneById($identity->getMainShippingAddress()->id, $identity)
                );
                $clients[] = $client;
            } catch (NotFoundException $e) {
                continue;
            }
        }

        return $clients;
    }

    /**
     * @param SalesRepresentativeSearchStruct $searchStruct
     * @param SalesRepresentativeEntity $salesRepresentativeEntity
     * @return SalesRepresentativeClientEntity[]
     */
    public function fetchClientsList(SalesRepresentativeSearchStruct $searchStruct, SalesRepresentativeEntity $salesRepresentativeEntity): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.client_id')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(
                self::TABLE_ALIAS,
                StoreFrontAuthenticationRepository::TABLE_NAME,
                StoreFrontAuthenticationRepository::TABLE_ALIAS,
                StoreFrontAuthenticationRepository::TABLE_ALIAS . '.id = ' . self::TABLE_ALIAS . '.client_id'
            )
            ->where(self::TABLE_ALIAS . '.sales_representative_id = :id')
            ->setParameter('id', $salesRepresentativeEntity->id);

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.client_id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->authenticationIdentityLoader->addSubSelect($query);

        $this->dbalHelper->applySearchStruct($searchStruct, $query);

        $clientIds = $query->execute()->fetchAll(\PDO::FETCH_COLUMN);

        return $this->fetchClientsByAuthIds($clientIds);
    }

    /**
     * @param SalesRepresentativeEntity $entity
     * @param SalesRepresentativeSearchStruct $searchStruct
     * @return int
     */
    public function fetchTotalCount(SalesRepresentativeEntity $entity, SalesRepresentativeSearchStruct $searchStruct): int
    {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(
                self::TABLE_ALIAS,
                StoreFrontAuthenticationRepository::TABLE_NAME,
                StoreFrontAuthenticationRepository::TABLE_ALIAS,
                StoreFrontAuthenticationRepository::TABLE_ALIAS . '.id = ' . self::TABLE_ALIAS . '.client_id'
            )
            ->where('clients.sales_representative_id = :id')
            ->setParameters(['id' => $entity->id]);

        $this->authenticationIdentityLoader->addSubSelect($query);

        $this->dbalHelper->applyFilters($searchStruct, $query);

        $statement = $query->execute();

        return (int) $statement->fetchColumn(0);
    }

    /**
     * @param int $salesRepresentativeId
     */
    public function deleteClientsBySalesRepresentativeId(int $salesRepresentativeId)
    {
        $this->connection->delete(self::TABLE_NAME, ['sales_representative_id' => $salesRepresentativeId]);
    }

    /**
     * @param int[] $clientIds
     * @param int $salesRepresentativeId
     */
    public function addClientsToSalesRepresentative(array $clientIds, int $salesRepresentativeId)
    {
        foreach ($clientIds as $clientId) {
            $this->connection->insert(
                self::TABLE_NAME,
                ['client_id' => $clientId, 'sales_representative_id' => $salesRepresentativeId]
            );
        }
    }
}
