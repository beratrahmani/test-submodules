<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\ProductOrderNumberType;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Cart\Framework\CartAccessStrategyInterface;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleEntity;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeInterface;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeRepositoryInterface;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeValidationExtender;
use Shopware\B2B\ContingentRule\Framework\UnsupportedContingentRuleEntityTypeException;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class ProductOrderNumberType implements ContingentRuleTypeInterface
{
    const NAME = 'ProductOrderNumber';

    /**
     * @var ProductOrderNumberRepository
     */
    private $articleOrderNumberRepository;

    /**
     * @param ProductOrderNumberRepository $articleOrderNumberRepository
     */
    public function __construct(ProductOrderNumberRepository $articleOrderNumberRepository)
    {
        $this->articleOrderNumberRepository = $articleOrderNumberRepository;
    }

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
        return new ProductOrderNumberRuleEntity($this->getTypeName());
    }

    /**
     * {@inheritdoc}
     */
    public function createValidationExtender(ContingentRuleEntity $entity): ContingentRuleTypeValidationExtender
    {
        return new ProductOrderNumberRuleValidationExtender($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function createCartAccessStrategy(
        OwnershipContext $ownershipContext,
        ContingentRuleEntity $entity
    ): CartAccessStrategyInterface {
        if (!$entity instanceof ProductOrderNumberRuleEntity) {
            throw new UnsupportedContingentRuleEntityTypeException($entity);
        }

        return new ProductOrderNumberAccessStrategy($entity->productOrderNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(Connection $connection): ContingentRuleTypeRepositoryInterface
    {
        return new ProductOrderNumberRepository($connection);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestKeys(): array
    {
        return [
            'productOrderNumber',
        ];
    }
}
