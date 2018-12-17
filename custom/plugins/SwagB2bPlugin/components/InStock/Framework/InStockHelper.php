<?php declare(strict_types=1);

namespace Shopware\B2B\InStock\Framework;

use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class InStockHelper
{
    /**
     * @var InStockRepository
     */
    private $inStockRepository;

    /**
     * @param InStockRepository $inStockRepository
     */
    public function __construct(InStockRepository $inStockRepository)
    {
        $this->inStockRepository = $inStockRepository;
    }

    /**
     * @param Identity $identity
     * @param InStockSearchStruct $searchStruct
     * @return InStockEntity[]
     */
    public function getCascadedInStocksForAuthId(Identity $identity, InStockSearchStruct $searchStruct): array
    {
        $inStocks = $this->inStockRepository
            ->fetchInStocksByAuthId($identity->getAuthId(), $searchStruct);

        $debtorInStocks = $this->inStockRepository
            ->fetchInStocksByAuthId($identity->getContextAuthId(), $searchStruct);

        return array_replace($debtorInStocks, $inStocks);
    }
}
