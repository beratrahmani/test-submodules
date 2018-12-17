<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Acl\Framework\AclReadHelper;
use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Company\Framework\CompanyFilterHelper;
use Shopware\B2B\Debtor\Framework\DebtorRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\StoreFrontAuthenticationRepository;

class ContactRepository implements GridRepository
{
    const TABLE_NAME = 'b2b_debtor_contact';
    const TABLE_ALIAS = 'contact';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DebtorRepository
     */
    private $debtorRepository;

    /**
     * @var DbalHelper
     */
    private $dbalHelper;

    /**
     * @var StoreFrontAuthenticationRepository
     */
    private $authenticationRepository;

    /**
     * @var AclReadHelper
     */
    private $aclReadHelper;

    /**
     * @var CompanyFilterHelper
     */
    private $companyFilterHelper;

    /**
     * @var ContactCompanyAssignmentFilter
     */
    private $contactCompanyAssignmentFilter;

    /**
     * @var ContactCompanyInheritanceFilter
     */
    private $contactCompanyInheritanceFilter;

    /**
     * @param Connection $connection
     * @param DbalHelper $dbalHelper
     * @param DebtorRepository $debtorRepository
     * @param StoreFrontAuthenticationRepository $authenticationRepository
     * @param AclReadHelper $aclReadHelper
     * @param ContactCompanyAssignmentFilter $contactCompanyAssignmentFilter
     * @param CompanyFilterHelper $companyFilterHelper
     * @param ContactCompanyInheritanceFilter $contactCompanyInheritanceFilter
     */
    public function __construct(
        Connection $connection,
        DbalHelper $dbalHelper,
        DebtorRepository $debtorRepository,
        StoreFrontAuthenticationRepository $authenticationRepository,
        AclReadHelper $aclReadHelper,
        ContactCompanyAssignmentFilter $contactCompanyAssignmentFilter,
        CompanyFilterHelper $companyFilterHelper,
        ContactCompanyInheritanceFilter $contactCompanyInheritanceFilter
    ) {
        $this->connection = $connection;
        $this->debtorRepository = $debtorRepository;
        $this->dbalHelper = $dbalHelper;
        $this->authenticationRepository = $authenticationRepository;
        $this->aclReadHelper = $aclReadHelper;
        $this->companyFilterHelper = $companyFilterHelper;
        $this->contactCompanyAssignmentFilter = $contactCompanyAssignmentFilter;
        $this->contactCompanyInheritanceFilter = $contactCompanyInheritanceFilter;
    }

    /**
     * @param string $email
     * @param OwnershipContext $ownershipContext
     * @return ContactEntity
     */
    public function fetchOneByEmail(string $email, OwnershipContext $ownershipContext): ContactEntity
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.email = :email')
            ->setParameter('email', $email);

        $this->filterByContextOwner($ownershipContext, $query);

