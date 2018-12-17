<?php declare(strict_types=1);

namespace Shopware\B2B\Debtor\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;

class DebtorRepository
{
    const TABLE_NAME = 's_user';

    const TABLE_ALIAS = 'user';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $email
     * @throws \Shopware\B2B\Common\Repository\NotFoundException
     * @return DebtorEntity
     */
    public function fetchOneByEmail(string $email): DebtorEntity
    {
        $statement = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(self::TABLE_ALIAS, 's_user_attributes', 'attributes', 'attributes.userID = ' . self::TABLE_ALIAS . '.id')
            ->where(self::TABLE_ALIAS . '.email = :email')
            ->andWhere('attributes.b2b_is_debtor = 1')
            ->andWhere(self::TABLE_ALIAS . '.accountmode = 0')
            ->setParameter('email', $email)
            ->execute();

        $user = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            throw new NotFoundException(sprintf('Debtor not found for %s', $email));
        }

        return (new DebtorEntity())->fromDatabaseArray($user);
    }

    /**
     * @param int $id
     * @throws \Shopware\B2B\Common\Repository\NotFoundException
     * @return DebtorEntity
     */
    public function fetchOneById(int $id): DebtorEntity
    {
        $statement = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(self::TABLE_ALIAS, 's_user_attributes', 'attributes', 'attributes.userID = ' . self::TABLE_ALIAS . '.id')
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->andWhere('attributes.b2b_is_debtor = 1')
            ->andWhere(self::TABLE_ALIAS . '.accountmode = 0')
            ->setParameter('id', $id)
            ->execute();

        $user = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            throw new NotFoundException(sprintf('Debtor not found for %s', $id));
        }

        return (new DebtorEntity())->fromDatabaseArray($user);
    }

    /**
     * @return DebtorEntity[]
     */
    public function fetchAllDebtors(): array
    {
        $statement = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(self::TABLE_ALIAS, 's_user_attributes', 'attributes', 'attributes.userID = ' . self::TABLE_ALIAS . '.id')
            ->where('attributes.b2b_is_debtor = 1')
            ->andWhere(self::TABLE_ALIAS . '.accountmode = 0')
            ->execute();

        $users = $statement->fetchAll();

        $entities = [];
        foreach ($users as $user) {
            $entities[] = (new DebtorEntity())->fromDatabaseArray($user);
        }

        if (count($entities) === 0) {
            throw new NotFoundException('No Debtors found');
        }

        return $entities;
    }

    /**
     * @param int $id
     * @param LoginContextService $contextService
     * @throws NotFoundException
     * @return Identity
     */
    public function fetchIdentityById(int $id, LoginContextService $contextService): Identity
    {
        $entity = $this->fetchOneById($id);

        $authId = $contextService->getAuthId(__CLASS__, $entity->id);

        return new DebtorIdentity(
            $authId,
            (int) $entity->id,
            self::TABLE_NAME,
            $entity,
            $contextService->getAvatar($authId)
        );
    }

    /**
     * @param int $userId
     */
    public function setUserAsDebtor(int $userId)
    {
        $this->connection->update(
            's_user_attributes',
            ['b2b_is_debtor' => 1],
            ['userID' => $userId]
        );
    }
}
