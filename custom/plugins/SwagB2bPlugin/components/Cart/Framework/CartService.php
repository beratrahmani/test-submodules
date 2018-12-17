<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Framework;

use Shopware\B2B\OrderClearance\Framework\OrderClearanceEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class CartService
{
    const ENVIRONMENT_NAME_ORDER = 'order';

    const ENVIRONMENT_NAME_MODIFY = 'modify';

    const ENVIRONMENT_NAME_LISTING = 'listing';

    /**
     * @var CartAccessFactoryInterface[]
     */
    private $rootStrategyFactories;

    /**
     * @param CartAccessFactoryInterface[] $rootStrategyFactories
     */
    public function __construct(CartAccessFactoryInterface ... $rootStrategyFactories)
    {
        $this->rootStrategyFactories = $rootStrategyFactories;
    }

    /**
     * @param Identity $identity
     * @param OrderClearanceEntity $orderEntity
     * @param string $environmentName
     * @return CartAccessResult
     */
    public function computeAccessibility(
        Identity $identity,
        OrderClearanceEntity $orderEntity,
        string $environmentName
    ): CartAccessResult {
        $result = new CartAccessResult();

        if ($identity->isSuperAdmin()) {
            return $result;
        }

        $strategies = [];
        foreach ($this->rootStrategyFactories as $strategyFactory) {
            $strategies[] = $strategyFactory->createCartAccessForIdentity($identity, $environmentName);
        }

        $blackList = new BlackListCartAccess(...$strategies);
        $cartContext = $this->createCartAccessContext($orderEntity);

        $blackList->checkAccess($cartContext, $result);
        $blackList->addInformation($result);

        return $result;
    }

    /**
     * @internal
     * @param OrderClearanceEntity $orderEntity
     * @return CartAccessContext
     */
    protected function createCartAccessContext(OrderClearanceEntity $orderEntity): CartAccessContext
    {
        $context = new CartAccessContext();
        $context->orderClearanceEntity = $orderEntity;

        return $context;
    }
}
