<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroupContact\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Controller\GridRepository;

/**
 * DB-Representation of contingent:contact assignment
 */
class ContingentContactRepository implements GridRepository
{
    const TABLE_NAME = 'b2b_contact_contingent_group';

    const TABLE_ALIAS = 'contact_contingent_groups';

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
     * @return string
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
        return [
            'name',
            'description',
        ];
    }

    /**
     * @param int $contingentGroupId
     * @param int $contactId
     */
    public function removeContingentContactAssignment(int $contingentGroupId, int $contactId)
    {
        $this->connection->delete(
            self::TABLE_NAME,
            [
                'contingent_group_id' => $contingentGroupId,
                'contact_id' => $contactId,
            ]
        );
    }

    /**
     * @param int $contingentGroupId
     * @param int $contactId
     */
    public function assignContingentContact(int $contingentGroupId, int $contactId)
    {
        $data = [
            'contingent_group_id' => $contingentGroupId,
            'contact_id' => $contactId,
        ];

        $this->connection->insert(
            self::TABLE_NAME,
            $data
        );
    }

    /**
     * @param int $contactId
     * @return array
     */
    public function getActiveContingentsByContactId(int $contactId): array
    {
        $contingentGroups = $this->connection->fetchAll(
            'SELECT contingent_group_id FROM ' . self::TABLE_NAME . '
             WHERE contact_id = :contactId
            ',
            [
                ':contactId' => $contactId,
            ]
        );

        return (array) $contingentGroups;
    }

    /**
     * @param int $contingentGroupId
     * @param int $contactId
     * @return bool
     */
    public function isContingentGroupContactDebtor(int $contingentGroupId, int $contactId): bool
    {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where('contact_id = :contactId')
            ->andWhere('contingent_group_id = :groupId')

            ->setParameter('contactId', $contactId)
            ->setParameter('groupId', $contingentGroupId)
            ->execute();

        return (bool) $query->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * @return array
     */
    public function getAdditionalSearchResourceAndFields(): array
    {
        return [];
    }
}
