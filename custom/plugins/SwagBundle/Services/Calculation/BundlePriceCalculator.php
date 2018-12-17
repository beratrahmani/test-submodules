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

namespace SwagBundle\Services\Calculation;

use Doctrine\ORM\AbstractQuery;
use InvalidArgumentException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Group;
use SwagBundle\Components\BundleComponentInterface;
use SwagBundle\Models\Bundle;
use SwagBundle\Models\Price;
use SwagBundle\Services\Dependencies\ProviderInterface;

class BundlePriceCalculator implements BundlePriceCalculatorInterface
{
    /**
     * @var ProviderInterface
     */
    private $dependenciesProvider;

    /**
     * @var CalculationRepositoryInterface
     */
    private $calculationRepository;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param ProviderInterface              $dependenciesProvider
     * @param CalculationRepositoryInterface $calculationRepository
     * @param ModelManager                   $modelManager
     */
    public function __construct(
        ProviderInterface $dependenciesProvider,
        CalculationRepositoryInterface $calculationRepository,
        ModelManager $modelManager
    ) {
        $this->dependenciesProvider = $dependenciesProvider;
        $this->calculationRepository = $calculationRepository;
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getBundlePrices(Bundle $bundle)
    {
        $prices = $this->calculationRepository->getRawBundlePrices($bundle->getId(), AbstractQuery::HYDRATE_OBJECT);

        //if no prices defined, return an error.
        if (empty($prices)) {
            return false;
        }

        $shop = $this->dependenciesProvider->getShop();

        $this->calculate($bundle, $prices, $shop->getCurrency()->getFactor());

        $bundle->getUpdatedPrices()->clear();
        foreach ($prices as $price) {
            $bundle->getUpdatedPrices()->add($price);
        }
        $this->modelManager->clear();

        return $bundle->getUpdatedPrices();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPrice(Bundle $bundle, Group $customerGroup = null)
    {
        $shop = $this->dependenciesProvider->getShop();
        if ($customerGroup === null) {
            $customerGroup = $shop->getCustomerGroup();
        }

        $price = $bundle->getPriceForCustomerGroup($customerGroup->getKey());

        if (!$price instanceof Price) {
            return false;
        }

        return $price;
    }

    /**
     * @param Bundle  $bundle
     * @param Price[] $prices
     * @param float   $currencyFactor
     *
     * @throws \InvalidArgumentException
     */
    private function calculate(Bundle $bundle, array $prices, $currencyFactor)
    {
        $total = $bundle->getTotalPrice();
        if ($total === null) {
            throw new InvalidArgumentException('The total price of the bundle can not be null.');
        }

        /** @var Price $bundlePrice */
        foreach ($prices as $bundlePrice) {
            if ($bundle->getDiscountType() === BundleComponentInterface::PERCENTAGE_DISCOUNT) {
                //first we set the percentage value from the price property to the expected percentage property
                $bundlePrice->setPercentage($bundlePrice->getPrice());

                //now we calculate the net price
                $bundlePrice->setNetPrice($this->calculateDiscount($total['net'], $bundlePrice));

                $bundlePrice->setGrossPrice($this->calculateDiscount($total['gross'], $bundlePrice));
            } else {
                //set the defined backend price as net price
                $bundlePrice->setNetPrice($bundlePrice->getPrice());

                $bundlePrice->setGrossPrice($bundlePrice->getPrice() / 100 * ($bundle->getArticle()->getTax()->getTax() + 100) * $currencyFactor);

                $bundlePrice->setPercentage($bundlePrice->getNetPrice() / ($total['net'] / 100) * $currencyFactor);
            }

            //check if the customer group prices should be displayed as gross or net prices
            if ($bundlePrice->getCustomerGroup()->getTax()) {
                $bundlePrice->setDisplayPrice($this->dependenciesProvider->getArticlesModule()->sFormatPrice($bundlePrice->getGrossPrice()));
            } else {
                $bundlePrice->setDisplayPrice($this->dependenciesProvider->getArticlesModule()->sFormatPrice($bundlePrice->getNetPrice()));
            }
        }
    }

    /**
     * @param float $price
     * @param Price $bundlePrice
     *
     * @return float|int
     */
    private function calculateDiscount($price, Price $bundlePrice)
    {
        return $price * (100 - $bundlePrice->getPercentage()) / 100;
    }
}
