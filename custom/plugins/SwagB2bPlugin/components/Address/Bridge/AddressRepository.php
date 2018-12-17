<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Bridge;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Acl\Framework\AclReadHelper;
use Shopware\B2B\Address\Framework\AddressCompanyAssignmentFilter;
use Shopware\B2B\Address\Framework\AddressCompanyInheritanceFilter;
use Shopware\B2B\Address\Framework\AddressEntity;
use Shopware\B2B\Address\Framework\AddressRepositoryInterface;
use Shopware\B2B\Address\Framework\AddressSearchStruct;
use Shopware\B2B\Address\Framework\CountryRepositoryInterface;
use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotRemoveUsedRecordException;
use Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Company\Framework\CompanyFilterHelper;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

/**
 * ACL enabled Address access and CRUD
 */
class AddressRepository implements AddressRepositoryInterface
{
    const TABLE_ATTRIBUTES_NAME = 's_user_addresses_attributes';
    const TABLE_ATTRIBUTES_ALIAS = 'attributes';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DbalHelper
     */
    private $dbalHelper;

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepositoryInterface;

    /**
     * @var AclReadHelper
     */
    private $aclReadHelper;
    
    /**
     * @var CompanyFilterHelper
     */
    private $companyFilterHelper;

    /**
     * @var AddressCompanyAssignmentFilter
     */
    private $addressCompanyAssignmentFilter;

    /**
     * @var AddressCompanyInheritanceFilter
     */
    private $addressCompanyInheritanceFilter;

