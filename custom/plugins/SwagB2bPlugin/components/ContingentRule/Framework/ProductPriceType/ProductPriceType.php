<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\ProductPriceType;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Cart\Framework\CartAccessStrategyInterface;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleEntity;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeInterface;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeRepositoryInterface;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeValidationExtender;
use Shopware\B2B\ContingentRule\Framework\UnsupportedContingentRuleEntityTypeException;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class ProductPriceType implements ContingentRuleTypeInterface
{
    const NAME = 'ProductPrice';

    /**
     * {@inheritdoc}
     */
    public function getTypeName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity(): ContingentRuleEntity
    {
        return new ProductPriceRuleEntity($this->getTypeName());
    }

    /**
     * {@inheritdoc}
     */
    public function createValidationExtender(ContingentRuleEntity $entity): ContingentRuleTypeValidationExtender
    {
        return new ProductPriceRuleValidationExtender($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function createCartAccessStrategy(
        OwnershipContext $ownershipContext,
        ContingentRuleEntity $entity
    ): CartAccessStrategyInterface {
        if (!$entity instanceof ProductPriceRuleEntity) {
            throw new UnsupportedContingentRuleEntityTypeException($entity);
        }

        return new ProductPriceAccessStrategy($entity->productPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(Connection $connection): ContingentRuleTypeRepositoryInterface
    {
        return new ProductPriceRepository($connection);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestKeys(): array
    {
        return [
            'productPrice',
        ];
    }
}
