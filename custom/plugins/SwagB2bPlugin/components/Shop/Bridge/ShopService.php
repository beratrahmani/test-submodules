<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Bridge;

use Shopware\B2B\Shop\Framework\ShopServiceInterface;
use Shopware\Components\DependencyInjection\Container;

class ShopService implements ShopServiceInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootCategoryId(): int
    {
        /** @var \Shopware\Models\Shop\Shop $shop */
        $shop = $this->container->get('shop');

        return (int) $shop->getCategory()->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentCurrencyFactor(): float
    {
        if (!$this->container->has('shop')) {
            return (float) 1;
        }

        /** @var \Shopware\Models\Shop\Shop $shop */
        $shop = $this->container->get('shop');

        return $shop->getCurrency()->getFactor();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentCurrencySymbol(): string
    {
        if (!$this->container->has('shop')) {
            return $this->getDefaultCurrencySymbol();
        }

        /** @var \Shopware\Models\Shop\Shop $shop */
        $shop = $this->container->get('shop');

        return $shop->getCurrency()->getSymbol();
    }

    /**
     * @internal
     * @return string
     */
    protected function getDefaultCurrencySymbol(): string
    {
        $connection = $this->container->get('dbal_connection');
        $currency = $connection->createQueryBuilder()
            ->select('templatechar')
            ->from('s_core_currencies')
            ->where('standard = 1')
            ->execute()
            ->fetchColumn();

        if (!$currency) {
            return '&euro;';
        }

        return $currency;
    }
}
