<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\TimeRestrictionType;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Cart\Framework\CartAccessStrategyInterface;
use Shopware\B2B\Cart\Framework\CartHistoryRepositoryInterface;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleEntity;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleService;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeInterface;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeRepositoryInterface;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeValidationExtender;
use Shopware\B2B\ContingentRule\Framework\UnsupportedContingentRuleEntityTypeException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderItemQuantityType implements ContingentRuleTypeInterface
{
    const NAME = 'OrderItemQuantity';

    /**
     * @var CartHistoryRepositoryInterface
     */
    private $cartHistoryRepository;

    /**
     * @var ContingentRuleService
     */
    private $contingentRuleService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param ContingentRuleService $contingentRuleService
     * @param CartHistoryRepositoryInterface $cartHistoryRepository
     * @param CurrencyService $currencyService
     */
    public function __construct(
        ContingentRuleService $contingentRuleService,
        CartHistoryRepositoryInterface $cartHistoryRepository,
        CurrencyService $currencyService
    ) {
        $this->cartHistoryRepository = $cartHistoryRepository;
        $this->contingentRuleService = $contingentRuleService;
        $this->currencyService = $currencyService;
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
        return new TimeRestrictionRuleEntity($this->getTypeName());
    }

    /**
     * {@inheritdoc}
     */
    public function createValidationExtender(ContingentRuleEntity $entity): ContingentRuleTypeValidationExtender
    {
        return new TimeRestrictionRuleValidationExtender($entity, $this->contingentRuleService);
    }

    /**
     * {@inheritdoc}
     */
    public function createCartAccessStrategy(OwnershipContext $ownershipContext, ContingentRuleEntity $entity): CartAccessStrategyInterface
    {
        if (!$entity instanceof TimeRestrictionRuleEntity) {
            throw new UnsupportedContingentRuleEntityTypeException($entity);
        }

        $currencyContext = $this->currencyService->createCurrencyContext();

        $cartHistory = $this->cartHistoryRepository
            ->fetchHistory(
                $this->contingentRuleService->getTimeRestrictions(),
                $ownershipContext,
                $currencyContext
            );

        return new OrderItemQuantityAccessStrategy(
            $cartHistory[$entity->timeRestriction],
            (int) $entity->value
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(Connection $connection): ContingentRuleTypeRepositoryInterface
    {
        return new TimeRestrictionRepository($connection);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestKeys(): array
    {
        return [
            'timeRestriction',
            'value',
        ];
    }
}
