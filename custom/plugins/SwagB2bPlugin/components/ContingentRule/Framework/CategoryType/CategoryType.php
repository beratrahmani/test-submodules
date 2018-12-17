<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\CategoryType;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Cart\Framework\CartAccessStrategyInterface;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleEntity;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeInterface;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeRepositoryInterface;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeValidationExtender;
use Shopware\B2B\ContingentRule\Framework\UnsupportedContingentRuleEntityTypeException;
use Shopware\B2B\Shop\Framework\CategoryRepositoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class CategoryType implements ContingentRuleTypeInterface
{
    const NAME = 'Category';

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
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
        return new CategoryRuleEntity($this->getTypeName());
    }

    /**
     * {@inheritdoc}
     */
    public function createValidationExtender(ContingentRuleEntity $entity): ContingentRuleTypeValidationExtender
    {
        return new CategoryRuleValidationExtender($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function createCartAccessStrategy(OwnershipContext $ownershipContext, ContingentRuleEntity $entity): CartAccessStrategyInterface
    {
        if (!$entity instanceof CategoryRuleEntity) {
            throw new UnsupportedContingentRuleEntityTypeException($entity);
        }

        return new CategoryAccessStrategy($entity->categoryId, $this->categoryRepository);
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(Connection $connection): ContingentRuleTypeRepositoryInterface
    {
        return new CategoryRepository($connection);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestKeys(): array
    {
        return [
            'categoryId',
        ];
    }
}
