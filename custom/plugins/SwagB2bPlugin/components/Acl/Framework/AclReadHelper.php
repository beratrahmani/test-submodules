<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class AclReadHelper
{
    /**
     * @var AclRepository
     */
    private $aclRepository;

    /**
     * @var string
     */
    private $mainTableAlias;

    /**
     * @var string
     */
    private $primaryKeyFieldName;

    /**
     * @param AclRepository $aclRepository
     * @param string $mainTableAlias
     * @param string $primaryKeyFieldName
     */
    public function __construct(
        AclRepository $aclRepository,
        string $mainTableAlias,
        string $primaryKeyFieldName = 'id'
    ) {
        $this->aclRepository = $aclRepository;
        $this->mainTableAlias = $mainTableAlias;
        $this->primaryKeyFieldName = $primaryKeyFieldName;
    }

    /**
     * @param OwnershipContext $context
     * @param QueryBuilder $query
     * @param string $alias
     */
    public function applyAclVisibility(
        OwnershipContext $context,
        QueryBuilder $query,
        string $alias = 'aclQuery'
    ) {
        try {
            $aclQuery = $this->aclRepository->getUnionizedSqlQuery($context);

            $query->innerJoin(
                $this->mainTableAlias,
                '(' . $aclQuery->sql . ')',
                $alias,
                "{$this->mainTableAlias}.{$this->primaryKeyFieldName} = {$alias}.referenced_entity_id"
            );

            foreach ($aclQuery->params as $name => $value) {
                $query->setParameter($name, $value);
            }
        } catch (AclUnsupportedContextException $e) {
            // nth
        }
    }

    /**
     * @param AclGrantContextSearchStruct $grantContextSearchStruct
     * @param QueryBuilder $query
     * @param string $alias
     */
    public function applyAclFilter(
        AclGrantContextSearchStruct $grantContextSearchStruct,
        QueryBuilder $query,
        string $alias = 'aclFilter'
    ) {
        $grantContext = $grantContextSearchStruct->aclGrantContext;

        $aclQuery = $this->aclRepository
            ->getDirectAssignableQuery($grantContext);

        $query->innerJoin(
            $this->mainTableAlias,
            '(' . $aclQuery->sql . ')',
            $alias,
            "{$alias}.referenced_entity_id = {$this->mainTableAlias}.{$this->primaryKeyFieldName}"
        );

        foreach ($aclQuery->params as $key => $value) {
            $query->setParameter($key, $value);
        }
    }
}
