<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

/**
 * Helper Service to extend Entities with ACL-properties
 */
class AclAccessExtensionService
{
    /**
     * Add fields `foreignAllowed` and `foreignGrantable` in relation to a context identity
     *
     * @param AclRepository $aclRepository
     * @param object $context
     * @param array $entities
     */
    public function extendEntitiesWithAssignment(AclRepository $aclRepository, $context, array $entities)
    {
        $relatedAclSettings = $aclRepository->fetchAllDirectlyIds($context);

        foreach ($entities as $entity) {
            $entity->foreignAllowed = array_key_exists($entity->id, $relatedAclSettings);
            $entity->foreignGrantable = false;

            if (!$entity->foreignAllowed) {
                continue;
            }

            $entity->foreignGrantable = $relatedAclSettings[$entity->id];
        }
    }

    /**
     * Add `ownerGrantable` to $entities
     *
     * @param AclRepository $aclRepository
     * @param object $context
     * @param object[] $entities
     */
    public function extendEntitiesWithIdentityOwnership(AclRepository $aclRepository, $context, array $entities)
    {
        try {
            $ownerAclSettings = $aclRepository->fetchAllGrantableIds($context);

            foreach ($entities as $entity) {
                $entity->ownerGrantable = false;

                if (!isset($ownerAclSettings[$entity->id])) {
                    continue;
                }

                if (!$ownerAclSettings[$entity->id]) {
                    continue;
                }

                $entity->ownerGrantable = true;
            }
        } catch (AclUnsupportedContextException $e) {
            foreach ($entities as $entity) {
                $entity->ownerGrantable = true;
            }
        }
    }
}
