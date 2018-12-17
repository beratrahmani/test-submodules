<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Bridge;

use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Shop\Framework\ProductServiceInterface;
use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductSearchInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Currency;
use Shopware\Models\Shop\Shop;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class ProductService implements ProductServiceInterface
{
    /**
     * @var ListProductServiceInterface
     */
    private $productService;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var ProductSearchInterface
     */
    private $productSearch;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param ListProductServiceInterface $productService
     * @param ContextServiceInterface $contextService
     * @param ProductSearchInterface $productSearch
     * @param ModelManager $modelManager
     */
    public function __construct(
        ListProductServiceInterface $productService,
        ContextServiceInterface $contextService,
        ProductSearchInterface $productSearch,
        ModelManager $modelManager
    ) {
        $this->productService = $productService;
        $this->contextService = $contextService;
        $this->productSearch = $productSearch;
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     * @throws NotFoundException
     */
    public function fetchProductNameByOrderNumber(string $orderNumber): string
    {
        $contextFactory = $this->contextService->createShopContext(
            $this->getShopId(),
            $this->getCurrencyId(),
            $this->getCustomerGroupKey()
        );

        $product = $this->productService->get($orderNumber, $contextFactory);

        if (!$product) {
            throw new NotFoundException("Product with order number {$orderNumber} not found.");
        }

        if ($product->getAdditional()) {
            return $product->getName() . ' ' . $product->getAdditional();
        }

        return $product->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchProductNamesByOrderNumbers(array $orderNumbers): array
    {
        $productNames = [];

        $contextFactory = $this->contextService->createShopContext(
            $this->getShopId(),
            $this->getCurrencyId(),
            $this->getCustomerGroupKey()
        );

        $products = $this->productService->getList($orderNumbers, $contextFactory);

        array_walk(
            $products,
            function (ListProduct $product) use (&$productNames) {
                $number = $product->getNumber();

                if ($product->hasAttribute('b2b_ordernumber')) {
                    $number = $product->getAttribute('b2b_ordernumber')->get('custom_ordernumber');
                }

                $productNames[$number] = $product->getName();


                if ($product->getAdditional()) {
                    $productNames[$number] .= ' ' . $product->getAdditional();
                }
            }
        );

        return $productNames;
    }

    /**
     * {@inheritdoc}
     */
    public function searchProductsByNameOrOrderNumber(string $term, int $limit): array
    {
        $customerGroupKey = $this->getCustomerGroupKey();
        $context = $this->contextService
            ->createShopContext($this->getShopId(), $this->getCurrencyId(), $customerGroupKey);

        $criteria = new Criteria();
        $criteria->addBaseCondition(new SearchTermCondition($term));
        $criteria->addBaseCondition(new VariantCondition());
        $criteria->limit($limit);

        $products = [];
        foreach (@$this->productSearch->search($criteria, $context)->getProducts() as $product) {
            $orderNumber = $product->getNumber();

            if ($product->hasAttribute('b2b_ordernumber')) {
                $orderNumber = $product->getAttribute('b2b_ordernumber')->get('custom_ordernumber');
            }

            $products[$orderNumber] = ['name' => $product->getName()];

            if ($product->getUnit() !== null) {
                $products[$orderNumber]['min'] = $product->getUnit()->getMinPurchase();
                $products[$orderNumber]['step'] = 1;

                if ($steps = $product->getUnit()->getPurchaseStep()) {
                    $products[$orderNumber]['step'] = $steps;
                }

                if ($max = $product->getUnit()->getMaxPurchase()) {
                    $products[$orderNumber]['max'] = $max;
                }
            }

            if ($product->getAdditional()) {
                $products[$orderNumber]['name'] .= ' ' . $product->getAdditional();
            }
        }

        return $products;
    }

    /**
     * @return int
     */
    private function getShopId(): int
    {
        try {
            $shop = Shopware()->Container()->get('shop');

            if (!$shop) {
                throw new ServiceNotFoundException('Unable to load shop through container');
            }

            return $shop->getId();
        } catch (ServiceNotFoundException $e) {
            return $this->modelManager
                ->getRepository(Shop::class)
                ->getDefault()
                ->getId();
        }
    }

    /**
     * @return string
     */
    private function getCustomerGroupKey(): string
    {
        try {
            $shop = Shopware()->Container()->get('shop');

            if (!$shop) {
                throw new ServiceNotFoundException('Unable to load shop through container');
            }

            return $shop->getCustomerGroup()->getKey();
        } catch (ServiceNotFoundException $e) {
            return $this->modelManager
                ->getRepository(Shop::class)
                ->getDefault()
                ->getCustomerGroup()
                ->getKey();
        }
    }

    /**
     * @return int
     */
    private function getCurrencyId(): int
    {
        try {
            /** @var Shop $shop */
            $shop = Shopware()->Container()->get('shop');

            if (!$shop) {
                throw new ServiceNotFoundException('Unable to load shop through container');
            }

            return $shop->getCurrency()->getId();
        } catch (ServiceNotFoundException $e) {
            return $this->modelManager
                ->getRepository(Currency::class)
                ->findOneBy(['default' => true])
                ->getId();
        }
    }
}