        $contactData = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        return $this->createContactByContactData($contactData, $email);
    }

    /**
     * @param string $email
     * @return ContactEntity
     */
    public function insecureFetchOneByEmail(string $email): ContactEntity
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.email = :email')
            ->setParameter('email', $email);

        $contactData = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        return $this->createContactByContactData($contactData, $email);
    }

    /**
     * @param string $email
     * @return bool
     */
    public function hasContactForEmail(string $email): bool
    {
        $statement = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.email = :email')
            ->setParameter('email', $email)
            ->execute();

        return (bool) $statement->fetchColumn();
    }

    /**
     * @param int $id
     * @param OwnershipContext $ownershipContext
     * @throws \Shopware\B2B\Common\Repository\NotFoundException
     * @return ContactEntity
     */
    public function fetchOneById(int $id, OwnershipContext $ownershipContext): ContactEntity
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->setParameter('id', $id);

        $this->filterByContextOwner($ownershipContext, $query);

        $contactData = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        return $this->createContactByContactData($contactData, (string) $id);
    }

    /**
     * @param int $id
     * @throws \Shopware\B2B\Common\Repository\NotFoundException
     * @return ContactEntity
     */
    public function insecureFetchOneById(int $id): ContactEntity
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->setParameter('id', $id);

        $contactData = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        return $this->createContactByContactData($contactData, (string) $id);
    }

    /**
     * @internal
     * @param $contactData
     * @param string $identifier
     * @throws \Shopware\B2B\Common\Repository\NotFoundException
     * @return ContactEntity
     */
    protected function createContactByContactData($contactData, string $identifier): ContactEntity
    {
        if (!$contactData) {
            throw new NotFoundException(sprintf('Contact not found for %s', (string) $identifier));
        }

        $contact = new ContactEntity();
        $contact->fromDatabaseArray($contactData);

        $authentication = $this->authenticationRepository
            ->fetchAuthenticationById($contact->contextOwnerId);

        $contact->debtor = $this->debtorRepository->fetchOneById($authentication->providerContext);

        return $contact;
    }

    /**
     * @param ContactEntity $contact
     * @param OwnershipContext $ownershipContext
     * @throws \Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException
     * @return ContactEntity
     */
    public function addContact(ContactEntity $contact, OwnershipContext $ownershipContext): ContactEntity
    {
        if (!$contact->isNew()) {
            throw new CanNotInsertExistingRecordException('The contact provided already exists');
        }

        $this->connection->insert(
            self::TABLE_NAME,
            array_merge(
                $contact->toDatabaseArray(),
                ['context_owner_id' => $ownershipContext->contextOwnerId]
            )
        );

        $contact->id = (int) $this->connection->lastInsertId();

        return $contact;
    }

    /**
     * @param ContactEntity $contact
     * @param OwnershipContext $ownershipContext
     * @throws \Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException
     * @return ContactEntity
     */
    public function updateContact(ContactEntity $contact, OwnershipContext $ownershipContext): ContactEntity
    {
        if ($contact->isNew()) {
            throw new CanNotUpdateExistingRecordException('The contact provided does not exist');
        }

        $this->connection->update(
            self::TABLE_NAME,
            $contact->toDatabaseArray(),
            [
                'id' => $contact->id,
                'context_owner_id' => $ownershipContext->contextOwnerId,
            ]
        );

        return $contact;
    }

    /**
     * @param ContactEntity $contact
     * @param OwnershipContext $ownershipContext
     * @throws \Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException
     * @return ContactEntity
     */
    public function removeContact(ContactEntity $contact, OwnershipContext $ownershipContext): ContactEntity
    {
        if ($contact->isNew()) {
            throw new CanNotRemoveExistingRecordException('The contact provided does not exist');
        }

        $this->connection->delete(
            self::TABLE_NAME,
            [
                'id' => $contact->id,
                'context_owner_id' => $ownershipContext->contextOwnerId,
            ]
        );

        $contact->id = null;

        return $contact;
    }

    /**
     * @param ContactEntity $contact
     * @param OwnershipContext $ownershipContext
     */
    public function updateDefaultAddresses(ContactEntity $contact, OwnershipContext $ownershipContext)
    {
        $addresses = [];

        if ($contact->defaultBillingAddressId) {
            $addresses['default_billing_address_id'] = $contact->defaultBillingAddressId;
        }

        if ($contact->defaultShippingAddressId) {
            $addresses['default_shipping_address_id'] = $contact->defaultShippingAddressId;
        }

        if (!$addresses) {
            throw new \InvalidArgumentException('no addresses given');
        }

        $this->connection->update(
            self::TABLE_NAME,
            $addresses,
            [
                'id' => $contact->id,
                'context_owner_id' => $ownershipContext->contextOwnerId,
            ]
        );
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param ContactSearchStruct $searchStruct
     * @return ContactEntity[]
     */
    public function fetchList(OwnershipContext $ownershipContext, ContactSearchStruct $searchStruct): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS);

        $this->filterByContextOwner($ownershipContext, $query);

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper->applySearchStruct($searchStruct, $query);
        $this->aclReadHelper->applyAclVisibility($ownershipContext, $query);

        $this->companyFilterHelper
            ->applyFilter(
                $this->aclReadHelper,
                $this->contactCompanyAssignmentFilter,
                $searchStruct,
                $query,
                $this->contactCompanyInheritanceFilter
            );

        $statement = $query->execute();
        $contactsData = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $contacts = [];
        foreach ($contactsData as $contactData) {
            $contacts[] = (new ContactEntity())->fromDatabaseArray($contactData);
        }

        return $contacts;
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @return ContactEntity[]
     */
    public function fetchFullList(OwnershipContext $ownershipContext): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->orderBy(self::TABLE_ALIAS . '.lastname', 'ASC');

        $this->filterByContextOwner($ownershipContext, $query);

        $this->aclReadHelper->applyAclVisibility($ownershipContext, $query);

        $statement = $query->execute();
        $contactsData = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $contacts = [];
        foreach ($contactsData as $contactData) {
            $contacts[] = (new ContactEntity())->fromDatabaseArray($contactData);
        }

        return $contacts;
    }

    /**
     * @param OwnershipContext $context
     * @param ContactSearchStruct $contactSearchStruct
     * @return int
     */
    public function fetchTotalCount(OwnershipContext $context, ContactSearchStruct $contactSearchStruct): int
    {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS);

        $this->filterByContextOwner($context, $query);

        $this->aclReadHelper->applyAclVisibility($context, $query);

        $this->companyFilterHelper
            ->applyFilter(
                $this->aclReadHelper,
                $this->contactCompanyAssignmentFilter,
                $contactSearchStruct,
                $query,
                $this->contactCompanyInheritanceFilter
            );

        $this->dbalHelper->applyFilters($contactSearchStruct, $query);

        $statement = $query->execute();

        return (int) $statement->fetchColumn(0);
    }

    /**
     * @internal
     * @param OwnershipContext $context
     * @param QueryBuilder $query
     */
    protected function filterByContextOwner(OwnershipContext $context, QueryBuilder $query)
    {
        $query->andWhere(self::TABLE_ALIAS . '.context_owner_id = :contextOwnerId')
            ->setParameter('contextOwnerId', $context->contextOwnerId);
    }

    /**
     * @param int $contactId
     * @param int $authId
     * @param OwnershipContext $ownershipContext
     */
    public function setAuthId(int $contactId, int $authId, OwnershipContext $ownershipContext)
    {
        $this->connection->update(
            self::TABLE_NAME,
            ['auth_id' => $authId],
            [
                'id' => $contactId,
                'context_owner_id' => $ownershipContext->contextOwnerId,
            ]
        );
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
        return [
            'email',
            'title',
            'salutation',
            'firstname',
            'lastname',
        ];
    }

    /**
     * @return array
     */
    public function getAdditionalSearchResourceAndFields(): array
    {
        return [];
    }
}
