<?php declare(strict_types=1);

namespace Shopware\B2B\Dashboard\Framework;

use Shopware\B2B\Cart\Framework\CartAccessResult;
use Shopware\B2B\Cart\Framework\InformationMessage;
use Shopware\B2B\ContingentGroup\Framework\ContingentGroupRepository;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleEntity;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleRepository;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleSearchStruct;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeFactory;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class InformationService
{
    /**
     * @var ContingentRuleRepository
     */
    private $contingentRuleRepository;

    /**
     * @var ContingentGroupRepository
     */
    private $contingentGroupRepository;

    /**
     * @var ContingentRuleTypeFactory
     */
    private $typeFactory;

    /**
     * @param ContingentGroupRepository $contingentGroupRepository
     * @param ContingentRuleRepository $contingentRuleRepository
     * @param ContingentRuleTypeFactory $typeFactory
     */
    public function __construct(
        ContingentGroupRepository $contingentGroupRepository,
        ContingentRuleRepository $contingentRuleRepository,
        ContingentRuleTypeFactory $typeFactory
    ) {
        $this->contingentRuleRepository = $contingentRuleRepository;
        $this->contingentGroupRepository = $contingentGroupRepository;
        $this->typeFactory = $typeFactory;
    }

    /**
     * @param Identity $identity
     * @param CurrencyContext $currencyContext
     * @return InformationMessage[]
     */
    public function getInformation(Identity $identity, CurrencyContext $currencyContext): array
    {
        $context = $identity->getOwnershipContext();

        $contingentGroupIds = $this->contingentGroupRepository
            ->fetchContingentGroupIdsForContact($context->identityId);

        $cartAccessResult = new CartAccessResult();

        foreach ($contingentGroupIds as $groupsId) {
            $rules = $this->contingentRuleRepository
                ->fetchListByContingentGroupId(
                    $groupsId,
                    new ContingentRuleSearchStruct(),
                    $currencyContext,
                    $context
                );

            $this->addInformationToCartAccessResult(
                $rules,
                $context,
                $cartAccessResult
            );
        }

        return $cartAccessResult->information;
    }

    /**
     * @internal
     * @param ContingentRuleEntity[] $rules
     * @param OwnershipContext $context
     * @param CartAccessResult $cartAccessResult
     */
    protected function addInformationToCartAccessResult(array $rules, OwnershipContext $context, CartAccessResult $cartAccessResult)
    {
        foreach ($rules as $rule) {
            $this->typeFactory
                ->createCartAccessStrategy($rule->type, $context, $rule)
                ->addInformation($cartAccessResult);
        }
    }
}