    /**
     * @param Connection $connection
     * @param DbalHelper $dbalHelper
     * @param CountryRepositoryInterface $countryRepositoryInterface
     * @param AclReadHelper $aclReadHelper
     * @param CompanyFilterHelper $companyFilterHelper
     * @param AddressCompanyAssignmentFilter $addressCompanyAssignmentFilter
     * @param AddressCompanyInheritanceFilter $addressCompanyInheritanceFilter
     */
    public function __construct(
        Connection $connection,
        DbalHelper $dbalHelper,
        CountryRepositoryInterface $countryRepositoryInterface,
        AclReadHelper $aclReadHelper,
        CompanyFilterHelper $companyFilterHelper,
        AddressCompanyAssignmentFilter $addressCompanyAssignmentFilter,
        AddressCompanyInheritanceFilter $addressCompanyInheritanceFilter
    ) {
        $this->connection = $connection;
        $this->dbalHelper = $dbalHelper;
        $this->countryRepositoryInterface = $countryRepositoryInterface;
        $this->aclReadHelper = $aclReadHelper;
        $this->companyFilterHelper = $companyFilterHelper;
        $this->addressCompanyAssignmentFilter = $addressCompanyAssignmentFilter;
        $this->addressCompanyInheritanceFilter = $addressCompanyInheritanceFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryList(): array
    {
        return $this->countryRepositoryInterface->getCountryList();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchList(
        string $type,
        OwnershipContext $ownershipContext,
        AddressSearchStruct $searchStruct
    ): array {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->addSelect(
                '(SELECT COUNT(*) FROM s_user 
                    WHERE (
                        default_billing_address_id = ' . self::TABLE_ALIAS . '.id 
                        OR default_shipping_address_id = ' . self::TABLE_ALIAS . '.id
                    )
                ) as is_used'
            )
            ->addSelect(self::TABLE_ATTRIBUTES_ALIAS . '.b2b_type as type')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(
                self::TABLE_ALIAS,
                self::TABLE_ATTRIBUTES_NAME,
                self::TABLE_ATTRIBUTES_ALIAS,
                self::TABLE_ALIAS . '.id = ' . self::TABLE_ATTRIBUTES_ALIAS . '.address_id'
            )
            ->where(self::TABLE_ATTRIBUTES_ALIAS . '.b2b_type = :type')
            ->andWhere(self::TABLE_ALIAS . '.user_id = :owner')
            ->setParameter('owner', $ownershipContext->shopOwnerUserId)
            ->setParameter('type', $type);

        $this->aclReadHelper->applyAclVisibility($ownershipContext, $query);

        $this->companyFilterHelper
            ->applyFilter(
                $this->aclReadHelper,
                $this->addressCompanyAssignmentFilter,
                $searchStruct,
                $query,
                $this->addressCompanyInheritanceFilter
            );

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper->applySearchStruct($searchStruct, $query);

        $statement = $query->execute();

        $addressesData = $statement
            ->fetchAll(\PDO::FETCH_ASSOC);

        $addresses = [];
        foreach ($addressesData as $addressData) {
            $addresses[] = (new AddressEntity())->fromDatabaseArray($addressData);
        }

        return $addresses;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchTotalCount(
        string $type,
        OwnershipContext $context,
        AddressSearchStruct $addressSearchStruct
    ): int {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(
                self::TABLE_ALIAS,
                self::TABLE_ATTRIBUTES_NAME,
                self::TABLE_ATTRIBUTES_ALIAS,
                self::TABLE_ALIAS . '.id = ' . self::TABLE_ATTRIBUTES_ALIAS . '.address_id'
            )
            ->where(self::TABLE_ATTRIBUTES_ALIAS . '.b2b_type = :type')
            ->andWhere(self::TABLE_ALIAS . '.user_id = :owner')
            ->setParameter('owner', $context->shopOwnerUserId)
            ->setParameter('type', $type);

        $this->aclReadHelper->applyAclVisibility($context, $query);

        $this->companyFilterHelper
            ->applyFilter(
                $this->aclReadHelper,
                $this->addressCompanyAssignmentFilter,
                $addressSearchStruct,
                $query,
                $this->addressCompanyInheritanceFilter
            );

        $this->dbalHelper->applyFilters($addressSearchStruct, $query);

        $statement = $query->execute();

        return (int) $statement->fetchColumn(0);
    }

    /**
     * {@inheritdoc}
     * @throws NotFoundException
     */
    public function fetchOneById(
        int $id,
        Identity $identity,
        string $addressType = null
    ): AddressEntity {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->addSelect(
                '(SELECT COUNT(*) FROM s_user 
                 WHERE default_billing_address_id = ' . self::TABLE_ALIAS . '.id 
                 OR default_shipping_address_id = ' . self::TABLE_ALIAS . '.id) as is_used'
            )
            ->addSelect(self::TABLE_ATTRIBUTES_ALIAS . '.b2b_type as type')
            ->leftJoin(
                self::TABLE_ALIAS,
                self::TABLE_ATTRIBUTES_NAME,
                self::TABLE_ATTRIBUTES_ALIAS,
                self::TABLE_ALIAS . '.id = ' . self::TABLE_ATTRIBUTES_ALIAS . '.address_id'
            )
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->andWhere(self::TABLE_ALIAS . '.user_id = :owner')
            ->setParameter('id', $id)
            ->setParameter('owner', $identity->getOwnershipContext()->shopOwnerUserId);

        if ($addressType) {
            $queryBuilder
                ->andWhere(self::TABLE_ATTRIBUTES_ALIAS . '.b2b_type = :addressType')
                ->setParameter('addressType', $addressType);
        }

        $this->aclReadHelper->applyAclVisibility($identity->getOwnershipContext(), $queryBuilder);

        $statement = $queryBuilder->execute();

        $addressData = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$addressData) {
            return $this->fetchOneByDefaultAddressId($id, $identity, $addressType);
        }

        $address = new AddressEntity();

        return $address->fromDatabaseArray($addressData);
    }

    /**
     * {@inheritdoc}
     * @throws \Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException
     */
    public function addAddress(AddressEntity $addressEntity, string $type, OwnershipContext $ownershipContext): AddressEntity
    {
        if (!$addressEntity->isNew()) {
            throw new CanNotInsertExistingRecordException('The address provided already exists');
        }

        $this->connection->insert(
            self::TABLE_NAME,
            array_merge(
                $addressEntity->toDatabaseArray(),
                ['user_id' => $ownershipContext->shopOwnerUserId]
            )
        );

        $addressEntity->id = (int) $this->connection->lastInsertId();

        $this->connection->insert(
            self::TABLE_ATTRIBUTES_NAME,
            [
                'address_id' => $addressEntity->id,
                'b2b_type' => $type,
            ]
        );

        $addressEntity->type = $type;

        return $addressEntity;
    }

