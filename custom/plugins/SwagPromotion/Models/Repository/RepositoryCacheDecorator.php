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

namespace SwagPromotion\Models\Repository;

use SwagPromotion\Struct\Promotion;

/**
 * Class RepositoryCacheDecorator will decorate the main promotion repository with a little cache layer.
 * This is needed, as shopware calls \sBasket::sGetBasket and \ProductListService quite a lot - and that requires the
 * promotion repository too be loaded too often
 */
class RepositoryCacheDecorator implements Repository
{
    /**
     * @var Repository
     */
    private $decorated;

    /**
     * @var Promotion[][]
     */
    private $cache;

    /**
     * RepositoryCacheDecorator constructor.
     *
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->decorated = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getActivePromotions(
        $customerGroupId = null,
        $shopId = null,
        array $voucherIds = []
    ) {
        $hash = md5(json_encode([$customerGroupId, $shopId, $voucherIds]));

        if (!isset($this->cache[$hash])) {
            $this->cache[$hash] = $this->decorated->getActivePromotions($customerGroupId, $shopId, $voucherIds);
        }

        return array_map(function ($promotion) {
            return clone $promotion;
        }, $this->cache[$hash]);
    }

    /**
     * {@inheritdoc}
     */
    public function getPromotionCounts($customerId)
    {
        return $this->decorated->getPromotionCounts($customerId);
    }
}
