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

namespace SwagLiveShopping\Bundle\SearchBundleDBAL\Sorting;

use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\SortingHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagLiveShopping\Bundle\SearchBundle\Sorting\LiveShoppingSorting;
use SwagLiveShopping\Bundle\SearchBundleDBAL\AbstractLiveShoppingHandler;

class LiveShoppingSortingHandler extends AbstractLiveShoppingHandler implements SortingHandlerInterface
{
    /**
     * @param SortingInterface $sorting
     *
     * @return bool
     */
    public function supportsSorting(SortingInterface $sorting)
    {
        return $sorting instanceof LiveShoppingSorting;
    }

    /**
     * @param SortingInterface     $sorting
     * @param QueryBuilder         $query
     * @param ShopContextInterface $context
     */
    public function generateSorting(
        SortingInterface $sorting,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        /* @var LiveShoppingSorting $sorting */
        $this->joinTable($query);
        $query->addOrderBy('liveShopping.active', $sorting->getDirection());
    }
}
