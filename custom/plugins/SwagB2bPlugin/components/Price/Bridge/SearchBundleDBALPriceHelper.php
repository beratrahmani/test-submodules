<?php declare(strict_types=1);

namespace Shopware\B2B\Price\Bridge;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder as DoctrineQueryBuilder;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\Bundle\SearchBundleDBAL\PriceHelper as CorePriceHelper;
use Shopware\Bundle\SearchBundleDBAL\PriceHelperInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct;

class SearchBundleDBALPriceHelper implements PriceHelperInterface
{
    /**
     * @var PriceHelperInterface
     */
    private $decorated;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param PriceHelperInterface $decorated
     * @param Connection $connection
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        PriceHelperInterface $decorated,
        Connection $connection,
        AuthenticationService $authenticationService
    ) {
        $this->decorated = $decorated;
        $this->connection = $connection;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @inheritdoc
     */
    public function getSelection(Struct\ProductContextInterface $context)
    {
        return $this->decorated->getSelection($context);
    }

    /**
     * @param QueryBuilder $query
     * @param Struct\ShopContextInterface $context
     */
    public function joinPrices(QueryBuilder $query, Struct\ShopContextInterface $context)
    {
        if (!$this->authenticationService->isB2b()) {
            $this->decorated->joinPrices($query, $context);

            return;
        }

        if ($query->hasState(CorePriceHelper::STATE_INCLUDES_CHEAPEST_PRICE)) {
            return;
        }

        $this->joinDefaultPrices($query, $context);
        $query = $this->buildQuery(
            $query,
            'customerPrice',
            [':currentCustomerGroup', $context->getCurrentCustomerGroup()->getKey()]
        );

        $query->leftJoin(
            'product',
            's_core_pricegroups_discounts',
            'priceGroup',
            'priceGroup.groupID = product.pricegroupID
             AND priceGroup.discountstart = 1
             AND priceGroup.customergroupID = :priceGroupCustomerGroup
             AND product.pricegroupActive = 1'
        );

        $query->setParameter(':priceGroupCustomerGroup', $context->getCurrentCustomerGroup()->getId());

        $query->addState(CorePriceHelper::STATE_INCLUDES_CHEAPEST_PRICE);
    }

    /**
     * @param QueryBuilder $query
     * @param Struct\ShopContextInterface $context
     */
    public function joinDefaultPrices(QueryBuilder $query, Struct\ShopContextInterface $context)
    {
        if (!$this->authenticationService->isB2b()) {
            $this->decorated->joinDefaultPrices($query, $context);

            return;
        }

        if ($query->hasState(CorePriceHelper::STATE_INCLUDES_DEFAULT_PRICE)) {
            return;
        }

        $this->joinAvailableVariant($query);

        $query = $this->buildQuery(
            $query,
            'defaultPrice',
            [':fallbackCustomerGroup', $context->getFallbackCustomerGroup()->getKey()]
        );

        $query->addState(CorePriceHelper::STATE_INCLUDES_DEFAULT_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function joinAvailableVariant(QueryBuilder $query)
    {
        return $this->decorated->joinAvailableVariant($query);
    }

    /**
     * @param QueryBuilder $query
     * @param $name
     * @param $group
     * @return QueryBuilder
     */
    private function buildQuery(QueryBuilder $query, $name, $group)
    {
        list($groupName, $groupValue) = $group;
        $subQueryName = $name . 's';

        $subQuery = $this->buildSubQuery($subQueryName, $groupName);

        $query->leftJoin(
            'product',
            '(' . $subQuery->getSQL() . ')',
            $name,
            'availableVariant.id = ' . $name . '.articledetailsID'
        );

        $query->setParameter($groupName, $groupValue)->setParameter(':debtor_id', $this->authenticationService->getIdentity()->getOwnershipContext()->shopOwnerUserId);

        return $query;
    }

    /**
     * @param $subQueryName
     * @param $groupName
     * @return DoctrineQueryBuilder
     */
    private function buildSubQuery($subQueryName, $groupName): DoctrineQueryBuilder
    {
        $subQuery = $this->connection->createQueryBuilder();

        $subQuery->select(
            'IFNULL(b2b_prices.price, ' . $subQueryName . '.price) as price',
            $subQueryName . '.pricegroup',
            $subQueryName . '.from',
            $subQueryName . '.to',
            $subQueryName . '.articleID',
            $subQueryName . '.articledetailsID',
            $subQueryName . '.pseudoprice',
            $subQueryName . '.baseprice',
            $subQueryName . '.percent'
        );

        $subQuery->from('s_articles_prices', $subQueryName);

        $subQuery->leftJoin(
            $subQueryName,
            'b2b_prices',
            'b2b_prices',
            'b2b_prices.articles_details_id = ' . $subQueryName . '.articledetailsID
             AND b2b_prices.debtor_id = :debtor_id
             AND b2b_prices.from = 1'
        );

        $subQuery->where(
            $subQueryName . '.priceGroup = ' . $groupName . '
            AND ' . $subQueryName . '.from = 1'
        );

        $subQuery->setParameter('debtor_id', $this->authenticationService->getIdentity()->getOwnershipContext()->shopOwnerUserId);

        return $subQuery;
    }
}
