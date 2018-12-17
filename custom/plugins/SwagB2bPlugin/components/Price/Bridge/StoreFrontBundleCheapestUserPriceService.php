<?php  declare(strict_types=1);

namespace Shopware\B2B\Price\Bridge;

use Shopware\B2B\Price\Framework\PriceEntity;
use Shopware\B2B\Price\Framework\PriceRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\Bundle\StoreFrontBundle\Service\CheapestPriceServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware_Components_Config;

class StoreFrontBundleCheapestUserPriceService implements CheapestPriceServiceInterface
{
    /**
     * @var CheapestPriceServiceInterface
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
     * @var bool
     */
    private $useCheapestGraduation;

    /**
     * @param CheapestPriceServiceInterface $service
     * @param PriceRepository $priceRepository
     * @param StoreFrontBundlePriceRuleFactory $priceRuleFactory
     * @param AuthenticationService $authenticationService
     * @param Shopware_Components_Config $config
     */
    public function __construct(
        CheapestPriceServiceInterface $service,
        PriceRepository $priceRepository,
        StoreFrontBundlePriceRuleFactory $priceRuleFactory,
        AuthenticationService $authenticationService,
        Shopware_Components_Config $config
    ) {
        $this->decorated = $service;
        $this->priceRuleFactory = $priceRuleFactory;
        $this->priceRepository = $priceRepository;
        $this->authenticationService = $authenticationService;
        $this->useCheapestGraduation = (bool) $config->get('useLastGraduationForCheapestPrice');
    }

    /**
     * {@inheritdoc}
     */
    public function get(ListProduct $product, ProductContextInterface $context)
    {
        $cheapestPrices = $this->getList([$product], $context);

        return array_shift($cheapestPrices);
    }

    /**
     * @param ListProduct[] $products
     * @param ProductContextInterface $context
     * @return array| BaseProduct[]|PriceRule[]
     */
    public function getList($products, ProductContextInterface $context): array
    {
        $priceRules = $this->decorated->getList($products, $context);

        if (!$this->authenticationService->isB2b()) {
            return $priceRules;
        }

        $debtorId = $this->authenticationService->getIdentity()->getOwnershipContext()->shopOwnerUserId;

        $customerPrices = $this->priceRepository->fetchPricesByDebtorIdAndOrderNumber($debtorId, array_keys($priceRules));

        return $this->getAssignedCustomerPrices($priceRules, $customerPrices);
    }

    /**
     * @param array $priceRules
     * @param array $customerPrices
     * @return array
     */
    private function getAssignedCustomerPrices(array $priceRules, array $customerPrices): array
    {
        $assignedPriceRules = $priceRules;

        foreach ($assignedPriceRules as $number => $rule) {
            $pricesForOrderNumber = $this->getCustomerPricesForOrderNumber((string) $number, $rule, $customerPrices);

            if (empty($pricesForOrderNumber)) {
                continue;
            }

            if ($this->useCheapestGraduation) {
                $assignedPriceRules[$number] = array_pop($pricesForOrderNumber);
                continue;
            }

            $assignedPriceRules[$number] = array_shift($pricesForOrderNumber);
        }

        return $assignedPriceRules;
    }

    /**
     * @param string $number
     * @param PriceRule $rule
     * @param PriceEntity[] $customerPrices
     * @return array
     */
    private function getCustomerPricesForOrderNumber(string $number, PriceRule $rule, array $customerPrices): array
    {
        $pricesForOrderNumber = [];

        foreach ($customerPrices as $customerPrice) {
            if ($number !== $customerPrice->orderNumber) {
                continue;
            }

            $pricesForOrderNumber[] = $this->priceRuleFactory->create($customerPrice, $rule->getCustomerGroup(), $rule->getUnit());
        }

        return $pricesForOrderNumber;
    }
}
