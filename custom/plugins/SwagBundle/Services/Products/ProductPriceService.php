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

namespace SwagBundle\Services\Products;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Price;
use Shopware\Models\Article\Unit;
use Shopware\Models\Customer\Group;
use SwagBundle\Services\Dependencies\ProviderInterface;

class ProductPriceService implements ProductPriceServiceInterface
{
    /**
     * @var ProviderInterface
     */
    private $dependenciesProvider;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param ProviderInterface $dependenciesProvider
     * @param ModelManager      $modelManager
     */
    public function __construct(
        ProviderInterface $dependenciesProvider,
        ModelManager $modelManager
    ) {
        $this->dependenciesProvider = $dependenciesProvider;
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductPrices(Detail $productVariant, Group $customerGroup, $quantity, $fallback = null)
    {
        $shop = $this->dependenciesProvider->getShop();
        if ($fallback === null) {
            $fallback = $shop->getCustomerGroup()->getKey();
        }

        $prices = $this->getProductVariantPriceForCustomerGroup(
            $productVariant->getId(),
            $customerGroup->getKey(),
            $fallback,
            AbstractQuery::HYDRATE_OBJECT
        );

        $currentPrice = null;

        /** @var Price $price */
        foreach ($prices as $price) {
            if (!is_numeric($price->getTo())) {
                $currentPrice = $price;
                break;
            }

            if ($quantity >= $price->getFrom() && $quantity <= $price->getTo()) {
                $currentPrice = $price;
                break;
            }
        }

        //if no price founded,
        if (!$currentPrice instanceof Price) {
            return false;
        }

        return [
            'net' => $this->calculateNetPrice($currentPrice->getPrice(), $productVariant, $quantity),
            'gross' => $this->calculateGrossPrice($currentPrice->getPrice(), $productVariant, $quantity),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getBasePriceOfVariant(Detail $variant)
    {
        $sArticleCoreModule = $this->dependenciesProvider->getArticlesModule();

        if ($variant->getUnit() instanceof Unit && $variant->getPurchaseUnit() > 0 && $variant->getReferenceUnit()) {
            $price = $variant->getPrices()->first();

            $basePrice = $price / $variant->getPurchaseUnit() * $variant->getReferenceUnit();

            return [
                'unit' => $sArticleCoreModule->sGetUnit($variant->getUnit()->getId()),
                'minPurchase' => $variant->getMinPurchase(),
                'maxPurchase' => $variant->getMaxPurchase(),
                'purchaseUnit' => (float) $variant->getPurchaseUnit(),
                'refrenceUnit' => (float) $variant->getReferenceUnit(),
                'referencePrice' => [
                    'numeric' => $basePrice,
                    'display' => $sArticleCoreModule->sFormatPrice($basePrice),
                ],
            ];
        }

        return [];
    }

    /**
     * Get the prices for the passed product id and customer group key.
     *
     * @param int    $productVariantId Contains the unique product variant identifier
     * @param string $customerGroupKey Contains the group key for the customer group
     * @param string $fallbackKey      Contains an fallback group key for the customer group
     * @param int    $hydrationMode    the hydration mode parameter control the result data type
     *
     * @return array
     */
    private function getProductVariantPriceForCustomerGroup(
        $productVariantId,
        $customerGroupKey,
        $fallbackKey = 'EK',
        $hydrationMode = AbstractQuery::HYDRATE_ARRAY
    ) {
        //no group key passed?
        if (empty($customerGroupKey)) {
            $customerGroupKey = $fallbackKey;
        }

        $builder = $this->getPriceQueryBuilder();
        $builder->setParameters([
            'productVariantId' => $productVariantId,
            'customerGroupKey' => $customerGroupKey,
        ]);
        $prices = $builder->getQuery()->getResult($hydrationMode);

        // Implements an additional fallback key which is used,
        // when the fallback customerGroup for the current shop is not EK.
        // So after checking the current customerGroup and the shop's fallback group
        // also EK is checked for prices
        if (empty($prices) && $customerGroupKey === $fallbackKey && $customerGroupKey !== 'EK') {
            $fallbackKey = 'EK';
        }

        if (empty($prices) && $customerGroupKey !== $fallbackKey) {
            return $this->getProductVariantPriceForCustomerGroup(
                $productVariantId,
                $fallbackKey,
                $fallbackKey,
                $hydrationMode
            );
        }

        return $prices;
    }

    /**
     * Returns an query builder object which creates an query to select all variant prices
     * for a specify customer group.
     *
     * @return QueryBuilder
     */
    private function getPriceQueryBuilder()
    {
        $builder = $this->modelManager->createQueryBuilder();

        return $builder->select(['prices'])
            ->from(Price::class, 'prices')
            ->where('prices.articleDetailsId = :productVariantId')
            ->andWhere('prices.customerGroupKey = :customerGroupKey')
            ->orderBy('prices.from', 'ASC');
    }

    /**
     * Internal helper function which calculates the net price for the passed price value and the passed product.
     *
     * @param float  $price
     * @param Detail $productVariant
     * @param int    $quantity
     *
     * @return float
     */
    private function calculateNetPrice($price, Detail $productVariant, $quantity)
    {
        $taxValue = $this->dependenciesProvider->getArticlesModule()->sCalculatingPriceNum(
            $price * $quantity,
            $productVariant->getArticle()->getTax()->getTax(),
            false,
            true,
            $productVariant->getArticle()->getTax()->getId(),
            false,
            $this->modelManager->toArray($productVariant)
        );

        return $taxValue;
    }

    /**
     * Internal helper function which calculates the gross price for the passed price value and the passed product.
     *
     * @param float  $price
     * @param Detail $productVariant
     * @param int    $quantity
     *
     * @return float
     */
    private function calculateGrossPrice($price, Detail $productVariant, $quantity)
    {
        //calculates the gross price for the selected product
        $taxValue = $this->dependenciesProvider->getArticlesModule()->sCalculatingPriceNum(
            $price * $quantity,
            $productVariant->getArticle()->getTax()->getTax(),
            false,
            false,
            $productVariant->getArticle()->getTax()->getId(),
            false,
            $this->modelManager->toArray($productVariant)
        );

        return $taxValue;
    }
}
