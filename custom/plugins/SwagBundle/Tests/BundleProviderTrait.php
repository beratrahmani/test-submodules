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

namespace SwagBundle\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Customer\Group;
use SwagBundle\Models\Bundle;

trait BundleProviderTrait
{
    /**
     * @return Bundle
     */
    private function getLimitedBundleWithoutInStock()
    {
        $bundle = new Bundle();
        $bundle->setArticle($this->getAssociatedArticle());
        $bundle->setName('Bundle with invalid instock');
        $bundle->setLimited(true);
        $bundle->setQuantity(0);
        $bundle->setCustomerGroups($this->getCustomerGroups());

        return $bundle;
    }

    /**
     * @return Bundle
     */
    private function getBundleWithInvalidCustomerGroups()
    {
        $bundle = new Bundle();
        $bundle->setArticle($this->getAssociatedArticle());
        $bundle->setName('Bundle without customer groups');
        $bundle->setCustomerGroups($this->getCustomerGroups('FOO'));

        return $bundle;
    }

    /**
     * @return Article
     */
    private function getAssociatedArticle()
    {
        $detail = new Detail();

        $article = new Article();
        $article->setConfiguratorSet(null);
        $article->setMainDetail($detail);

        return $article;
    }

    /**
     * @param string $groupKey
     *
     * @return ArrayCollection
     */
    private function getCustomerGroups($groupKey = 'EK')
    {
        $customerGroup = new Group();
        $customerGroup->setKey($groupKey);

        return new ArrayCollection([$customerGroup]);
    }
}
