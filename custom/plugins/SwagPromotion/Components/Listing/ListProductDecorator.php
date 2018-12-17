<?php
/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

namespace SwagPromotion\Components\Listing;

use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Components\Plugin\CachedConfigReader;
use SwagCustomProducts\Components\Services\TemplateServiceInterface;
use SwagPromotion\Struct\Promotion;

/**
 * ListProductDecorator decorated the core's ListProductService
 */
class ListProductDecorator implements ListProductServiceInterface
{
    /**
     * @var ListProductServiceInterface
     */
    protected $decorate;

    /**
     * @var PromotionProductHighlighter
     */
    private $productHighlighter;

    /**
     * @var TemplateServiceInterface
     */
    private $templateService;

    /**
     * @var string
     */
    private $priceDisplaying;

    /**
     * ListProductDecorator constructor.
     *
     * @param string                        $pluginName
     * @param ListProductServiceInterface   $decorate
     * @param PromotionProductHighlighter   $productHighlighter
     * @param CachedConfigReader            $configReader
     * @param TemplateServiceInterface|null $templateService
     */
    public function __construct(
        $pluginName,
        ListProductServiceInterface $decorate,
        PromotionProductHighlighter $productHighlighter,
        CachedConfigReader $configReader,
        TemplateServiceInterface $templateService = null
    ) {
        $this->decorate = $decorate;
        $this->productHighlighter = $productHighlighter;
        $this->templateService = $templateService;

        $config = $configReader->getByPluginName($pluginName);
        $this->priceDisplaying = $config['promotionPriceDisplaying'];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $numbers, Struct\ProductContextInterface $context)
    {
        $products = $this->decorate->getList($numbers, $context);

        if (!Shopware()->Container()->has('shop')) {
            return $products;
        }

        $promotions = $this->productHighlighter->getProductPromotions($products, $context);

        foreach ($products as $number => $product) {
            if (!isset($promotions[$number])) {
                continue;
            }

            $remainingPromotions = $this->modifyPrices($product, $promotions[$number]->promotions);
            $promotions[$number]->promotions = $remainingPromotions;

            $product->addAttribute('promotion', $promotions[$number]);
            $product->setAllowBuyInListing(false);
        }

        return $products;
    }

    /**
     * {@inheritdoc}
     */
    public function get($number, Struct\ProductContextInterface $context)
    {
        $product = $this->getList([$number], $context);

        return array_shift($product);
    }

    /**
     * Will modify the prices of the ListProduct struct in place to show the discount to the customer
     * Also only promotions will be returned, that have valid discounts
     *
     * @param Struct\ListProduct $product
     * @param Promotion[]        $promotionsForProduct
     *
     * @return mixed
     */
    private function modifyPrices(Struct\ListProduct $product, $promotionsForProduct)
    {
        // this is a little hack for prevent a wrong calculated Price
        if ($product->hasState('promotion_price_is_calculated')) {
            return $promotionsForProduct;
        }
        // this belongs to the hack above
        $product->addState('promotion_price_is_calculated');

        $prices = $product->getPrices();
        $newPricesAndPromotions = $this->calculateNewPrice($prices, $promotionsForProduct, $product);
        $product->setPrices($newPricesAndPromotions['newPrices']);
        $promotionsForProduct = $newPricesAndPromotions['validPromotions'];

        $cheapestPrice = $product->getCheapestPrice();
        $newCheapestPrice = $this->calculateNewPrice([$cheapestPrice], $promotionsForProduct, $product);
        $product->setCheapestPrice(array_shift($newCheapestPrice['newPrices']));

        $cheapestUnitPrice = $product->getCheapestUnitPrice();
        $newCheapestUnitPrice = $this->calculateNewPrice(
            [$cheapestUnitPrice],
            $promotionsForProduct,
            $product
        );
        $product->setCheapestUnitPrice(array_shift($newCheapestUnitPrice['newPrices']));

        return $promotionsForProduct;
    }

