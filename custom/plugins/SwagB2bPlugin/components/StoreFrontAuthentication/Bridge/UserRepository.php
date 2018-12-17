<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Bridge;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    const TABLE_NAME = 's_user';

    const TABLE_NAME_ATTRIBUTES = 's_user_attributes';

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
     * @param array $userData
     * @return int
     */
    public function syncUser(array $userData): int
    {
        $check = $this->connection
            ->fetchColumn(
                'SELECT id FROM ' . self::TABLE_NAME . ' WHERE email LIKE :email LIMIT 1',
                ['email' => $userData['email']]
            );

        if (!$check) {
            $userData['firstlogin'] = date('Y-m-d');

            $this->connection->insert(
                self::TABLE_NAME,
                array_merge(
                    $userData,
                    ['email' => $userData['email']]
                )
            );

            return (int) $this->connection->lastInsertId();
        }

        $this->connection
            ->update(
                self::TABLE_NAME,
                $userData,
                ['email' => $userData['email']]
            );

        return (int) $check;
    }

    /**
     * @param int $id
     * @return array
     */
    public function fetchOneById(int $id): array
    {
        $statement = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, 'user')
            ->where('user.id = :id')
            ->setParameter('id', $id)
            ->execute();

        $user = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            throw new NotFoundException(sprintf('User not found for %s', $id));
        }

        return $user;
    }

    /**
     * @param int $addressId
     * @param string $type
     * @param OwnershipContext $context
     */
    public function checkAddress(int $addressId, string $type, OwnershipContext $context)
    {
        $addressIdent = $this->getAddressIdentification($addressId);

        if ($addressIdent['b2b_type'] === $type && (int) $addressIdent['user_id'] === $context->shopOwnerUserId) {
            return;
        }

        if (!$addressIdent['b2b_type']) {
            $this->updateAttributes($addressId, $type);

            return;
        }

        if ($addressIdent['b2b_type'] !== $type) {
            $newAddress = $this->findAddress($context->shopOwnerUserId, $type);

            if (!$newAddress) {
                $newAddress['address_id'] = $this->duplicateAddress(
                    $this->getAddress($addressId),
                    $type
                );
            }

            $this->connection->update(
                's_user',
                ['default_' . $type . '_address_id' => $newAddress['address_id']],
                ['id' => $context->shopOwnerUserId]
            );
        }
    }

    /**
     * @internal
     * @param int $id
     * @return array
     */
    protected function getAddressIdentification(int $id): array
    {
        return $this->getBaseQuery()
            ->select('address.user_id, attributes.b2b_type')
            ->where('address.id = :id')
            ->setParameter('id', $id)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @internal
     * @param int $id
     * @return array
     */
    protected function getAddress(int $id): array
    {
        return $this->getBaseQuery()
            ->select('address.*')
            ->where('address.id = :id')
            ->setParameter('id', $id)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @internal
     * @param array $address
     * @param string $type
     * @return int
     */
    protected function duplicateAddress(array $address, string $type): int
    {
        unset($address['id']);
        $this->connection->insert(
            's_user_addresses',
            $address
        );

        $id = (int) $this->connection->lastInsertId();

        $this->updateAttributes($id, $type);

        return $id;
    }

    /**
     * @internal
     * @param int $userId
     * @param string $type
     * @return array|bool
     */
    protected function findAddress(int $userId, string $type)
    {
        return $this->getBaseQuery()
            ->where('address.user_id = :userId')
            ->andWhere('attributes.b2b_type = :type')
            ->setParameter('userId', $userId)
            ->setParameter('type', $type)
            ->orderBy('address.id', 'ASC')
            ->setMaxResults(1)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @internal
     * @return QueryBuilder
     */
    protected function getBaseQuery(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->select('*')
            ->from('s_user_addresses', 'address')
            ->leftJoin(
                'address',
                's_user_addresses_attributes',
                'attributes',
                'address.id = attributes.address_id'
            );
    }

    /**
     * @internal
     * @param int $addressId
     * @param string $type
     */
    protected function updateAttributes(int $addressId, string $type)
    {
        try {
            $this->connection->insert(
                's_user_addresses_attributes',
                [
                    'address_id' => $addressId,
                    'b2b_type' => $type,
                ]
            );
        } catch (DBALException $e) {
            $this->connection->update(
                's_user_addresses_attributes',
                ['b2b_type' => $type],
                ['address_id' => $addressId]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isMailAvailable(string $mail): bool
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME)
            ->where('email = :email')
            ->setParameter('email', $mail);

        return !(bool) $query->execute()->fetchAll();
    }

    /**
     * {@inheritdoc}
     */
    public function updateEmail(string $originalMail, string $newMail)
    {
        $this->connection->update(
            self::TABLE_NAME,
            ['email' => $newMail],
            ['email' => $originalMail]
        );
    }
}
