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
use Shopware\Components\Model\ModelManager;
use SwagPromotion\Models\Promotion;

class Shopware_Controllers_Backend_SwagPromotion extends Shopware_Controllers_Backend_Application
{
    protected $model = Promotion::class;
    protected $alias = 'promotion';

    /**
     * {@inheritdoc}
     */
    public function save($data)
    {
        $data = $this->moveToMainArray(
            $data,
            [
                'applyRules' => 'promotionRules',
                'rules' => 'promotionRules',
            ]
        );

        if (strpos($data['rules'], ',"":[]') !== false) {
            $data['rules'] = str_replace(',"":[]', '', $data['rules']);
        }

        $data = $this->normalizeDateTime($data);

        return parent::save($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getDetail($id)
    {
        $result = parent::getDetail($id);

        $result = $this->moveToSubArray(
            $result,
            [
                'applyRules' => 'promotionRules',
                'rules' => 'promotionRules',
            ]
        );

        $result = $this->normalizeResultDateTime($result);

        return $result;
    }

    /**
     * saves promotion if edited in list row
     */
    public function saveRowEditingDataAction()
    {
        $data = json_decode($this->request->getParam('transportData'), true);

        $params = $this->convertData($data);

        $sql = 'UPDATE s_plugin_promotion SET
                active = :active,
                amount = :amount
                WHERE id = :id';

        $this->get('db')->query($sql, $params);

        $this->view->assign(['success' => true]);
    }

    /**
     * duplicates given promotion
     */
    public function duplicateRowAction()
    {
        $data = json_decode($this->request->getParam('transportData'), true);
        $promotionId = $data['id'];

        if (empty($promotionId)) {
            $this->View()->assign(['success' => false, 'message' => 'No valid promotion ID passed']);

            return;
        }

        /** @var ModelManager $em */
        $em = $this->get('models');

        $promotion = $em->find($this->model, $promotionId);

        $newPromotion = clone $promotion;
        $newPromotion->setName($promotion->getName() . ' Clone');

        $em->persist($newPromotion);
        $em->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * deletes the given promotion
     */
    public function deleteRowAction()
    {
        $data = json_decode($this->request->getParam('transportData'), true);
        $promotionId = $data['id'];

        if (empty($promotionId)) {
            $this->View()->assign(['success' => false, 'message' => 'No valid promotion ID passed']);

            return;
        }

        /** @var ModelManager $em */
        $em = $this->get('models');

        $promotion = $em->find($this->model, $promotionId);

        $em->remove($promotion);
        $em->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDetailQuery($id)
    {
        $builder = parent::getDetailQuery($id);

        $builder->addSelect(['customerGroups', 'shops', 'doNotRunAfter', 'doNotAllowLater', 'voucher', 'freeGoodsArticle']);
        $builder->leftJoin('promotion.customerGroups', 'customerGroups');
        $builder->leftJoin('promotion.shops', 'shops');
        $builder->leftJoin('promotion.doNotRunAfter', 'doNotRunAfter');
        $builder->leftJoin('promotion.doNotAllowLater', 'doNotAllowLater');
        $builder->leftJoin('promotion.voucher', 'voucher');
        $builder->leftJoin('promotion.freeGoodsArticle', 'freeGoodsArticle');

        return $builder;
    }

    /**
     * {@inheritdoc}
     */
    protected function getList($offset, $limit, $sort = [], $filter = [], $wholeParams = [])
    {
        $list = parent::getList($offset, $limit, $sort, $filter, $wholeParams);

        /** @var SwagPromotion\Components\Promotion\Statistics $statisticService */
        $statisticService = $this->get('swag_promotion.statistics');
        $promotionStats = $statisticService->getStatisticsForPromotionList(array_column($list['data'], 'id'));

        if (!$promotionStats) {
            return $list;
        }

        foreach ($list['data'] as $key => $promotion) {
            if (isset($promotionStats[$promotion['id']])) {
                $list['data'][$key]['turnover'] = $promotionStats[$promotion['id']]['turnover'];
                $list['data'][$key]['orders'] = $promotionStats[$promotion['id']]['orders'];
            }
        }

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSearchAssociationQuery($association, $model, $search)
    {
        $builder = parent::getSearchAssociationQuery($association, $model, $search);
        if ($model == 'Shopware\Models\Voucher\Voucher') {
            $builder->andWhere('voucher.modus < 2');
        }

        if ($association == 'freeGoodsArticle' && strlen($search) > 0) {
            $builder->leftJoin($association . '.details', 'details')
                ->leftJoin($association . '.supplier', 'supplier')
                ->orWhere('details.number LIKE :search')
                ->orWhere('supplier.name LIKE :search');
        }

        return $builder;
    }

    /**
     * Moves $items back to the mainArray of $data
     *
     * array(
     *      'step' => 'discount'
     * )
     *
     * will convert the array
     *
     * array(
     *      discount => array(
     *          'step' => 'discount'
     *      )
     * )
     *
     * to
     *
     * array(
     *  'step' => 'discount'
     * )
     *
     * @param $data
     * @param $items
     *
     * @return mixed
     */
    private function moveToMainArray($data, $items)
    {
        foreach ($items as $item => $subArray) {
            $data[$item] = $data[$subArray][0][$item];
            unset($data[$subArray][$item]);
        }

        foreach ($data['promotionRules'] as $key => $rule) {
            if (strpos($rule['rules'], ',"":[]') !== false) {
                $data['promotionRules'][$key]['rules'] = str_replace(',"":[]', '', $rule['rules']);
            }
        }

        return $data;
    }

    /**
     * Moves $items to a subarray
     *
     * array(
     *  'step' => 'discount'
     * )
     *
     * will convert the array
     *
     * array(
     *      'step' => 'discount'
     * )
     *
     * to
     *
     * array(
     *      discount => array(
     *          'step' => 'discount'
     *      )
     * )
     *
     *
     * @param array $data
     * @param array $items
     *
     * @return array
     */
    private function moveToSubArray(array $data, array $items)
    {
        foreach ($items as $item => $subArray) {
            $data['data'][$subArray][$item] = $data['data'][$item];
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function normalizeDateTime(array $data)
    {
        //PHP post is filtering null-values, so they are unset from the array.
        //Yet we do need those in the array for the fromArray-method.
        if (!isset($data['validFrom'])) {
            $data['validFrom'] = null;
        } else {
            $data['validFrom'] = $this->getDateTime($data['validFrom'], $data['timeFrom']);
        }
        if (!isset($data['validTo'])) {
            $data['validTo'] = null;
        } else {
            $data['validTo'] = $this->getDateTime($data['validTo'], $data['timeTo']);
        }

        return $data;
    }

    /**
     * @param string $date
     * @param string $time
     *
     * @return string
     */
    private function getDateTime($date, $time)
    {
        $date = substr($date, 0, 10);
        if (!isset($time)) {
            $time = '00:00:00';
        }

        $dateTime = new DateTime($date . 'T' . $time);

        return $dateTime->format('Y-m-d H:i:s');
    }

    /**
     * @param array $result
     *
     * @return array
     */
    private function normalizeResultDateTime(array $result)
    {
        $validFrom = $result['data']['validFrom'];
        $validTo = $result['data']['validTo'];

        if ($validFrom instanceof DateTime) {
            $result['data']['timeFrom'] = $validFrom->format('H:i');
        }

        if ($validTo instanceof DateTime) {
            $result['data']['timeTo'] = $validTo->format('H:i');
        }

        return $result;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function convertData(array $data)
    {
        $returnValue = [];
        $returnValue['active'] = $data['active'];
        $returnValue['amount'] = $data['amount'];
        $returnValue['id'] = $data['id'];

        return $returnValue;
    }
}
