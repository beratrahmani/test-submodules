<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Currency\Framework\CurrencyAware;
use Shopware\B2B\Currency\Framework\CurrencyCalculator;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\ProductName\Framework\ProductNameAware;
use Shopware\B2B\ProductName\Framework\ProductNameService;

class AuditLogRepository implements GridRepository
{
    const TABLE_NAME = 'b2b_audit_log';

    const TABLE_ALIAS = 'auditLog';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DbalHelper
     */
    private $dbalHelper;

    /**
     * @var AuditLogIndexRepository
     */
    private $auditLogIndexRepository;

    /**
     * @var CurrencyCalculator
     */
    private $currencyCalculator;

    /**
     * @var ProductNameService
     */
    private $productNameService;

    /**
     * @param Connection $connection
     * @param DbalHelper $dbalHelper
     * @param AuditLogIndexRepository $auditLogIndexRepository
     * @param CurrencyCalculator $currencyCalculator
     * @param ProductNameService $productNameService
     */
    public function __construct(
        Connection $connection,
        DbalHelper $dbalHelper,
        AuditLogIndexRepository $auditLogIndexRepository,
        CurrencyCalculator $currencyCalculator,
        ProductNameService $productNameService
    ) {
        $this->connection = $connection;
        $this->dbalHelper = $dbalHelper;
        $this->auditLogIndexRepository = $auditLogIndexRepository;
        $this->currencyCalculator = $currencyCalculator;
        $this->productNameService = $productNameService;
    }

    /**
     * @param string $referenceTable
     * @param int $referenceId
     * @param AuditLogSearchStruct $searchStruct
     * @param CurrencyContext $currencyContext
     * @return AuditLogEntity[]
     */
    public function fetchList(
        string $referenceTable,
        int $referenceId,
        AuditLogSearchStruct $searchStruct,
        CurrencyContext $currencyContext
    ): array {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->addSelect(AuditLogIndexRepository::TABLE_ALIAS . '.*')
            ->addSelect(AuditLogAuthorRepository::TABLE_ALIAS . '.*')
            ->addSelect(self::TABLE_ALIAS . '.id AS id')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(
                self::TABLE_ALIAS,
                AuditLogAuthorRepository::TABLE_NAME,
                AuditLogAuthorRepository::TABLE_ALIAS,
                self::TABLE_ALIAS . '.author_hash = ' . AuditLogAuthorRepository::TABLE_ALIAS . '.hash'
            )
            ->innerJoin(
                self::TABLE_ALIAS,
                AuditLogIndexRepository::TABLE_NAME,
                AuditLogIndexRepository::TABLE_ALIAS,
                self::TABLE_ALIAS . '.id = ' . AuditLogIndexRepository::TABLE_ALIAS . '.audit_log_id'
            )
            ->where(AuditLogIndexRepository::TABLE_ALIAS . '.reference_table = :referenceTable')
            ->andWhere(AuditLogIndexRepository::TABLE_ALIAS . '.reference_id = :referenceId')
            ->setParameter(':referenceTable', $referenceTable)
            ->setParameter(':referenceId', $referenceId);

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper->applySearchStruct($searchStruct, $query);

        $auditLogs = $query->execute()->fetchAll();

        return array_map(
            function (array $log) use ($currencyContext) {
                $entity = new AuditLogEntity();
                $entity->fromDatabaseArray($log);
                $entity->eventDate = new \DateTime($entity->eventDate);
                $entity->authorIdentity = (new AuditLogAuthorEntity())->fromDatabaseArray($log);

                if ($entity->logValue instanceof CurrencyAware) {
                    $this->currencyCalculator->recalculateAmount($entity->logValue, $currencyContext);
                }

                if ($entity->logValue instanceof ProductNameAware) {
                    $this->productNameService->translateProductName($entity->logValue);
                }

                return $entity;
            },
            $auditLogs
        );
    }

    /**
     * @param string $referenceTable
     * @param int $referenceId
     * @param AuditLogSearchStruct $searchStruct
     * @return int
     */
    public function fetchTotalCount(string $referenceTable, int $referenceId, AuditLogSearchStruct $searchStruct): int
    {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->innerJoin(
                self::TABLE_ALIAS,
                AuditLogIndexRepository::TABLE_NAME,
                AuditLogIndexRepository::TABLE_ALIAS,
                self::TABLE_ALIAS . '.id = ' . AuditLogIndexRepository::TABLE_ALIAS . '.audit_log_id'
            )
            ->where(AuditLogIndexRepository::TABLE_ALIAS . '.reference_table = :referenceTable')
            ->andWhere(AuditLogIndexRepository::TABLE_ALIAS . '.reference_id = :referenceId')
            ->setParameter(':referenceTable', $referenceTable)
            ->setParameter(':referenceId', $referenceId);

        $this->dbalHelper->applyFilters($searchStruct, $query);

        $statement = $query->execute();

        return (int) $statement->fetchColumn(0);
    }

    /**
     * @param AuditLogEntity $auditLogEntity
     * @param AuditLogIndexEntity[] $auditLogIndex
     * @throws \Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException
     * @throws \Shopware\B2B\AuditLog\Framework\RefusingToInsertDuplicatedLogEntryException
     * @return AuditLogEntity
     */
    public function createAuditLog(
        AuditLogEntity $auditLogEntity,
        array $auditLogIndex
    ): AuditLogEntity {
        if (!$auditLogEntity->isNew()) {
            throw new CanNotInsertExistingRecordException('The Audit Log provided already exists');
        }

        if (!$auditLogEntity->logValue->isChanged()) {
            throw new RefusingToInsertDuplicatedLogEntryException('The depending entitiy didn\'nt change.');
        }

        $entityData = $auditLogEntity->toDatabaseArray();

        $this->connection->insert(
            self::TABLE_NAME,
            $entityData
        );

        $auditLogEntity->id = (int) $this->connection->lastInsertId();

        $this->createAuditLogIndex($auditLogEntity->id, $auditLogIndex);

        return $auditLogEntity;
    }

    /**
     * @internal
     * @param int $auditLogId
     * @param AuditLogIndexEntity[] $auditLogIndexes
     * @throws \Exception
     * @return bool
     */
    protected function createAuditLogIndex(int $auditLogId, array $auditLogIndexes)
    {
        foreach ($auditLogIndexes as $index) {
            if (!($index instanceof AuditLogIndexEntity)) {
                throw new IsNoAuditLogIndexEntityException('Provided Entity is not instance of AuditLogIndexEntity');
            }

            $index->auditLogId = $auditLogId;
            $this->auditLogIndexRepository->createAuditLogIndex($index);
        }

        return true;
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
            'log_type',
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
