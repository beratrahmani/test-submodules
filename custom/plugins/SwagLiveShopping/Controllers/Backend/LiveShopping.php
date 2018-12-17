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
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Customer\Group;
use Shopware\Models\Shop\Shop;
use SwagLiveShopping\Models\LiveShopping;

/**
 * Backend Controller of the SwagLiveShopping Plugin.
 *
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class Shopware_Controllers_Backend_LiveShopping extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Pre dispatch event of the bundle backend module.
     */
    public function preDispatch()
    {
        if (!in_array($this->Request()->getActionName(), ['index', 'load', 'validateNumber'], true)) {
            $this->Front()->Plugins()->Json()->setRenderer();
        }
    }

    /**
     * Global interface to create a new LiveShopping.
     *
     * Creates a new LiveShopping record. The function can handles only one data set.
     * The function expects the new record data directly in the request object:
     * <pre>
     *
     * </pre>
     *
     * In case the request was successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * In case the request wasn't successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * @return bool the function assigns the result to the controller view
     */
    public function createLiveShoppingAction()
    {
        $this->View()->assign($this->saveLiveShopping($this->Request()->getParams()));

        return true;
    }

    /**
     * Global interface to update an existing LiveShopping record.
     *
     * Updates an existing LiveShopping record. The function can handles only one data set.
     * The function expects the updated record data directly in the request object:
     * <pre>
     *
     * </pre>
     * Property which aren't passed in the request object, won't be updated.
     *
     * In case the request was successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * In case the request wasn't successfully this function returns the following data:
     *
     * @return bool the function assigns the result to the controller view
     */
    public function updateLiveShoppingAction()
    {
        $this->View()->assign($this->saveLiveShopping($this->Request()->getParams()));

        return true;
    }

    /**
     * Global interface to delete an existing LiveShopping record.
     *
     * Removes an existing LiveShopping record from the shopware database.
     * The function can handles multiple data set.
     * In case that the function has to remove only one record, the function expects the following
     * request parameter structure:
     * <pre>
     *
     * </pre>
     *
     * In case that the function has to remove multiple record, the function expects the following
     * request parameter structure:
     * <pre>
     *
     * </pre>
     *
     * In case the request was successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * @return bool the function assigns the result to the controller view
     */
    public function deleteLiveShoppingAction()
    {
        $this->View()->assign($this->deleteLiveShopping($this->Request()->getParam('id')));

        return true;
    }

    /**
     * Global interface to get an offset of defined LiveShopping records.
     *
     * The getListAction expects the standard listing parameters directly in the request parameters
     * start, limit, filter and sort.
     *
     * In case the request was successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * In case the request wasn't successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * @return bool the function assigns the result to the controller view
     */
    public function getListAction()
    {
        $this->View()->assign(
            $this->getList(
                $this->Request()->getParam('articleId'),
                $this->Request()->getParam('filter', []),
                $this->Request()->getParam('sort', []),
                $this->Request()->getParam('start'),
                $this->Request()->getParam('limit')
            )
        );

        return true;
    }

    /**
     * Global interface to get the whole data for a single LiveShopping record.
     *
     * The getDetailAction expects the LiveShopping id in the request parameters.
     * This function can handles only one data set.
     *
     * In case the request was successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * In case the request wasn't successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * @return bool
     */
    public function getDetailAction()
    {
        $this->View()->assign($this->getDetail($this->Request()->getParam('id')));

        return true;
    }

    /**
     * Global interface which used for the bundle backend extension of the product module.
     * Returns an offset of product variants for the passed product id.
     *
     * @return bool
     */
    public function getVariantsAction()
    {
        $this->View()->assign(
            $this->getVariants(
                $this->Request()->getParam('articleId'),
                $this->Request()->getParam('start', 0),
                $this->Request()->getParam('limit', 20)
            )
        );

        return true;
    }

    /**
     * Remove validation.
     *
     * This function is used to validate the inserted live shopping order number
     * The number has to be unique in the whole system.
     */
    public function validateNumberAction()
    {
        $pluginManager = $this->get('plugins');
        $pluginManager->Controller()->ViewRenderer()->setNoRender();

        echo $this->validateNumber(
            $this->Request()->getParam('value'),
            $this->Request()->getParam('param')
        );
    }

    /**
     * Internal helper function which returns an offset of variants for the passed product id.
     *
     * @param int $productId
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    private function getVariants($productId, $offset, $limit)
    {
        /** @var \Doctrine\ORM\QueryBuilder $builder */
        $builder = $this->get('models')->getRepository(LiveShopping::class)
            ->getProductVariantsQueryBuilder($productId, $offset, $limit);

        $query = $builder->getQuery();

        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $paginator = $this->getPaginator($query);

        return [
            'success' => true,
            'total' => $paginator->count(),
            'data' => $paginator->getIterator()->getArrayCopy(),
        ];
    }

    /**
     * Internal helper function to get an offset of defined LiveShopping records.
     *
     * The getListAction expects the standard listing parameters directly in the request parameters start, limit, filter and sort.
     *
     * In case the request was successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * In case the request wasn't successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * @param int   $productId
     * @param array $filter        An array of listing filters to filter the result set
     * @param array $sort          An array of listing order by condition to sort the result set
     * @param int   $offset        an offset for a paginated listing
     * @param int   $limit         an limit for a paginated listing
     * @param int   $hydrationMode
     *
     * @return array Result of the listing query or the exception code and message
     */
    private function getList(
        $productId,
        array $filter,
        array $sort,
        $offset,
        $limit,
        $hydrationMode = \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY
    ) {
        try {
            /** @var \Doctrine\ORM\QueryBuilder $builder */
            $builder = $this->get('models')->getRepository(LiveShopping::class)
                ->getListQueryBuilder($productId, $filter, $sort, $offset, $limit);

            $query = $builder->getQuery();

            $query->setHydrationMode($hydrationMode);

            $paginator = $this->getPaginator($query);

            return [
                'success' => true,
                'total' => $paginator->count(),
                'data' => $paginator->getIterator()->getArrayCopy(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Internal function to save a single LiveShopping record.
     *
     * Used from the createLiveShoppingAction and updateLiveShoppingAction interface.
     * Contains the whole source code logic to save a single LiveShopping record.
     *
     * In case the request was successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * In case the request wasn't successfully this function returns the following data:
     * <pre>
     *
     * </pre>
     *
     * @param array $data The whole liveShopping data as array
     *
     * @return array Result of the delete process
     */
    private function saveLiveShopping(array $data)
    {
        try {
            $modelManager = $this->get('models');
            /* @var LiveShopping $model  */
            if (empty($data['id'])) {
                $model = new LiveShopping();
            } else {
                $model = $modelManager->find(LiveShopping::class, $data['id']);
            }

            if (!$model instanceof LiveShopping) {
                return ['success' => false, 'message' => "LiveShopping record can't created or find"];
            }

            $data = $this->prepareLiveShoppingData($data);
            $model->fromArray($data);

            $modelManager->persist($model);
            $modelManager->flush();

            $productId = $data['articleId'];
            $this->invalidateProductCache($productId);

            $data = $this->getDetail($model->getId());

            $this->updateESIndex($productId);

            return [
                'success' => true,
                'data' => $data['data'],
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Internal helper function to prepare the associated data of a single liveShopping resource.
     *
     * @param array $data
     *
     * @return array $data
     */
    private function prepareLiveShoppingData(array $data)
    {
        $modelManager = $this->get('models');

        /** @var Article $product */
        $product = $modelManager->find(Article::class, $data['articleId']);
        $data['article'] = $product;

        $data = $this->prepareLiveShoppingTimeFields($data);
        $data['customerGroups'] = $this->prepareLiveShoppingCustomerGroups($data);
        $data['limitedVariants'] = $this->prepareLiveShoppingLimitedVariants($data);
        $data['prices'] = $this->prepareLiveShoppingPrices($data, $product);
        $data['shops'] = $this->prepareLiveShoppingShops($data);

        return $data;
    }

    /**
     * Helper function to prepare the live shopping time fields.
     *
     * This function is used to convert the passed ExtJs dates into valid doctrine
     * date values.
     *
     * @param array $data
     *
     * @return array
     */
    private function prepareLiveShoppingTimeFields(array $data)
    {
        if (!empty($data['id'])) {
            unset($data['created']);
        } else {
            $data['created'] = new \DateTime();
        }

        if (!empty($data['validFrom'])) {
            $validFrom = new \DateTime($data['validFrom']);
            $validFromTime = explode(':', $data['validFromTime']);
            $validFrom->setTime($validFromTime[0], $validFromTime[1]);
            $data['validFrom'] = $validFrom;
        }

        if (!empty($data['validTo'])) {
            $validTo = new \DateTime($data['validTo']);
            $validToTime = explode(':', $data['validToTime']);
            $validTo->setTime($validToTime[0], $validToTime[1]);
            $data['validTo'] = $validTo;
        }

        return $data;
    }

    /**
     * Helper function to prepare the live shopping customer groups.
     *
     * This function is used to convert the passed ExtJs customer group data into valid
     * customer group models for doctrine.
     *
     * @param array $data
     *
     * @return array
     */
    private function prepareLiveShoppingCustomerGroups(array $data)
    {
        $customerGroups = [];
        $modelManager = $this->get('models');
        foreach ($data['customerGroups'] as $customerGroupData) {
            if (empty($customerGroupData['id'])) {
                continue;
            }
            $customerGroup = $modelManager->find(Group::class, $customerGroupData['id']);
            if (!($customerGroup instanceof Group)) {
                continue;
            }
            $customerGroups[] = $customerGroup;
        }

        return $customerGroups;
    }

    /**
     * Helper function to prepare the live shopping shops
     *
     * This function is used to convert the passed ExtJs shop data into valid
     * models for doctrine.
     *
     * @param array $data
     *
     * @return array
     */
    private function prepareLiveShoppingShops(array $data)
    {
        $shops = [];
        $modelManager = $this->get('models');
        foreach ($data['shops'] as $shopData) {
            if (empty($shopData['id'])) {
                continue;
            }
            $shop = $modelManager->find(Shop::class, $shopData['id']);
            if (!($shop instanceof Shop)) {
                continue;
            }
            $shops[] = $shop;
        }

        return $shops;
    }

    /**
     * Helper function to prepare the live shopping limited details
     *
     * This function is used to convert the passed ExtJs product details data into valid
     * models for doctrine.
     *
     * @param array $data
     *
     * @return array
     */
    private function prepareLiveShoppingLimitedVariants(array $data)
    {
        $limitedVariants = [];
        $modelManager = $this->get('models');
        foreach ($data['limitedVariants'] as $limitedVariantData) {
            if (empty($limitedVariantData['id'])) {
                continue;
            }
            $limitedVariant = $modelManager->find(Detail::class, $limitedVariantData['id']);
            if (!($limitedVariant instanceof Detail)) {
                continue;
            }
            $limitedVariants[] = $limitedVariant;
        }

        return $limitedVariants;
    }

    /**
     * Helper function to prepare the live shopping prices.
     *
     * This function is used to calculate the inserted gross and net prices.
     *
     * @param array   $data
     * @param Article $product
     *
     * @return array
     */
    private function prepareLiveShoppingPrices(array $data, Article $product)
    {
        $prices = [];
        $modelManager = $this->get('models');

        foreach ($data['prices'] as $priceData) {
            /* @var Group $customerGroup  */
            $customerGroup = $modelManager->find(Group::class, $priceData['customerGroup'][0]['id']);
            if (!($customerGroup instanceof Group)) {
                continue;
            }

            $priceData['customerGroup'] = $customerGroup;
            if ($customerGroup->getTaxInput()) {
                $priceData['price'] = $priceData['price'] / (100 + $product->getTax()->getTax()) * 100;
                $priceData['endPrice'] = $priceData['endPrice'] / (100 + $product->getTax()->getTax()) * 100;
            }
            $prices[] = $priceData;
        }

        return $prices;
    }

    /**
     * Internal function to delete a LiveShopping record.
     *
     * Used from the deleteLiveShoppingAction interface.
     * Contains the whole source code logic to delete a single LiveShopping record.
     *
     * @param int $id unique identifier for the LiveShopping record
     *
     * @return array Result of the delete process
     */
    private function deleteLiveShopping($id)
    {
        try {
            $modelManager = $this->get('models');
            /* @var LiveShopping $liveShopping  */
            $model = $modelManager->find(LiveShopping::class, (int) $id);
            $productId = $model->getArticle()->getId();

            $modelManager->remove($model);
            $modelManager->flush();

            $this->updateESIndex($productId);

            return [
                'success' => true,
                'data' => ['id' => $id],
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Internal function to get the whole data for a single LiveShopping record.
     * The LiveShopping record will be identified over the
     * passed id parameter. The second parameter "$hydrationMode" can be use to control the result data type.
     *
     * @param int $id
     * @param int $hydrationMode
     *
     * @return array
     */
    private function getDetail($id, $hydrationMode = \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY)
    {
        try {
            /** @var \Doctrine\ORM\QueryBuilder $builder */
            $builder = $this->get('models')->getRepository(LiveShopping::class)
                ->getDetailQueryBuilder($id);

            $query = $builder->getQuery();

            $query->setHydrationMode($hydrationMode);

            $paginator = $this->getPaginator($query);

            $records = $paginator->getIterator()->getArrayCopy();

            $liveShopping = $records[0];

            if ($hydrationMode === \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY) {
                $liveShopping['customerGroups'] = $this->getLiveShoppingCustomerGroups($id);
                $liveShopping['shops'] = $this->getLiveShoppingShops($id);
                $liveShopping['validFromTime'] = $this->getTimeOfDateTime($liveShopping['validFrom']);
                $liveShopping['validToTime'] = $this->getTimeOfDateTime($liveShopping['validTo']);
                $liveShopping['prices'] = $this->formatPricesIntoGross(
                    $liveShopping['prices'],
                    $liveShopping['article']['tax']
                );
            }

            return [
                'success' => true,
                'data' => $liveShopping,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Internal function which validates the passed order number for live shoppings.
     * Each live shopping order number can only be defined one time.
     * Returns true if the passed number is unique.
     *
     * @param string $number         Number to validate
     * @param int    $liveShoppingId Optional live shopping id, sent to exclude an existing live shopping
     *
     * @return bool
     */
    private function validateNumber($number, $liveShoppingId = null)
    {
        $parameters = ['number' => $number];
        $modelManager = $this->get('models');

        $builder = $modelManager->createQueryBuilder();
        $builder->select(['liveShopping'])
                ->from(LiveShopping::class, 'liveShopping')
                ->where('liveShopping.number = :number');

        if ($liveShoppingId !== null) {
            $builder->andWhere('liveShopping.id != :liveShoppingId');
            $parameters['liveShoppingId'] = $liveShoppingId;
        }
        $builder->setParameters($parameters);
        $result = $builder->getQuery()->getArrayResult();

        return empty($result);
    }

    /**
     * Helper function to get the live shopping assigned customer groups
     *
     * @param int $liveShoppingId
     *
     * @return array
     */
    private function getLiveShoppingCustomerGroups($liveShoppingId)
    {
        $customerGroups = $this->get('models')->getRepository(LiveShopping::class)
            ->getCustomerGroupsQueryBuilder($liveShoppingId)
            ->getQuery()
            ->getArrayResult();

        if (!empty($customerGroups[0]['customerGroups'])) {
            return $customerGroups[0]['customerGroups'];
        }

        return [];
    }

    /**
     * Helper function to get the assigned live shopping shops.
     *
     * @param int $liveShoppingId
     *
     * @return array
     */
    private function getLiveShoppingShops($liveShoppingId)
    {
        $shops = $this->get('models')->getRepository(LiveShopping::class)
            ->getShopsQueryBuilder($liveShoppingId)->getQuery()->getArrayResult();

        if (!empty($shops[0]['shops'])) {
            return $shops[0]['shops'];
        }

        return [];
    }

    /**
     * Helper function to get the hour and minute value of the passed
     * date time object.
     *
     * @param DateTime $dateTime
     *
     * @return string
     */
    private function getTimeOfDateTime($dateTime)
    {
        if ($dateTime instanceof \DateTime) {
            return $dateTime->format('H:i');
        }

        return '00:00';
    }

    /**
     * Formats the prices for the bundle detail page.
     * The prices has to format from net prices (from the database)
     * to gross prices (for the view).
     *
     * @param array $prices
     * @param array $tax
     *
     * @return array
     */
    private function formatPricesIntoGross(array $prices, array $tax)
    {
        foreach ($prices as &$price) {
            if ($price['customerGroup']['taxInput']) {
                $price['price'] = $this->calculateGrossPrice($price['price'], $tax['tax']);
                $price['endPrice'] = $this->calculateGrossPrice($price['endPrice'], $tax['tax']);
            }
        }

        return $prices;
    }

    /**
     * @param Query $query
     *
     * @return Paginator
     */
    private function getPaginator(Query $query)
    {
        return $this->get('models')->createPaginator($query);
    }

    /**
     * Triggers an event to clean product cache
     *
     * @param int $productId
     */
    private function invalidateProductCache($productId)
    {
        $this->get('events')->notify('Shopware_Plugins_HttpCache_InvalidateCacheId', ['cacheId' => "a{$productId}"]);
    }

    /**
     * @param string $productId
     */
    private function updateESIndex($productId)
    {
        if ($this->get('kernel')->getConfig()['es']['enabled']) {
            $backlogProcessor = $this->get('shopware_elastic_search.backlog_processor');
            $backlogProcessor->add(
                [
                    new Shopware\Bundle\ESIndexingBundle\Struct\Backlog(
                        Shopware\Bundle\ESIndexingBundle\Subscriber\ORMBacklogSubscriber::EVENT_ARTICLE_UPDATED,
                        ['id' => $productId]
                    ),
                ]
            );
        }
    }

    /**
     * @param float $netPrice
     * @param float $taxRate
     *
     * @throws \InvalidArgumentException
     *
     * @return float
     */
    private function calculateGrossPrice($netPrice, $taxRate)
    {
        if (!is_numeric($netPrice) || !is_numeric($taxRate)) {
            throw new \InvalidArgumentException();
        }

        return $netPrice / 100 * (100 + $taxRate);
    }
}