    /**
     * {@inheritdoc}
     * @throws CanNotUpdateExistingRecordException
     * @throws NotFoundException
     */
    public function updateAddress(AddressEntity $addressEntity, OwnershipContext $ownershipContext, string $type): AddressEntity
    {
        if ($addressEntity->isNew()) {
            throw new CanNotUpdateExistingRecordException('The address provided does not exist');
        }

        $this->connection->update(
            self::TABLE_NAME,
            $addressEntity->toDatabaseArray(),
            [
                'id' => $addressEntity->id,
                'user_id' => $ownershipContext->shopOwnerUserId,
            ]
        );

        $this->connection->update(
            self::TABLE_ATTRIBUTES_NAME,
            ['b2b_type' => $type],
            ['address_id' => $addressEntity->id]
        );

        $addressEntity->type = $type;

        return $addressEntity;
    }

    /**
     * {@inheritdoc}
     * @throws \Shopware\B2B\Common\Repository\CanNotRemoveUsedRecordException
     * @throws \Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException
     */
    public function removeAddress(AddressEntity $addressEntity, OwnershipContext $ownershipContext): AddressEntity
    {
        if ($addressEntity->isNew()) {
            throw new CanNotRemoveExistingRecordException('The address provided does not exist');
        }

        if ($this->isAddressUsed($addressEntity, $ownershipContext)) {
            throw new CanNotRemoveUsedRecordException('The address provided is in use');
        }

        $this->connection->delete(
            self::TABLE_NAME,
            [
                'id' => $addressEntity->id,
                'user_id' => $ownershipContext->shopOwnerUserId,
            ]
        );

        $addressEntity->id = null;

        return $addressEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function getMainTableAlias(): string
    {
        return self::TABLE_ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullTextSearchFields(): array
    {
        return [
            'company',
            'department',
            'salutation',
            'title',
            'firstname',
            'lastname',
            'street',
            'zipcode',
            'city',
            'ustid',
            'phone',
            'additional_address_line1',
            'additional_address_line2',
        ];
    }

    /**
     * check if the given AddressEntity is in use
     *
     * @internal
     * @param AddressEntity $addressEntity
     * @param OwnershipContext $ownershipContext
     * @return bool
     */
    protected function isAddressUsed(AddressEntity $addressEntity, OwnershipContext $ownershipContext): bool
    {
        return (bool) $this->connection->fetchColumn(
            'SELECT COUNT(*) FROM s_user
             WHERE (default_billing_address_id = :id OR default_shipping_address_id = :id) AND id = :user_id',
            [
                ':id' => (int) $addressEntity->id,
                ':user_id' => $ownershipContext->shopOwnerUserId,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalSearchResourceAndFields(): array
    {
        return [];
    }

    /**
     * @internal
     * @param int $id
     * @param Identity $identity
     * @param null|string $addressType
     * @return AddressEntity
     */
    protected function fetchOneByDefaultAddressId(int $id, Identity $identity, string $addressType = null): AddressEntity
    {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->addSelect('1 as is_used')
            ->addSelect(self::TABLE_ATTRIBUTES_ALIAS . '.b2b_type as type')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->leftJoin(
                self::TABLE_ALIAS,
                self::TABLE_ATTRIBUTES_NAME,
                self::TABLE_ATTRIBUTES_ALIAS,
                self::TABLE_ALIAS . '.id = ' . self::TABLE_ATTRIBUTES_ALIAS . '.address_id'
            )
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->andWhere(self::TABLE_ALIAS . '.user_id = :owner')
            ->andWhere(self::TABLE_ALIAS . '.id in(:defaultIds)')
            ->setParameter('id', $id)
            ->setParameter('owner', $identity->getOwnershipContext()->shopOwnerUserId)
            ->setParameter(
                ':defaultIds',
                [
                    $identity->getMainBillingAddress()->id,
                    $identity->getMainShippingAddress()->id,
                ],
                Connection::PARAM_INT_ARRAY
            );

        if ($addressType) {
            $query
                ->andWhere(self::TABLE_ATTRIBUTES_ALIAS . '.b2b_type = :addressType')
                ->setParameter('addressType', $addressType);
        }

        $statement = $query->execute();

        $addressData = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$addressData) {
            throw new NotFoundException(sprintf('Unable to locate address with id "%s"', $id));
        }

        $address = new AddressEntity();

        return $address->fromDatabaseArray($addressData);
    }
}
