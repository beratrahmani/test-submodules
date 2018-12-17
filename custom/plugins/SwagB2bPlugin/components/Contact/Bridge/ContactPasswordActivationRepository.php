<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Bridge;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Contact\Framework\ContactPasswordActivationEntity;
use Shopware\B2B\Contact\Framework\ContactPasswordActivationRepositoryInterface;

class ContactPasswordActivationRepository implements ContactPasswordActivationRepositoryInterface
{
    const TABLE_NAME = 's_core_optin';

    const TABLE_ALIAS = 'core_optin';

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
     * {@inheritdoc}
     */
    public function addContactActivation(ContactPasswordActivationEntity $activation): ContactPasswordActivationEntity
    {
        if (!$activation->isNew()) {
            throw new CanNotInsertExistingRecordException('The contact activation provided already exists');
        }

        $this->connection->insert(
            self::TABLE_NAME,
            $activation->toDatabaseArray()
        );

        $activation->id = (int) $this->connection->lastInsertId();

        return $activation;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchOneByHash(string $hash): ContactPasswordActivationEntity
    {
        $statement = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.hash = :hash')
            ->setParameter('hash', $hash)
            ->execute();

        $activationData = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$activationData) {
            throw new NotFoundException(sprintf('Activation not found for %s', $hash));
        }

        $activationEntity = new ContactPasswordActivationEntity();

        return $activationEntity->fromDatabaseArray($activationData);
    }

    /**
     * {@inheritdoc}
     */
    public function removeActivation(ContactPasswordActivationEntity $activation): ContactPasswordActivationEntity
    {
        if ($activation->isNew()) {
            throw new CanNotRemoveExistingRecordException('The activation provided does not exist');
        }

        $this->connection->delete(
            self::TABLE_NAME,
            ['hash' => $activation->hash]
        );

        $activation->id = null;

        return $activation;
    }
}
