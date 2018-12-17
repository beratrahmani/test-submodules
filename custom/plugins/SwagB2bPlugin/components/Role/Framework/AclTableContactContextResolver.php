<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Acl\Framework\AclContextResolver;
use Shopware\B2B\Acl\Framework\AclQuery;
use Shopware\B2B\Acl\Framework\AclUnsupportedContextException;
use Shopware\B2B\Contact\Framework\ContactAclGrantContext;
use Shopware\B2B\Contact\Framework\ContactEntity;
use Shopware\B2B\Contact\Framework\ContactIdentity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class AclTableContactContextResolver extends AclContextResolver
{
    /**
     * @return bool
     */
    public function isMainContext(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(string $aclTableName, int $contextId, QueryBuilder $queryBuilder): AclQuery
    {
        $mainAlias = $this->getNextPrefix();
        $treeRolePrefix = $this->getNextPrefix();
        $directRolePrefix = $this->getNextPrefix();
        $roleContactPrefix = $this->getNextPrefix();

        $queryBuilder
            ->select($mainAlias . '.*')
            ->from($aclTableName, $mainAlias)
            ->innerJoin(
                $mainAlias,
                'b2b_role',
                $directRolePrefix,
                "{$mainAlias}.entity_id = {$directRolePrefix}.id"
            )->innerJoin(
                $directRolePrefix,
                'b2b_role',
                $treeRolePrefix,
                "{$directRolePrefix}.left >= {$treeRolePrefix}.left AND {$directRolePrefix}.right <= {$treeRolePrefix}.right AND {$directRolePrefix}.context_owner_id = {$treeRolePrefix}.context_owner_id"
            )->innerJoin(
                $treeRolePrefix,
                'b2b_role_contact',
                $roleContactPrefix,
                "{$roleContactPrefix}.role_id = {$treeRolePrefix}.id"
            )->where(
                "{$roleContactPrefix}.debtor_contact_id = :p_{$roleContactPrefix}ContextId"
            )
            ->setParameter(":p_{$roleContactPrefix}ContextId", $contextId);

        return (new AclQuery())->fromQueryBuilder($queryBuilder);
    }

    /**
     * @param $context
     * @throws AclUnsupportedContextException
     * @return int
     */
    public function extractId($context): int
    {
        if ($context instanceof ContactIdentity) {
            return $context->getId();
        }

        if ($context instanceof OwnershipContext && is_a($context->identityClassName, ContactIdentity::class, true)) {
            return $context->identityId;
        }

        if ($context instanceof ContactEntity) {
            return $context->id;
        }

        if ($context instanceof ContactAclGrantContext) {
            return $context->getEntity()->id;
        }

        throw new AclUnsupportedContextException();
    }
}
