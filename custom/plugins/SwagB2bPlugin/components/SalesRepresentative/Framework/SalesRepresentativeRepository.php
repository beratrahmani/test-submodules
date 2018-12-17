<?php declare(strict_types=1);

namespace Shopware\B2B\SalesRepresentative\Framework;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Common\Repository\NotFoundException;

class SalesRepresentativeRepository implements GridRepository
{
    const TABLE_NAME = 's_user';

    const TABLE_ALIAS = 'user';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var SalesRepresentativeClientRepository
     */
    private $clientRepository;

    /**
     * @param Connection $connection
     * @param SalesRepresentativeClientRepository $clientRepository
     */
    public function __construct(Connection $connection, SalesRepresentativeClientRepository $clientRepository)
    {
        $this->connection = $connection;
        $this->clientRepository = $clientRepository;
    }

    /**
     * @return array
     */
    public function getAdditionalSearchResourceAndFields(): array
    {
        return [
            '%2$s' => [
                'contact' => [
                    'phone',
                    'email',
                    'firstname',
                    'lastname',
                ],
                'debtor' => [
                    'phone',
                    'email',
                    'firstname',
                    'lastname',
                ],
            ],
        ];
    }

    /**
     * @return string query alias for filter construction
     */
    public function getMainTableAlias(): string
    {
        return self::TABLE_ALIAS;
    }

    /**
     * @return string[]
     */
    public function getFullTextSearchFields(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function fetchOneByEmail(string $email): SalesRepresentativeEntity
    {
        $statement = $this->getBaseQuery();

        $data = $statement->andWhere(self::TABLE_ALIAS . '.email = :email')
            ->setParameter('email', $email)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            throw new NotFoundException(sprintf('Sales Representative not found for %s', $email));
        }

        return $this->createEntity($data);
    }

    /**
     * @param int $id
     * @throws \Shopware\B2B\Common\Repository\NotFoundException
     * @return SalesRepresentativeEntity
     */
    public function fetchOneById(int $id): SalesRepresentativeEntity
    {
        $statement = $this->getBaseQuery();

        $data = $statement->andWhere(self::TABLE_ALIAS . '.id = :id')
            ->setParameter('id', $id)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            throw new NotFoundException(sprintf('Sales Representative not found for %s', $id));
        }

        return $this->createEntity($data);
    }

    /**
     * @internal
     * @param array $data
     * @return SalesRepresentativeEntity
     */
    protected function createEntity(array $data): SalesRepresentativeEntity
    {
        $entity = new SalesRepresentativeEntity();
        $entity->fromDatabaseArray($data);

        $this->clientRepository->fetchClients($entity);

        return $entity;
    }

    /**
     * @internal
     * @return QueryBuilder
     */
    protected function getBaseQuery(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(
                self::TABLE_ALIAS,
                self::TABLE_NAME . '_attributes',
                'attributes',
                'attributes.userID = ' . self::TABLE_ALIAS . '.id'
            )
            ->where('attributes.b2b_is_sales_representative = 1');
    }
}
