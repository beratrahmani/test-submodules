<?php declare(strict_types=1);

namespace Shopware\B2B\Currency\Framework;

use Shopware\B2B\Shop\Framework\ShopServiceInterface;

class CurrencyService
{
    /**
     * @var ShopServiceInterface
     */
    private $shopService;

    /**
     * @param ShopServiceInterface $shopService
     */
    public function __construct(ShopServiceInterface $shopService)
    {
        $this->shopService = $shopService;
    }

    /**
     * @return CurrencyContext
     */
    public function createCurrencyContext(): CurrencyContext
    {
        return new CurrencyContext($this->shopService->getCurrentCurrencyFactor());
    }
}
