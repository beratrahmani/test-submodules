<?php declare(strict_types=1);

namespace Shopware\B2B\InStock\Bridge;

use Shopware\B2B\Common\Filter\EqualsFilter;
use Shopware\B2B\Common\Filter\OrFilter;
use Shopware\B2B\InStock\Framework\InStockEntity;
use Shopware\B2B\InStock\Framework\InStockHelper;
use Shopware\B2B\InStock\Framework\InStockRepository;
use Shopware\B2B\InStock\Framework\InStockSearchStruct;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\Bundle\StoreFrontBundle\Gateway\ListProductGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware_Components_Config as ShopConfig;

class ListProductGatewayDecorator implements ListProductGatewayInterface
{
    /**
     * @var ListProductGatewayInterface
     */
    private $decoratedGateway;

    /**
     * @var InStockRepository
     */
    private $inStockRepository;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var ShopConfig
     */
    private $config;

    /**
     * @var InStockHelper
     */
    private $inStockHelper;

    /**
     * @var InStockBridgeRepository
     */
    private $bridgeRepository;

    /**
     * @param ListProductGatewayInterface $decoratedGateway
     * @param InStockRepository $inStockRepository
     * @param AuthenticationService $authenticationService
     * @param ShopConfig $config
     * @param InStockHelper $inStockHelper
     * @param InStockBridgeRepository $bridgeRepository
     */
    public function __construct(
        ListProductGatewayInterface $decoratedGateway,
        InStockRepository $inStockRepository,
        AuthenticationService $authenticationService,
        ShopConfig $config,
        InStockHelper $inStockHelper,
        InStockBridgeRepository $bridgeRepository
    ) {
        $this->decoratedGateway = $decoratedGateway;
        $this->inStockRepository = $inStockRepository;
        $this->authenticationService = $authenticationService;
        $this->config = $config;
        $this->inStockHelper = $inStockHelper;
        $this->bridgeRepository = $bridgeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $numbers, Struct\ShopContextInterface $context)
    {
        $listProducts = $this->decoratedGateway->getList($numbers, $context);

        if (!$this->authenticationService->isB2b() || !$listProducts) {
            return $listProducts;
        }

        $searchStruct = new InStockSearchStruct();

        $filters = [];
        foreach ($listProducts as $listProduct) {
            $filters[] = $this->getDetailFilters($listProduct->getVariantId());
        }

        $searchStruct->filters = [new OrFilter($filters)];

        $inStocks = $this->inStockHelper->getCascadedInStocksForAuthId(
            $this->authenticationService->getIdentity(),
            $searchStruct
        );

        if (count($inStocks) === 0) {
            return $listProducts;
        }

        $minPurchases = $this->bridgeRepository->getMinPurchases(array_keys($inStocks));

        return $this->setStock($listProducts, $inStocks, $minPurchases);
    }

    /**
     * @internal
     * @param Struct\ListProduct[] $listProducts
     * @param InStockEntity[] $inStocks
     * @param array $minPurchases
     * @return Struct\ListProduct[]
     */
    protected function setStock(array $listProducts, array $inStocks, array $minPurchases): array
    {
        foreach ($listProducts as $key => $listProduct) {
            if (!array_key_exists($listProduct->getVariantId(), $inStocks)) {
                continue;
            }

            $inStock = $inStocks[$listProduct->getVariantId()]->inStock;

            if ($this->config->get('hideNoInStock')
                && $listProduct->isCloseouts()
                && $minPurchases[$listProduct->getVariantId()] > $inStock
            ) {
                unset($listProducts[$key]);
                continue;
            }

            $listProduct->setStock($inStock);
        }

        return $listProducts;
    }

    /**
     * @internal
     * @param int $detailId
     * @return EqualsFilter
     */
    protected function getDetailFilters(int $detailId): EqualsFilter
    {
        return new EqualsFilter(
            $this->inStockRepository::TABLE_ALIAS,
            'articles_details_id',
            $detailId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get($number, Struct\ShopContextInterface $context)
    {
        $products = $this->getList([$number], $context);

        return array_shift($products);
    }
}
