<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Cart\Framework\CartAccessStrategyInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

interface ContingentRuleTypeInterface
{
    /**
     * @return string
     */
    public function getTypeName(): string;

    /**
     * @return ContingentRuleEntity
     */
    public function createEntity(): ContingentRuleEntity;

    /**
     * @param ContingentRuleEntity $entity
     * @return ContingentRuleTypeValidationExtender
     */
    public function createValidationExtender(ContingentRuleEntity $entity): ContingentRuleTypeValidationExtender;

    /**
     * @param OwnershipContext $ownershipContext
     * @param ContingentRuleEntity $entity
     * @return CartAccessStrategyInterface
     */
    public function createCartAccessStrategy(OwnershipContext $ownershipContext, ContingentRuleEntity $entity): CartAccessStrategyInterface;

    /**
     * @param Connection $connection
     * @return ContingentRuleTypeRepositoryInterface
     */
    public function getRepository(Connection $connection): ContingentRuleTypeRepositoryInterface;

    /**
     * @return string[]
     */
    public function getRequestKeys(): array;
}
