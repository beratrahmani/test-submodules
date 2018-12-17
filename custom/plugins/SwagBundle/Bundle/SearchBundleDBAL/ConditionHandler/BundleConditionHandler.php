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

namespace SwagBundle\Bundle\SearchBundleDBAL\ConditionHandler;

use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagBundle\Bundle\SearchBundle\Condition\BundleCondition;
use SwagBundle\Bundle\SearchBundleDBAL\BundleJoinHelper;

class BundleConditionHandler implements ConditionHandlerInterface
{
    /**
     * @var BundleJoinHelper
     */
    private $bundleJoinHelper;

    /**
     * @param BundleJoinHelper $bundleJoinHelper
     */
    public function __construct(BundleJoinHelper $bundleJoinHelper)
    {
        $this->bundleJoinHelper = $bundleJoinHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return $condition instanceof BundleCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        $this->bundleJoinHelper->joinTable($query, $context);

        $query->andWhere('swag_bundles.articleID IS NOT NULL');
    }
}
