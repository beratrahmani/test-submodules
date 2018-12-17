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
use SwagPromotion\Components\Promotion\Statistics;

class Shopware_Controllers_Backend_SwagPromotionAnalytics extends Shopware_Controllers_Backend_Analytics
{
    public function getAnalyticsAction()
    {
        $fromDate = $this->request->get('fromDate');
        $toDate = $this->request->get('toDate');

        /** @var Statistics $statisticTool */
        $statisticTool = $this->get('swag_promotion.statistics');
        $promotionIds = $statisticTool->findPromotionIdsInTime($fromDate, $toDate);
        $data = $statisticTool->getStatisticsForPromotionList($promotionIds);

        $data = $this->prepareDate($data);

        $this->send($data, count($data));
    }

    public function getAnalyticsDetailAction()
    {
        $fromDate = $this->request->get('fromDate');
        $toDate = $this->request->get('toDate');
        $offset = $this->request->get('start');
        $limit = $this->request->get('limit');

        /** @var Statistics $statisticTool */
        $statisticTool = $this->get('swag_promotion.statistics');
        $promotionIds = $statisticTool->findPromotionIdsInTime($fromDate, $toDate);
        $data = $statisticTool->getStatisticsForPromotionDetails($promotionIds, $offset, $limit);

        $this->send($data, count($data));
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function prepareDate(array $data)
    {
        $returnValue = [];

        foreach ($data as $key => $row) {
            $row['id'] = $key;
            $returnValue[] = $row;
        }

        return $returnValue;
    }
}
