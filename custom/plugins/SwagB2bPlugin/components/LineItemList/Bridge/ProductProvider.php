<?php declare(strict_types=1);

namespace Shopware\B2B\LineItemList\Bridge;

use InvalidArgumentException;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemReference;
use Shopware\B2B\LineItemList\Framework\ProductProviderInterface;
use Shopware\B2B\LineItemList\Framework\UnsupportedQuantityException;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Service\ProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Product;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Currency;
use Shopware\Models\Shop\Shop;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class ProductProvider implements ProductProviderInterface
{
    /**
     * @var ProductServiceInterface
     */
    private $productService;

    /**
     * @var ContextService
     */
    private $contextService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param ProductServiceInterface $productService
     * @param ContextService $contextService
     * @param ModelManager $modelManager
     */
    public function __construct(
        ProductServiceInterface $productService,
        ContextService $contextService,
        ModelManager $modelManager
    ) {
        $this->productService = $productService;
        $this->contextService = $contextService;
        $this->modelManager = $modelManager;
    }

    /**
     * @param LineItemList $list
     */
    public function updateList(LineItemList $list)
    {
        $productNumbers = array_map(function (LineItemReference $reference) {
            return $reference->referenceNumber;
        }, $list->references);

        $shopContext = $this->contextService->createShopContext($this->getShopId(), $this->getCurrencyId(), $this->getCustomerGroupKey());

        $products = $this->productService->getList($productNumbers, $shopContext);
        $totalAmount = 0;
        $totalAmountNet = 0;

        foreach ($productNumbers as $productNumber) {
            $reference = $this->findReferenceInList($list, $productNumber);

            $this->updateReferenceFromProduct($products, $productNumber, $reference);

            $totalAmount += $reference->quantity * (float) $reference->amount;
            $totalAmountNet += $reference->quantity * (float) $reference->amountNet;
        }

        $list->amount = $totalAmount;
        $list->amountNet = $totalAmountNet;
        $list->currencyFactor = $shopContext->getCurrency()->getFactor();
    }

    /**
     * @param LineItemReference $lineItemReference
     */
    public function updateReference(LineItemReference $lineItemReference)
    {
        $shopContext = $this->contextService->createShopContext($this->getShopId(), $this->getCurrencyId(), $this->getCustomerGroupKey());

        $product = @$this->productService
            ->get($lineItemReference->referenceNumber, $shopContext);

        if (!$product) {
            return;
        }

        $this->updateReferenceFromProduct([$product], $lineItemReference->referenceNumber, $lineItemReference);
    }

    /**
     * @param string $productNumber
     * @return bool
     */
    public function isProduct(string $productNumber): bool
    {
        $shopContext = $this->contextService->createShopContext($this->getShopId(), $this->getCurrencyId(), $this->getCustomerGroupKey());

        $products = @$this->productService
            ->getList([$productNumber], $shopContext);

        return count($products) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxMinAndSteps(LineItemReference $lineItemReference)
    {
        $shopContext = $this->contextService->createShopContext($this->getShopId());
        $purchaseStep = 1;
        $minPurchase = 1;

        if (
            ($product = @$this->productService->get($lineItemReference->referenceNumber, $shopContext)) !== null
            && $product->getUnit() !== null
        ) {
            if ($max = $product->getUnit()->getMaxPurchase()) {
                $lineItemReference->maxPurchase = $max;
            }

            if ($step = $product->getUnit()->getPurchaseStep()) {
                $purchaseStep = $step;
            }

            if ($min = $product->getUnit()->getMinPurchase()) {
                $minPurchase = $min;
            }
        }

        $lineItemReference->minPurchase = $minPurchase;
        $lineItemReference->purchaseStep = $purchaseStep;
    }

    /**
     * @param LineItemList $list
     * @param string $productNumber
     * @return LineItemReference
     */
    private function findReferenceInList(LineItemList $list, string $productNumber): LineItemReference
    {
        $foundIndex = false;
        foreach ($list->references as $index => $currentReference) {
            if (strcasecmp($currentReference->referenceNumber, $productNumber) === 0) {
                $foundIndex = $index;
                break;
            }
        }

        return $list->references[$foundIndex];
    }

    /**
     * @param string $price
     * @param string $locale
     * @throws InvalidArgumentException
     * @return float
     */
    public static function convertPriceToLocale(string $price, string $locale = 'en_EN'): float
    {
        $formatter = numfmt_create($locale, \NumberFormatter::DEFAULT_STYLE);

        $formattedPrice = numfmt_parse($formatter, $price);

        if (!$formattedPrice) {
            throw new InvalidArgumentException('wrong price format given');
        }

        return (float) $formattedPrice;
    }

    /**
     * @param Product[] $products
     * @param string $productNumber
     * @param LineItemReference $reference
     */
    private function updateReferenceFromProduct(array $products, string $productNumber, LineItemReference $reference)
    {
        $product = null;

        foreach ($products as $currentProduct) {
            if ($currentProduct->getNumber() === $productNumber) {
                $product = $currentProduct;
                break;
            }

            if ($this->isCustomOrderNumber($currentProduct, $productNumber)) {
                $product = $currentProduct;
                break;
            }
        }

        if (!$product) {
            return;
        }

        $this->determinePriceForReference($reference, $product);
    }

    /**
     * @param Product $currentProduct
     * @param string $productNumber
     * @return bool
     */
    private function isCustomOrderNumber(Product $currentProduct, string $productNumber): bool
    {
        if (!$currentProduct->hasAttribute('b2b_ordernumber')) {
            return false;
        }

        $customOrderNumber = $currentProduct->getAttribute('b2b_ordernumber')->get('custom_ordernumber');

        return $customOrderNumber === $productNumber;
    }

    /**
     * @param LineItemReference $reference
     * @param Product $product
     */
    private function determinePriceForReference(LineItemReference $reference, Product $product)
    {
        if (!$reference->quantity) {
            throw new UnsupportedQuantityException('The quantity is not supported');
        }

        $price = $this->getPriceRuleForQuantity($product, $reference->quantity);

        $reference->amount = $price->getCalculatedPrice();
        $reference->amountNet = ($price->getRule()->getPrice() * $this->getCurrencyFactor());
    }

    /**
     * @param Product $product
     * @param int$quantity
     * @return Price
     */
    private function getPriceRuleForQuantity(Product $product, int $quantity): Price
    {
        $prices = $product->getPrices();

        $this->sortPrices($prices);

        return $this->getSuitablePrice($prices, $quantity);
    }

    /**
     * @param array $prices
     * @param int $quantity
     * @return Price
     */
    private function getSuitablePrice(array $prices, int $quantity): Price
    {
        /** @var Price $price */
        foreach ($prices as $price) {
            $to = $price->getRule()->getTo();

            if (!$to) {
                return $price;
            }

            if ($quantity > $to) {
                continue;
            }

            return $price;
        }
    }

    /**
     * @param array $prices
     */
    private function sortPrices(array $prices)
    {
        usort($prices, function ($firstPrice, $secondPrice) {
            $firstTo = $firstPrice->getRule()->getTo();
            $secondTo = $secondPrice->getRule()->getTo();

            if ($firstTo === $secondTo) {
                return 0;
            }

            if ($firstTo < $secondTo || $secondTo === null) {
                return 1;
            }

            return -1;
        });
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
     * @internal
     * @return string
     */
    protected function getCustomerGroupKey(): string
    {
        try {
            $shop = Shopware()->Container()->get('shop');

            if (!$shop) {
                throw new ServiceNotFoundException('Unable to load shop through container');
            }

            $context = $this->contextService->getShopContext();

            return $context->getCurrentCustomerGroup()->getKey();
        } catch (ServiceNotFoundException $e) {
            return $this->modelManager
                ->getRepository(Shop::class)
                ->getDefault()
                ->getCustomerGroup()->getKey();
        }
    }

    /**
     * @internal
     */
    protected function getCurrencyId(): int
    {
        try {
            /** @var Shop $shop */
            $shop = Shopware()->Container()->get('shop');

            if (!$shop) {
                throw new ServiceNotFoundException('Unable to load shop through container');
            }

            $context = $this->contextService->getShopContext();

            return $context->getCurrency()->getId();
        } catch (ServiceNotFoundException $e) {
            return $this->modelManager
                ->getRepository(Currency::class)
                ->findOneBy(['default' => true])
                ->getId();
        }
    }

    /**
     * @internal
     * @return float
     */
    protected function getCurrencyFactor(): float
    {
        try {
            /** @var Shop $shop */
            $shop = Shopware()->Container()->get('shop');

            if (!$shop) {
                throw new ServiceNotFoundException('Unable to load shop through container');
            }

            $context = $this->contextService->getShopContext();

            return $context->getCurrency()->getFactor();
        } catch (ServiceNotFoundException $e) {
            return $this->modelManager
                ->getRepository(Currency::class)
                ->findOneBy(['default' => 1])
                ->getFactor();
        }
    }
}
