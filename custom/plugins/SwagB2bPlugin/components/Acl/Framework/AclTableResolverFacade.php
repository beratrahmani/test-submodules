<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Connects ACLTable data and ACLResolver data to a single useful context object.
 */
class AclTableResolverFacade
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var object
     */
    private $context;

    /**
     * @var AclContextResolver
     */
    private $contextResolver;

    /**
     * @param AclContextResolver $contextResolver
     * @param string $tableName
     * @param object $context
     */
    public function __construct(AclContextResolver $contextResolver, string $tableName, $context)
    {
        $this->tableName = $tableName;
        $this->context = $context;
        $this->contextResolver = $contextResolver;
    }

    /**
     * @throws \Shopware\B2B\Acl\Framework\AclUnsupportedContextException
     * @return int
     */
    public function getId(): int
    {
        return $this->contextResolver
            ->extractId($this->context);
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @throws \Shopware\B2B\Acl\Framework\AclUnsupportedContextException
     * @return AclQuery
     */
    public function getQuery(QueryBuilder $queryBuilder): AclQuery
    {
        return $this->contextResolver
            ->getQuery($this->tableName, $this->getId(), $queryBuilder);
    }
}
