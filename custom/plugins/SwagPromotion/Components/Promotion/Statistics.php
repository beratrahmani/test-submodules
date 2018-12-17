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

namespace SwagPromotion\Components\Promotion;

use Doctrine\DBAL\Connection;

class Statistics
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * get all the data that are required by the Promotion-Details Table
     * and returns a array like:
     *
     * array(
     *      promotion_id    => data,
     *      promotion_name  => data,
     *      user_id         => data,
     *      user_name       => data,
     *      order_id        => data,
     *      order_number    => data,
     *      order_turnover  => data
     * )
     *
     * @param array $promotionIds
     * @param int   $offset
     * @param int   $limit
     *
     * @return array
     */
    public function getStatisticsForPromotionDetails(array $promotionIds, $offset, $limit)
    {
        if (empty($promotionIds)) {
            return [];
        }

        $builder = $this->connection->createQueryBuilder();
        $result = $builder->select(
            [
                'c.promotion_id as promotion_id',
                'c.customer_id as user_id',
                'c.order_id as order_id',
                'p.name as promotion_name',
                'u.firstname as firstname',
                'u.lastname as lastname',
                'o.ordernumber as order_number',
                'o.invoice_amount as order_turnover',
                'o.invoice_shipping as invoice_shipping',
            ]
        )->from('s_plugin_promotion_customer_count', 'c')
            ->innerJoin('c', 's_user_addresses', 'u', 'c.customer_id = u.user_id')
            ->innerJoin('c', 's_plugin_promotion', 'p', 'p.id = c.promotion_id')
            ->innerJoin('c', 's_order', 'o', 'o.id = c.order_id')
            ->where('c.promotion_id IN (?)')
            ->orderBy('c.promotion_id')
            ->setParameter(0, $promotionIds, CONNECTION::PARAM_INT_ARRAY)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        $returnValue = [];
        foreach ($result as $promotionDetails) {
            $returnValue[] = $this->prepareDetailsData($promotionDetails);
        }

        return $returnValue;
    }

    /**
     * get all the data that are required by the Promotion chart/table
     * and returns a array like:
     *
     * array(
     *      id => array(
     *          turnover    => data,
     *          orders      => data,
     *          name        => data
     *      )
     * )
     *
     * @param array $promotionIds
     *
     * @return array
     */
    public function getStatisticsForPromotionList(array $promotionIds)
    {
        if (empty($promotionIds)) {
            return [];
        }

        $builder = $this->connection->createQueryBuilder();

        $builder->select(['c.promotion_id', 'p.name', 'o.invoice_amount', 'o.invoice_shipping'])
            ->from('s_plugin_promotion_customer_count', 'c')
            ->innerJoin('c', 's_plugin_promotion', 'p', 'p.id = c.promotion_id')
            ->innerJoin('c', 's_order', 'o', 'o.id = c.order_id')
            ->where('c.promotion_id IN (?)')
            ->setParameter(0, $promotionIds, Connection::PARAM_INT_ARRAY);

        $stmt = $builder->execute();
        $results = $stmt->fetchAll();

        // manually group / sum the invoice
        $statistics = [];
        foreach ($results as $result) {
            $id = $result['promotion_id'];
            if (!isset($statistics[$id])) {
                $statistics[$id] = ['turnover' => 0, 'orders' => 0];
            }
            $statistics[$id]['turnover'] += $result['invoice_amount'] + $result['invoice_shipping'];
            ++$statistics[$id]['orders'];
            $statistics[$id]['name'] = $result['name'];
        }

        return $statistics;
    }

    /**
     * Find all Promotions between Time ['fromDate'] and ['toDate']
     *
     * @param string $fromDate
     * @param string $toDate
     *
     * @return array
     */
    public function findPromotionIdsInTime($fromDate, $toDate)
    {
        if (!$fromDate || !$toDate) {
            return [];
        }

        $sql = 'SELECT id FROM s_plugin_promotion
                WHERE (valid_from <= :fromDate AND valid_to >= :toDate)
                OR (ISNULL(valid_from) AND ISNULL(valid_to))';

        $result = $this->connection->fetchAll(
            $sql,
            ['fromDate' => $this->getFormattedDate($fromDate), 'toDate' => $this->getFormattedDate($toDate)]
        );

        if (!$result) {
            return [];
        }

        $idArray = [];
        foreach ($result as $position) {
            $idArray[] = (int) $position['id'];
        }

        return $idArray;
    }

    /**
     * format the dateTimeString
     * and return it ad ("Y-m-d") format
     *
     * @param string $date
     *
     * @return string
     */
    private function getFormattedDate($date)
    {
        $dateObject = new \DateTime($date);

        return $dateObject->format('Y-m-d');
    }

    /**
     * create a array from the database result
     *
     * @param array $promotionDetails
     *
     * @return array
     */
    private function prepareDetailsData(array $promotionDetails)
    {
        $returnValue = [];
        $returnValue['promotion_id'] = $promotionDetails['promotion_id'];
        $returnValue['promotion_name'] = $promotionDetails['promotion_name'];
        $returnValue['user_id'] = $promotionDetails['user_id'];
        $returnValue['user_name'] = $promotionDetails['lastname'] . ', ' . $promotionDetails['firstname'];
        $returnValue['order_id'] = $promotionDetails['order_id'];
        $returnValue['order_number'] = $promotionDetails['order_number'];
        $returnValue['order_turnover'] = $promotionDetails['order_turnover'] + $promotionDetails['invoice_shipping'];

        return $returnValue;
    }
}
