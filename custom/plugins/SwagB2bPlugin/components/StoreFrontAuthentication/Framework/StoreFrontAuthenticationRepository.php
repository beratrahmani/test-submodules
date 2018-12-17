<?php declare(strict_types = 1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Repository\NotFoundException;

class StoreFrontAuthenticationRepository
{
    const TABLE_NAME = 'b2b_store_front_auth';

    const TABLE_ALIAS = 'auth';

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
     * @param string $providerKey
     * @param int $providerContext
     * @return int
     */
    public function fetchIdByProviderData(string $providerKey, int $providerContext): int
    {
        return (int) $this->connection
            ->fetchColumn('SELECT id FROM ' . self::TABLE_NAME . ' WHERE provider_key = :providerKey AND provider_context = :providerContext', [
                'providerKey' => $providerKey,
                'providerContext' => $providerContext,
        ]);
    }

    /**
     * @param int $authId
     * @return StoreFrontAuthenticationEntity
     */
    public function fetchAuthenticationById(int $authId): StoreFrontAuthenticationEntity
    {
        $data = $this->connection->fetchAssoc('SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = :id', ['id' => $authId]);

        if (!$data) {
            throw new NotFoundException('Could not find a authentication with id "' . $authId . '"');
        }

        $entity = new StoreFrontAuthenticationEntity();
        $entity->fromDatabaseArray($data);

        return $entity;
    }

    /**
     * @param string $providerKey
     * @param int $providerContext
     * @param int|null $contextOwnerId
     * @return int
     */
    public function createAuthContextEntry(string $providerKey, int $providerContext, int $contextOwnerId = null)
    {
        $insertData = [
            'provider_key' => $providerKey,
            'provider_context' => $providerContext,
        ];

        if ($contextOwnerId) {
            $insertData['context_owner_id'] = $contextOwnerId;
        }

        $this->connection->insert(
            self::TABLE_NAME,
            $insertData
        );

        $authId =  (int) $this->connection->lastInsertId();

        if (!$contextOwnerId) {
            $this->connection->update(
                self::TABLE_NAME,
                ['context_owner_id' => $authId],
                ['id' => $authId]
            );
        }

        return $authId;
    }

    /**
     * @param int $authId
     * @param int $mediaId
     */
    public function syncAvatarImage(int $authId, int $mediaId)
    {
        $this->connection->createQueryBuilder()
            ->update(self::TABLE_NAME, self::TABLE_ALIAS)
            ->set('media_id', ':mediaId')
            ->where('id = :id')
            ->setParameters([
                'id' => $authId,
                'mediaId' => $mediaId,
            ])
            ->execute();
    }

    /**
     * @param int $authId
     * @return string
     */
    public function fetchAvatarById(int $authId): string
    {
        $path = $this->connection->createQueryBuilder()
            ->select('media.path')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(self::TABLE_ALIAS, 's_media', 'media', 'media.id = media_id')
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->setParameter('id', $authId)
            ->execute()
            ->fetchColumn();

        return $path ?: '';
    }
}
