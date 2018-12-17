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

namespace SwagAboCommerce\Bundle\SearchBundleDBAL;

use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagAboCommerce\Bundle\SearchBundle\Condition\AboCommerceCondition;
use SwagAboCommerce\Services\DBALJoinTableServiceInterface;

class AboCommerceConditionHandler implements ConditionHandlerInterface
{
    /**
     * @var DBALJoinTableServiceInterface
     */
    private $joinTableService;

    /**
     * @param DBALJoinTableServiceInterface $joinTableService
     */
    public function __construct(DBALJoinTableServiceInterface $joinTableService)
    {
        $this->joinTableService = $joinTableService;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return $condition instanceof AboCommerceCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        $this->joinTableService->joinTable($query);
        $query->andWhere('aboProduct.id IS NOT NULL');
    }
}
