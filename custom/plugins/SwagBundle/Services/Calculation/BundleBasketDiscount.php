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

use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Components\Cart\BasketHelperInterface;
use Shopware\Components\Cart\ProportionalTaxCalculatorInterface;
use Shopware\Components\Cart\Struct\DiscountContext;
use Shopware\Components\Cart\Struct\Price;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Basket;
use SwagBundle\Components\BundleBasketInterface;
use SwagBundle\Components\BundleComponentInterface;
use SwagBundle\Models\Bundle;
use SwagBundle\Models\Repository;
use SwagBundle\Services\CustomerGroupServiceInterface;
use SwagBundle\Services\Dependencies\ProviderInterface;
use SwagBundle\Services\Discount\BundleDiscountServiceInterface;
use SwagBundle\Services\FullBundleServiceInterface;

class BundleBasketDiscount implements BundleBasketDiscountInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Repository
     */
    private $bundleRepository;

    /**
     * @var ProviderInterface
     */
    private $dependenciesProvider;

    /**
     * @var FullBundleServiceInterface
     */
    private $fullBundleService;

    /**
     * @var CustomerGroupServiceInterface
     */
    private $customerGroupService;

    /**
     * @var \Shopware_Components_Config
     */
    private $shopwareConfig;

    /**
     * @var BasketHelperInterface
     */
    private $bundleBasketHelper;

    /**
     * @var ProportionalTaxCalculatorInterface
     */
    private $calculator;

    /**
     * @var BundleDiscountServiceInterface
     */
    private $bundleDiscountService;

    /**
     * @param ModelManager                       $modelManager
     * @param ProviderInterface                  $dependenciesProvider
     * @param FullBundleServiceInterface         $fullBundleService
     * @param CustomerGroupServiceInterface      $customerGroupService
     * @param \Shopware_Components_Config        $shopwareConfig
     * @param BasketHelperInterface              $bundleBasketHelper
     * @param ProportionalTaxCalculatorInterface $calculator
     * @param BundleDiscountServiceInterface     $bundleDiscountService
     */
    public function __construct(
        ModelManager $modelManager,
        ProviderInterface $dependenciesProvider,
        FullBundleServiceInterface $fullBundleService,
        CustomerGroupServiceInterface $customerGroupService,
        \Shopware_Components_Config $shopwareConfig,
        BasketHelperInterface $bundleBasketHelper,
        ProportionalTaxCalculatorInterface $calculator,
        BundleDiscountServiceInterface $bundleDiscountService
    ) {
        $this->modelManager = $modelManager;
        $this->bundleRepository = $modelManager->getRepository(Bundle::class);
        $this->dependenciesProvider = $dependenciesProvider;
        $this->fullBundleService = $fullBundleService;
        $this->customerGroupService = $customerGroupService;
        $this->shopwareConfig = $shopwareConfig;
        $this->bundleBasketHelper = $bundleBasketHelper;
        $this->calculator = $calculator;
        $this->bundleDiscountService = $bundleDiscountService;
    }

    /**
     * {@inheritdoc}
     */
    public function updateBundleBasketDiscount(array $basketItems, $currencyFactor)
    {
        $this->modelManager->clear();
        $session = $this->dependenciesProvider->getSession();
        $sArticles = $this->dependenciesProvider->getArticlesModule();

        $isTaxFree = $session->get('taxFree');
        if (!$isTaxFree && $this->shopwareConfig->get('proportionalTaxCalculation')) {
            $this->handleProportionalTaxCalculation($basketItems, $currencyFactor);

            return;
        }

        if ($isTaxFree && $this->shopwareConfig->get('proportionalTaxCalculation')) {
            $this->handleTaxFree($basketItems, $currencyFactor);

            return;
        }

        /** @var Basket $basketItem */
        foreach ($basketItems as $basketItem) {
            $bundle = $this->getBundle($basketItem);
            if ($bundle === false) {
                continue;
            }

            $taxRate = (float) $sArticles->getTaxRateByConditions($bundle->getArticle()->getTax()->getId());

            $basketRow = $this->setNewValues(
                $basketItem->getId(),
                $currencyFactor,
                $taxRate,
                $this->getLatestGrossPrice($bundle),
                $this->getLatestNetPrice($bundle)
            );

            $this->modelManager->persist($basketRow);
        }

        $this->modelManager->flush();
    }

    /**
     * Use the latest net price.
     *
     * @param Bundle $bundle
     *
     * @return mixed
     */
    private function getLatestNetPrice(Bundle $bundle)
    {
        return abs($bundle->getDiscount()['net']) * -1; //-1 for the negative value
    }

    /**
     * @param Bundle $bundle
     *
     * @return float
     */
    private function getLatestGrossPrice(Bundle $bundle)
    {
        return abs($bundle->getDiscount()['gross']) * -1; //-1 for the negative value
    }

    /**
     * @param Basket $basketItem
     *
     * @return Bundle|false
     */
    private function getBundle(Basket $basketItem)
    {
        $bundle = $this->bundleRepository->findOneBy([
            'number' => $basketItem->getOrderNumber(),
        ]);

        if (!$bundle instanceof Bundle) {
            return false;
        }

        //Recalculate the bundle and update the prices corresponding to the current customer group
        $bundle = $this->fullBundleService->getCalculatedBundle($bundle, '', true, $basketItem);

        // \SwagBundle\Services\FullBundleService::getCalculatedBundle returns an array if an error occurred. Therefore it must be checked again.
        if (!$bundle instanceof Bundle) {
            return false;
        }

        return $bundle;
    }

    /**
     * @param int    $basketId
     * @param string $currencyFactor
     * @param float  $taxRate
     * @param float  $price
     * @param float  $netPrice
     *
     * @return Basket
     */
    private function setNewValues($basketId, $currencyFactor, $taxRate, $price, $netPrice)
    {
        /** @var Basket $basketRow */
        $basketRow = $this->modelManager->find(Basket::class, $basketId);
        $basketRow->setMode(BundleBasketInterface::BUNDLE_DISCOUNT_BASKET_MODE);
        $basketRow->setTaxRate($taxRate);
        $basketRow->setNetPrice($netPrice);
        $basketRow->setPrice($price);
        $basketRow->setCurrencyFactor($currencyFactor);

        if ($this->customerGroupService->useNetPriceInBasket()) {
            $basketRow->setPrice($netPrice);
        }

        return $basketRow;
    }

    /**
     * @param Basket $firstBundleDiscount
     *
     * @return DiscountContext
     */
    private function getDiscountContext(Basket $firstBundleDiscount)
    {
        $discountContext = new DiscountContext(
            $firstBundleDiscount->getSessionId(),
            null,
            null,
            null,
            null,
            null,
            null,
            null
        );
        $discountContext->addAttribute('bundleDiscount', new Attribute([
            'bundleMainProductBasketId' => $firstBundleDiscount->getAttribute()->getBundlePackageId(),
            'bundleDiscountOrderNumber' => $firstBundleDiscount->getOrderNumber(),
        ]));

        return $discountContext;
    }

    /**
     * @param $bundleDiscount
     *
     * @return float
     */
    private function getTaxRate(Basket $bundleDiscount)
    {
        $taxId = $this->modelManager->getConnection()->executeQuery(
            'SELECT `id` FROM `s_core_tax` WHERE `tax` = :taxRate',
            ['taxRate' => $bundleDiscount->getTaxRate()]
        )
            ->fetchColumn();
        if ($taxId === false) {
            $taxId = $this->modelManager->getConnection()->executeQuery(
                'SELECT `groupID` FROM `s_core_tax_rules` WHERE `tax` = :taxRate',
                ['taxRate' => $bundleDiscount->getTaxRate()]
            )
                ->fetchColumn();
        }

        return (float) $this->dependenciesProvider->getArticlesModule()->getTaxRateByConditions($taxId);
    }

    /**
     * @param DiscountContext $discountContext
     * @param Bundle          $bundle
     * @param float           $bundleDiscountValue
     *
     * @return Price[]
     */
    private function getDiscounts(DiscountContext $discountContext, Bundle $bundle, $bundleDiscountValue)
    {
        $prices = $this->bundleBasketHelper->getPositionPrices($discountContext);

        if ($bundle->getDiscountType() === BundleComponentInterface::ABSOLUTE_DISCOUNT) {
            $discounts = $this->calculator->calculate(
                $bundleDiscountValue,
                $prices,
                $this->customerGroupService->useNetPriceInBasket()
            );
        } else {
            $discounts = $this->calculator->recalculatePercentageDiscount(
                $bundle->getDiscount()['percentage'],
                $prices,
                $this->customerGroupService->useNetPriceInBasket()
            );
        }

        return $discounts;
    }

    /**
     * @param Basket[] $basketItems
     *
     * @return array
     */
    private function sortBasketItemDiscounts(array $basketItems)
    {
        $result = [];

        /** @var Basket $basketItem */
        foreach ($basketItems as $basketItem) {
            if (!isset($result[$basketItem->getAttribute()->getBundlePackageId()])) {
                $result[$basketItem->getAttribute()->getBundlePackageId()];
            }

            $result[$basketItem->getAttribute()->getBundlePackageId()][] = $basketItem;
        }

        return $result;
    }

    /**
     * @param Basket[] $basketItems
     * @param float    $currencyFactor
     */
    private function handleTaxFree(array $basketItems, $currencyFactor)
    {
        $basketItems = $this->sortBasketItemDiscounts($basketItems);

        foreach ($basketItems as $bundleDiscounts) {
            $index = 0;
            while (count($bundleDiscounts) > 1) {
                $entity = $this->modelManager->merge($bundleDiscounts[$index]);
                $this->modelManager->remove($entity);

                unset($bundleDiscounts[$index]);
                ++$index;
            }

            $this->modelManager->flush();

            /** @var Basket $basketItem */
            $basketItem = end($bundleDiscounts);
            $bundle = $this->getBundle($basketItem);

            $basketRow = $this->setNewValues(
                $basketItem->getId(),
                $currencyFactor,
                0,
                $this->getLatestGrossPrice($bundle),
                $this->getLatestNetPrice($bundle)
            );

            $pattern = '/\s?\(.*\)/';
            $newName = preg_replace($pattern, '', $basketItem->getArticleName());
            $basketRow->setArticleName($newName);

            $this->modelManager->persist($basketRow);
        }
        $this->modelManager->flush();
    }

    /**
     * @param Basket[] $basketItems
     * @param float    $currencyFactor
     */
    private function handleProportionalTaxCalculation(array $basketItems, $currencyFactor)
    {
        $groupedBasketItems = [];
        /** @var Basket $basketItem */
        foreach ($basketItems as $basketItem) {
            $groupedBasketItems[$basketItem->getOrderNumber() . '-' . $basketItem->getAttribute()->getBundlePackageId()][] = $basketItem;
        }

        /** @var Basket[] $groupedBasketItem */
        foreach ($groupedBasketItems as $groupedBasketItem) {
            $bundleDiscountValue = 0.0;
            foreach ($groupedBasketItem as $bundleDiscount) {
                $bundleDiscountValue += $bundleDiscount->getPrice();
            }

            $firstBundleDiscount = $groupedBasketItem[0];
            $bundle = $this->getBundle($firstBundleDiscount);
            if ($bundle === false) {
                continue;
            }

            $discountContext = $this->getDiscountContext($firstBundleDiscount);
            $discounts = $this->getDiscounts($discountContext, $bundle, $bundleDiscountValue);

            foreach ($groupedBasketItem as $bundleDiscount) {
                $taxRate = $this->getTaxRate($bundleDiscount);
                $discountForTaxRate = null;
                foreach ($discounts as $discount) {
                    if ((float) $discount->getTaxRate() === $taxRate) {
                        $discountForTaxRate = $discount;
                        break;
                    }
                }
                if ($discountForTaxRate === null) {
                    continue;
                }

                $basketRow = $this->setNewValues(
                    $bundleDiscount->getId(),
                    $currencyFactor,
                    $taxRate,
                    abs($discountForTaxRate->getPrice()) * -1,
                    abs($discountForTaxRate->getNetPrice()) * -1
                );

                $oldDiscountName = $basketRow->getArticleName();
                $newDiscountName = '(' . $taxRate . '%)';
                $taxRateRegEx = '/\(\d+(\.?\d+)?%\)/'; // matches "(7%)", "(19.34%)"

                $basketRow->setArticleName(preg_replace($taxRateRegEx, $newDiscountName, $oldDiscountName));

                $this->modelManager->persist($basketRow);
            }

            $this->modelManager->flush();
        }
    }
}
