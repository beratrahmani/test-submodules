<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework;

use Shopware\B2B\Cart\Framework\BlackListCartAccess;
use Shopware\B2B\Cart\Framework\CartAccessFactoryInterface;
use Shopware\B2B\Cart\Framework\CartAccessStrategyInterface;
use Shopware\B2B\ContingentGroup\Framework\ContingentGroupRepository;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class RestrictionCartAccessFactory implements CartAccessFactoryInterface
{
    /**
     * @var array
     */
    private $allowedTypes;

    /**
     * @var ContingentGroupRepository
     */
    private $contingentGroupRepository;

    /**
     * @var ContingentRuleRepository
     */
    private $contingentRuleRepository;

    /**
     * @var ContingentRuleTypeFactory
     */
    private $typeFactory;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param ContingentGroupRepository $contingentGroupRepository
     * @param ContingentRuleRepository $contingentRuleRepository
     * @param ContingentRuleTypeFactory $typeFactory
     * @param array $allowedTypes
     * @param CurrencyService $currencyService
     */
    public function __construct(
        ContingentGroupRepository $contingentGroupRepository,
        ContingentRuleRepository $contingentRuleRepository,
        ContingentRuleTypeFactory $typeFactory,
        CurrencyService $currencyService,
        array $allowedTypes
    ) {
        $this->contingentGroupRepository = $contingentGroupRepository;
        $this->contingentRuleRepository = $contingentRuleRepository;
        $this->typeFactory = $typeFactory;
        $this->allowedTypes = $allowedTypes;
        $this->currencyService = $currencyService;
    }

    /**
     * {@inheritdoc}
     */
    public function createCartAccessForIdentity(Identity $identity, string $environmentName): CartAccessStrategyInterface
    {
        $context = $identity->getOwnershipContext();

        $ruleStrategies = [];

        $contingentGroupIds = $this->contingentGroupRepository
            ->fetchContingentGroupIdsForContact($context->identityId);

        foreach ($this->allowedTypes as $type) {
            foreach ($contingentGroupIds as $contingentGroupId) {
                $rules = $this->contingentRuleRepository
                    ->fetchActiveRuleItemsForRuleType($type, $contingentGroupId, $this->currencyService->createCurrencyContext());

                foreach ($rules as $rule) {
                    $ruleStrategies[] = $this->typeFactory
                        ->createCartAccessStrategy($type, $context, $rule);
                }
            }
        }

        return new BlackListCartAccess(... $ruleStrategies);
    }
}
