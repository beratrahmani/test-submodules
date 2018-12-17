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

use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagPromotion\Components\BasketContextBuilder;
use SwagPromotion\Components\DataProvider\ProductDataProvider;
use SwagPromotion\Components\ProductMatcher;
use SwagPromotion\Components\Rules\RuleBuilder;
use SwagPromotion\Components\Services\DependencyProviderInterface;
use SwagPromotion\Models\Repository\Repository as PromotionRepository;
use SwagPromotion\Struct\ListProduct\PromotionContainerStruct;
use SwagPromotion\Struct\Promotion;

/**
 * PromotionProductHighlighter will add a promotion struct to ListProducts,
 * so products with promotions can be highlighted
 */
class PromotionProductHighlighter
{
    /**
     * @var ProductDataProvider
     */
    protected $productDataProvider;

    /**
     * @var PromotionRepository
     */
    private $promotionRepository;

    /**
     * @var ProductMatcher
     */
    private $productMatcher;

    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @var BasketContextBuilder
     */
    private $basketContextBuilder;

    /**
     * PromotionProductHighlighter constructor.
     *
     * @param ProductDataProvider         $productDataProvider
     * @param PromotionRepository         $promotionRepository
     * @param ProductMatcher              $productMatcher
     * @param DependencyProviderInterface $dependencyProvider
     * @param BasketContextBuilder        $basketContextBuilder
     */
    public function __construct(
        ProductDataProvider $productDataProvider,
        PromotionRepository $promotionRepository,
        ProductMatcher $productMatcher,
        DependencyProviderInterface $dependencyProvider,
        BasketContextBuilder $basketContextBuilder
    ) {
        $this->productDataProvider = $productDataProvider;
        $this->promotionRepository = $promotionRepository;
        $this->productMatcher = $productMatcher;
        $this->dependencyProvider = $dependencyProvider;
        $this->basketContextBuilder = $basketContextBuilder;
    }

    /**
     * Returns all promotion containers for a specified array of products.
     *
     * @param ListProduct[]        $products
     * @param ShopContextInterface $context
     *
     * @return PromotionContainerStruct[]
     */
    public function getProductPromotions(array $products, ShopContextInterface $context)
    {
        $tmp = [];

        // Build array for later product context query
        foreach ($products as $product) {
            $number = $product->getNumber();
            $tmp[$number] = [
                'quantity' => $product->getCheapestPrice()->getUnit()->getMinPurchase() ?: 1,
                'ordernumber' => $number,
                'price' => $product->getCheapestPrice()->getCalculatedPrice(),
            ];
        }

        // get product context objects to perform promotion checks
        $result = $this->productDataProvider->get(array_column($tmp, 'quantity', 'ordernumber'));
        foreach ($result as $number => $article) {
            $result[$number]['price'] = $tmp[$number]['price'];
        }

        // get all promotions for current user / shop / customerGroup combination ordered by priority
        $promotions = $this->promotionRepository->getActivePromotions(
            $context->getCurrentCustomerGroup()->getId(),
            $context->getShop()->getId()
        );

        $customerData = [];
        $customerId = $this->dependencyProvider->getSession()->get('sUserId');
        if ($customerId !== null) {
            $customerData = $this->basketContextBuilder->getCustomerData($customerId);
        }

        $resultStructs = [];
        /** @var Promotion $promotion */
        foreach ($promotions as $promotion) {
            // clone the promotion as the highlighter is only for displaying and should not impact other parts
            $promotion = clone $promotion;
            $matches = $result;
            if (strpos($promotion->type, 'product.') === 0) {
                $matches = $this->productMatcher->getMatchingProducts($result, $promotion->applyRules);
            } elseif (strpos($promotion->type, 'basket.') === 0) {
                continue;
            }

            foreach ($promotion->rules as &$ruleContainer) {
                foreach ($ruleContainer as $ruleName => $rule) {
                    if (stripos($ruleName, 'customer') !== false) {
                        if ($customerId === null) {
                            // Do not show the promotion if the customer is not logged in,
                            // and a customer rule is declared for the promotion,
                            // because in this case it is not possible to determinate if the promotion would ever apply
                            continue 3;
                        }
                    } elseif (stripos($ruleName, 'basket') !== false) {
                        // Do not check for basket rules at this point, because they are reachable for the customer
                        unset($ruleContainer[$ruleName]);
                    }
                }
            }
            unset($ruleContainer);

            /** @var RuleBuilder $basketRuleBuilder */
            $basketRuleBuilder = $this->basketContextBuilder->getBasketRuleBuilder([], [], $customerData);

            // Evaluate the rules, skip if rules don't apply
            $rules = $basketRuleBuilder->fromArray($promotion->rules);
            if (!$rules->validate()) {
                continue;
            }

            foreach ($matches as $article) {
                $number = $article['ordernumber'];
                if (!isset($resultStructs[$number])) {
                    $resultStructs[$number] = new PromotionContainerStruct();
                }

                $resultStructs[$number]->promotions[] = $promotion;
            }
        }

        return $resultStructs;
    }
}
