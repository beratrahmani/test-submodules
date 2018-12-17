<?php declare(strict_types=1);

namespace Shopware\B2B\Price\Bridge;

use Shopware\B2B\Price\Framework\PriceEntity;
use Shopware\B2B\Price\Framework\PriceRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\Bundle\StoreFrontBundle\Service\GraduatedPricesServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;

class StoreFrontBundleGraduatedUserPricesService implements GraduatedPricesServiceInterface
{
    /**
     * @var GraduatedPricesServiceInterface
     */
    private $decorated;

    /**
     * @var StoreFrontBundlePriceRuleFactory
     */
    private $priceRuleFactory;

    /**
     * @var PriceRepository
     */
    private $priceRepository;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param GraduatedPricesServiceInterface $service
     * @param PriceRepository $priceRepository
     * @param StoreFrontBundlePriceRuleFactory $priceRuleFactory
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        GraduatedPricesServiceInterface $service,
        PriceRepository $priceRepository,
        StoreFrontBundlePriceRuleFactory $priceRuleFactory,
        AuthenticationService $authenticationService
    ) {
        $this->decorated = $service;
        $this->priceRuleFactory = $priceRuleFactory;
        $this->priceRepository = $priceRepository;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param ListProduct $product
     * @param ProductContextInterface $context
     * @return mixed
     */
    public function get(ListProduct $product, ProductContextInterface $context)
    {
        $graduatedPrices = $this->getList([$product], $context);

        return array_shift($graduatedPrices);
    }

    /**
     * @param ListProduct[] $products
     * @param ProductContextInterface $context
     * @return array|PriceRule[]
     */
    public function getList($products, ProductContextInterface $context): array
    {
        /** @var PriceRule[] $priceRules */
        $priceRules = $this->decorated->getList($products, $context);

        if (!$this->authenticationService->isB2b()) {
            return $priceRules;
        }

        $debtorId = $this->authenticationService->getIdentity()->getOwnershipContext()->shopOwnerUserId;

        $customPrices = $this->priceRepository->fetchPricesByDebtorIdAndOrderNumber($debtorId, array_keys($priceRules));

        /** @var $coreRules PriceRule[] */
        foreach ($priceRules as $number => &$coreRules) {
            $coreRules = $this->getCustomRules($coreRules, (string) $number, $customPrices);
        }

        return $priceRules;
    }

    /**
     * @param PriceRule[] $coreRules
     * @param string $number
     * @param PriceEntity[] $prices
     * @return array
     */
    private function getCustomRules(array $coreRules, string $number, array $prices): array
    {
        $customRules = [];

        foreach ($prices as $price) {
            if ($number !== $price->orderNumber) {
                continue;
            }

            $userPriceRule = $this->priceRuleFactory->create(
                $price,
                $coreRules[0]->getCustomerGroup(),
                $coreRules[0]->getUnit()
            );
            $userPriceRule->setPseudoPrice($coreRules[0]->getPseudoPrice());

            $customRules[] = $userPriceRule;
        }

        if (empty($customRules)) {
            return $coreRules;
        }

        $this->checkForLastRuleAndAddItIfNot($customRules, $coreRules[0]->getPrice());
        $this->checkForFirstRuleFromOneAndAddItIfNot($customRules, $coreRules[0]->getPrice());

        return $customRules;
    }

    /**
     * @param array $customRules
     * @param mixed $price
     */
    private function checkForLastRuleAndAddItIfNot(array &$customRules, $price)
    {
        $lastRule = end($customRules);

        if ($lastRule->getTo() !== null) {
            $customRules[] = $this->priceRuleFactory->generateLastRuleByPriceRule($lastRule, $price);
        }
    }

    /**
     * @param array $customRules
     * @param mixed $price
     */
    private function checkForFirstRuleFromOneAndAddItIfNot(array &$customRules, $price)
    {
        $firstRule = $customRules[0];

        if ($firstRule->getFrom() > 1) {
            array_unshift($customRules, $this->priceRuleFactory->generateFirstRuleByPriceRule($firstRule, $price));
        }
    }
}