    /**
     * Calculates the new price for the product and remove promotions, that have a discount higher than the
     * product's price
     *
     * @param Struct\Product\Price[] $prices
     * @param Promotion[]            $promotionsForCalculation
     * @param Struct\ListProduct     $product
     *
     * @return array
     */
    private function calculateNewPrice(array $prices, array $promotionsForCalculation, Struct\ListProduct $product)
    {
        $promotionProductAbsoluteCount = 0;

        foreach ($prices as $price) {
            $initialPrice = $price->getCalculatedPrice();
            $newCalculatedPrice = $price->getCalculatedPrice();
            $stopProcessingPriority = null;
            $exclusivePromotion = false;
            foreach ($promotionsForCalculation as $key => $promotion) {
                //unset all other promotions if there is an exclusive promotion
                if ($exclusivePromotion) {
                    unset($promotionsForCalculation[$key]);
                    continue;
                }

                // if $stopProcessingPriority is set,
                // all promotions with lower priority get unset and are ignored for price calculation
                if ($stopProcessingPriority !== null && $stopProcessingPriority > $promotion->priority) {
                    unset($promotionsForCalculation[$key]);
                    continue;
                }

                // don't calculate new price if there is a step greater 1 configured,
                // because the displayed price should be on base of 1 piece
                if ($promotion->step > 1) {
                    continue;
                }

                if ($promotion->type === 'product.percentage') {
                    $currentNewCalculatedPrice = $newCalculatedPrice - ($initialPrice * $promotion->amount / 100);
                    if ($currentNewCalculatedPrice > 0.0) {
                        $newCalculatedPrice -= $initialPrice * $promotion->amount / 100;
                    } else {
                        unset($promotionsForCalculation[$key]);
                    }
                } elseif ($promotion->type === 'product.absolute') {
                    ++$promotionProductAbsoluteCount;
                    $currentNewCalculatedPrice = $newCalculatedPrice - $promotion->amount;
                    if ($currentNewCalculatedPrice > 0.0) {
                        $newCalculatedPrice -= $promotion->amount;
                    } elseif ($currentNewCalculatedPrice === 0.0 && $promotionProductAbsoluteCount === 1) {
                        $newCalculatedPrice = 0.01;
                    } else {
                        unset($promotionsForCalculation[$key]);
                    }
                }

                // if this promotion quits the processing, save the priority to skip promotions with lower priority
                if ($promotion->stopProcessing) {
                    $stopProcessingPriority = $promotion->priority;
                }

                if ($promotion->exclusive) {
                    $exclusivePromotion = true;
                }
            }

            if ($this->priceDisplaying === 'normal') {
                continue;
            }

            if ($newCalculatedPrice === $price->getCalculatedPrice()) {
                continue;
            }

            if ($this->isCustomProduct($product->getId())) {
                continue;
            }

            if ($this->priceDisplaying === 'pseudo') {
                $price->setCalculatedPseudoPrice($price->getCalculatedPrice());
            }

            $price->setCalculatedPrice($newCalculatedPrice);
            if (!$product->hasState('hasNewPromotionProductPrice')) {
                $product->addState('hasNewPromotionProductPrice');
            }
            if ($price->getCalculatedReferencePrice()) {
                $unit = $price->getUnit()->getReferenceUnit() / $price->getUnit()->getPurchaseUnit();
                $newUnitPrice = $unit * $newCalculatedPrice;
                $price->setCalculatedReferencePrice($newUnitPrice);
            }
        }

        $promotionsForCalculation = array_values($promotionsForCalculation);

        return ['newPrices' => $prices, 'validPromotions' => $promotionsForCalculation];
    }

    /**
     * @param int $productId
     *
     * @return bool
     */
    private function isCustomProduct($productId)
    {
        if ($this->templateService === null) {
            return false;
        }

        $customProductsTemplate = $this->templateService->getTemplateByProductId($productId, false);
        if ($customProductsTemplate === null) {
            return false;
        }

        return (bool) $customProductsTemplate['active'];
    }
}
