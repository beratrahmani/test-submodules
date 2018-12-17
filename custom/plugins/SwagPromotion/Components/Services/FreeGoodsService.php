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

namespace SwagPromotion\Components\Services;

use Doctrine\DBAL\Connection;
use SwagPromotion\Components\Promotion\ProductStacker\ProductStackRegistry;
use SwagPromotion\Struct\Promotion;

class FreeGoodsService
{
    /**
     * @var Connection
     */
    private $dbConnection;

    /**
     * @var ProductStackRegistry
     */
    private $stackerRegistry;

    /**
     * FreeGoodsService constructor.
     *
     * @param Connection           $dbConnection
     * @param ProductStackRegistry $productStackRegistry
     */
    public function __construct(Connection $dbConnection, ProductStackRegistry $productStackRegistry)
    {
        $this->dbConnection = $dbConnection;
        $this->stackerRegistry = $productStackRegistry;
    }

    /**
     * adds the given product as free good to the basket
     * also updates the basket attribute field
     *
     * @param string $orderNumber
     * @param int    $promotionId
     */
    public function addArticleAsFreeGood($orderNumber, $promotionId)
    {
        $basket = Shopware()->Modules()->Basket();
        $basketId = $basket->sAddArticle($orderNumber);

        $sql = 'SELECT swag_is_free_good_by_promotion_id
                FROM s_order_basket_attributes
                WHERE basketID = :basketId;';
        $promotionIds = $this->dbConnection->executeQuery($sql, ['basketId' => $basketId])->fetchColumn();

        $promotionIds = unserialize($promotionIds);

        if ($promotionIds) {
            $promotionIds[] = $promotionId;
        } else {
            $promotionIds = [$promotionId];
        }

        $promotionIds = serialize($promotionIds);

        $sql = 'UPDATE s_order_basket_attributes
                SET swag_is_free_good_by_promotion_id = :promotionIds
                WHERE basketID = :basketId;';
        $this->dbConnection->executeQuery($sql, ['promotionIds' => $promotionIds, 'basketId' => $basketId]);

        $basket->sRefreshBasket();
    }

    /**
     * if the quantity of a free good products is lower than the amount of promotion IDs, we have to remove some IDs
     *
     * @param int $basketId
     * @param int $quantity
     *
     * @return bool
     */
    public function updateFreeGoodsItem($basketId, $quantity)
    {
        $sql = 'SELECT swag_is_free_good_by_promotion_id
                FROM s_order_basket_attributes
                WHERE basketID = :basketId;';
        $promotionIds = $this->dbConnection->executeQuery($sql, ['basketId' => $basketId])->fetchColumn();

        $promotionIds = unserialize($promotionIds);

        // no promotion IDs are set so do nothing
        if (!$promotionIds) {
            return false;
        }

        $amountPromotionIds = count($promotionIds);

        //quantity is higher than the amount of promotion IDs so there is no problem
        if ($quantity >= $amountPromotionIds) {
            return false;
        }

        // delete last promotion ID until amount of promotion IDs matches quantity
        $diff = $amountPromotionIds - $quantity;
        for ($i = 0; $i < $diff; ++$i) {
            array_pop($promotionIds);
        }

        $this->dbConnection->update(
            's_order_basket_attributes',
            [
                'swag_is_free_good_by_promotion_id' => $promotionIds ? serialize($promotionIds) : null,
            ],
            [
                'basketID' => $basketId,
            ]
        );

        return true;
    }

    /**
     * if a free good promotion is not valid anymore, update data in the basket attribute field,
     * so it does not affect other calculations
     *
     * @param array|null $basketItems
     * @param array      $freeGoods
     * @param int        $promotionId
     */
    public function clearFreeGoodsFromBasket($basketItems, array $freeGoods, $promotionId)
    {
        foreach ($basketItems as $basketItem) {
            if (!in_array($basketItem['articleID'], $freeGoods)) {
                continue;
            }

            $basketId = $basketItem['id'];

            $sql = 'SELECT swag_is_free_good_by_promotion_id
                    FROM s_order_basket_attributes
                    WHERE basketID = :basketId;';
            $promotionIds = $this->dbConnection->executeQuery($sql, ['basketId' => $basketId])->fetchColumn();

            $promotionIds = unserialize($promotionIds);

            if (!$promotionIds) {
                continue;
            }

            $promotionIds = array_diff($promotionIds, [$promotionId]);

            $this->dbConnection->update(
                's_order_basket_attributes',
                [
                    'swag_is_free_good_by_promotion_id' => $promotionIds ? serialize($promotionIds) : null,
                ],
                [
                    'basketID' => $basketId,
                ]
            );
        }
    }

    /**
     * @param Promotion $promotion
     * @param array     $matches
     *
     * @return bool
     */
    public function isAchievedStack(Promotion $promotion, array $matches)
    {
        $stacker = $this->stackerRegistry->getStacker($promotion->stackMode);
        $result = $stacker->getStack(
            $matches,
            $promotion->step,
            $promotion->maxQuantity,
            $promotion->chunkMode
        );

        if ($result) {
            return true;
        }

        return false;
    }
}
