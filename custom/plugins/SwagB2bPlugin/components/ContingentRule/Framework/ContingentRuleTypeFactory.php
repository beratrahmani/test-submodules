<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework;

use Shopware\B2B\Cart\Framework\CartAccessStrategyInterface;
use Shopware\B2B\Common\Service\CrudServiceRequest;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class ContingentRuleTypeFactory
{
    /**
     * @var ContingentRuleTypeInterface[]
     */
    private $contingentRuleTypes;

    /**
     * @param ContingentRuleTypeInterface[] $contingentRuleTypes, ...
     */
    public function __construct(ContingentRuleTypeInterface ... $contingentRuleTypes)
    {
        $this->contingentRuleTypes = $contingentRuleTypes;
    }

    /**
     * @param string $typeName
     * @return string[]
     */
    public function getRequestKeys(string $typeName): array
    {
        return $this->findTypeByName($typeName)->getRequestKeys();
    }

    /**
     * @param string $typeName
     * @throws \InvalidArgumentException
     * @return ContingentRuleEntity
     */
    public function createEntityFromTypeName(string $typeName): ContingentRuleEntity
    {
        return $this->findTypeByName($typeName)->createEntity();
    }

    /**
     * @param CrudServiceRequest $request
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @return ContingentRuleEntity
     */
    public function createEntityFromServiceRequest(CrudServiceRequest $request): ContingentRuleEntity
    {
        return $this->findTypeByName($request->requireParam('type'))->createEntity();
    }

    /**
     * @param string $typeName
     * @param OwnershipContext $ownershipContext
     * @param ContingentRuleEntity $entity
     * @return CartAccessStrategyInterface
     */
    public function createCartAccessStrategy(
        string $typeName,
        OwnershipContext $ownershipContext,
        ContingentRuleEntity $entity
    ): CartAccessStrategyInterface {
        $type = $this->findTypeByName($typeName);

        return $type->createCartAccessStrategy($ownershipContext, $entity);
    }

    /**
     * @param string $typeName
     * @throws \InvalidArgumentException
     * @return ContingentRuleTypeInterface
     */
    public function findTypeByName(string $typeName): ContingentRuleTypeInterface
    {
        foreach ($this->contingentRuleTypes as $contingentRuleType) {
            if ($contingentRuleType->getTypeName() === $typeName) {
                return $contingentRuleType;
            }
        }

        throw new \InvalidArgumentException(
            'Unable to create a contingent rule entity - type "' . $typeName . '" not known '
        );
    }
}
