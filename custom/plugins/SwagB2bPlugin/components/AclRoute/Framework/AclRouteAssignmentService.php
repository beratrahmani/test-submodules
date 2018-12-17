<?php declare(strict_types=1);

namespace Shopware\B2B\AclRoute\Framework;

use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Acl\Framework\AclUnsupportedContextException;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class AclRouteAssignmentService
{
    /**
     * @var AclRepository
     */
    private $aclRepository;

    /**
     * @var AclRouteRepository
     */
    private $aclRouteRepository;

    /**
     * @var array
     */
    private $routeMapping;

    /**
     * @param AclRepository $aclRepository
     * @param AclRouteRepository $aclRouteRepository
     * @param array $routeMapping
     */
    public function __construct(
        AclRepository $aclRepository,
        AclRouteRepository $aclRouteRepository,
        array $routeMapping
    ) {
        $this->aclRepository = $aclRepository;
        $this->aclRouteRepository = $aclRouteRepository;
        $this->routeMapping = $routeMapping;
    }

    /**
     * @param object $context
     */
    public function allow(OwnershipContext $ownershipContext, $context, int $subjectId, bool $grantable = false)
    {
        $this->testIsGrantable($ownershipContext, $subjectId);
        $this->aclRepository->allow($context, $subjectId, $grantable);

        try {
            $mappedRouteIds = $this->aclRouteRepository->fetchMappedRouteIds($this->routeMapping, $subjectId);
        } catch (NotFoundException $e) {
            return;
        }

        $mappedRouteIds = $this->filterAlreadyAssigned($context, $mappedRouteIds);

        $this->aclRepository->allowAll($context, $mappedRouteIds);
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param object $context
     * @param int $subjectId
     */
    public function deny(OwnershipContext $ownershipContext, $context, int $subjectId)
    {
        $this->testIsGrantable($ownershipContext, $subjectId);
        $this->aclRepository->deny($context, $subjectId);
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param object $context
     * @param string $componentName
     * @param bool $grantable
     */
    public function allowComponent(OwnershipContext $ownershipContext, $context, string $componentName, bool $grantable)
    {
        $subjectIds = $this->aclRouteRepository
            ->fetchActionIdsByControllerName($ownershipContext, $componentName);

        $this->allowAllSubjectIdsIfAllowed($ownershipContext, $context, $subjectIds, $grantable);
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param object $context
     * @param string $componentName
     */
    public function denyComponent(OwnershipContext $ownershipContext, $context, string $componentName)
    {
        $subjectIds = $this->aclRouteRepository
            ->fetchActionIdsByControllerName($ownershipContext, $componentName);

        $this->denyAllSubjectIdsIfAllowed($ownershipContext, $context, $subjectIds);
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param object $context
     * @param bool $grantable
     */
    public function allowAll(OwnershipContext $ownershipContext, $context, bool $grantable = false)
    {
        $subjectIds = $this->aclRouteRepository
            ->fetchAllActionIds($ownershipContext);

        $this->allowAllSubjectIdsIfAllowed($ownershipContext, $context, $subjectIds, $grantable);
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param object $contex
     * @param mixed $context
     */
    public function denyAll(OwnershipContext $ownershipContext, $context)
    {
        $subjectIds = $this->aclRouteRepository
            ->fetchAllActionIds($ownershipContext);

        $this->denyAllSubjectIdsIfAllowed($ownershipContext, $context, $subjectIds);
    }

    /**
     * @internal
     * @param OwnershipContext $ownershipContext
     * @param $context
     * @param array $subjectIds
     */
    protected function denyAllSubjectIdsIfAllowed(OwnershipContext $ownershipContext, $context, array $subjectIds)
    {
        $this->testSubjectIdsGrantable($ownershipContext, $subjectIds);
        $this->aclRepository->denyAll($context, $subjectIds);
    }

    /**
     * @internal
     * @param OwnershipContext $ownershipContext
     * @param $context
     * @param array $subjectIds
     * @param bool $grantable
     */
    protected function allowAllSubjectIdsIfAllowed(OwnershipContext $ownershipContext, $context, array $subjectIds, bool $grantable)
    {
        $this->testSubjectIdsGrantable($ownershipContext, $subjectIds);
        $this->aclRepository->allowAll($context, $subjectIds, $grantable);
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param array $subjectIds
     */
    protected function testSubjectIdsGrantable(OwnershipContext $ownershipContext, array $subjectIds)
    {
        foreach ($subjectIds as $subjectId) {
            $this->testIsGrantable($ownershipContext, $subjectId);
        }
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param $subjectId
     * @throws AclWriteOperationNotAllowedException
     */
    protected function testIsGrantable(OwnershipContext $ownershipContext, $subjectId)
    {
        try {
            $isGrantable = $this->aclRepository->isGrantable($ownershipContext, $subjectId);

            if (!$isGrantable) {
                throw new \DomainException($ownershipContext->identityClassName . '::' . $ownershipContext->authId . ' is not allowed to assign ' . $subjectId);
            }
        } catch (AclUnsupportedContextException $e) {
            //nth
        }
    }

    /**
     * @param object $context
     * @return int[]
     */
    public function filterAlreadyAssigned($context, array $mappedRouteIds): array
    {
        $alreadyAssignedIds = array_keys($this->aclRepository->fetchAllDirectlyIds($context));
        $mappedRouteIds = array_diff($mappedRouteIds, $alreadyAssignedIds);

        return $mappedRouteIds;
    }
}
