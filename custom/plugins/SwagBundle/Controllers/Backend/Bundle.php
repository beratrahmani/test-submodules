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
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail as ProductVariant;
use Shopware\Models\Customer\Group;
use Shopware\Models\Tax\Tax;
use SwagBundle\Components\BundleComponentInterface;
use SwagBundle\Models\Article as BundleProduct;
use SwagBundle\Models\Bundle;

/**
 * Backend Controller of the Bundle Plugin.
 *
 * This controller handles all actions around the shopware backend to define bundles.
 * The controller handles the actions of the bundle overview module and the actions
 * of the extended product bundle tab.
 * The product extension are defined in the SwagBundle/Views/backend/article folder.
 * The bundle overview are defined in the SwagBundle/Views/backend/bundle folder.
 *
 * @category  Shopware
 *
 * @copyright Copyright (c), shopware AG (http://en.shopware.com)
 */
class Shopware_Controllers_Backend_Bundle extends Shopware_Controllers_Backend_ExtJs
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
     * Global interface to get a list of bundles.
     * The getListAction expects the standard listing parameters directly in the request
     * parameters start, limit, filter and sort.
     */
    public function getListAction()
    {
        $this->View()->assign(
            $this->getList(
                $this->Request()->getParam('productId'),
                $this->Request()->getParam('filter', []),
                $this->Request()->getParam('sort', []),
                $this->Request()->getParam('start'),
                $this->Request()->getParam('limit')
            )
        );
    }

    /**
     * Global interface to get an offset of defined bundles with the whole bundle data.
     * The getFullListAction expects the standard listing parameters directly in the request
     * parameters start, limit, filter and sort.
     */
    public function getFullListAction()
    {
        $this->View()->assign(
            $this->getFullList(
                $this->Request()->getParam('filter', []),
                $this->Request()->getParam('sort', []),
                $this->Request()->getParam('start'),
                $this->Request()->getParam('limit')
            )
        );
    }

    /**
     * Global interface to get the whole data for a single product bundle.
     * The getDetailAction expects the bundle id in the request parameters.
     */
    public function getDetailAction()
    {
        $this->View()->assign(
            $this->getDetail(
                (int) $this->Request()->getParam('id')
            )
        );
    }

    /**
     * Global interface to create a new bundle.
     * The createBundleAction expects the bundle data directly in the request parameters.
     * <code>
     * Example:
     * $this->Request()->getParams() => Whole bundle data
     * </code>
     */
    public function createBundleAction()
    {
        $this->View()->assign(
            $this->saveBundle(
                $this->Request()->getParams()
            )
        );
    }

    /**
     * Global interface to update an existing bundle.
     * The updateBundleAction expects the bundle data directly in the request parameters.
     * <code>
     * Example:
     * $this->Request()->getParams() => Whole bundle data
     * </code>
     */
    public function updateBundleAction()
    {
        $this->View()->assign(
            $this->saveBundle(
                $this->Request()->getParams()
            )
        );
    }

    /**
     * Global interface to update the position of a bundle-product.
     * Expects a json-encoded array in the request parameter "items".
     */
    public function savePositionAction()
    {
        $this->View()->assign(
            $this->savePosition(
                json_decode($this->Request()->getParam('items'))
            )
        );
    }

    /**
     * Global interface to delete an existing bundle.
     * The deleteBundleAction expects the id of the bundle in the request parameters.
     */
    public function deleteBundleAction()
    {
        $this->View()->assign(
            $this->deleteBundle(
                json_decode($this->Request()->getParam('bundles'))
            )
        );
    }

    /**
     * Global interface which used for a product suggest search component.
     * Returns an offset of products.
     */
    public function searchProductAction()
    {
        $searchString = $this->Request()->getParam('query');
        if (!$searchString) {
            $searchString = $this->Request()->getParam('number');
        }

        $this->View()->assign(
            $this->searchProduct(
                $searchString,
                $this->Request()->getParam('start'),
                $this->Request()->getParam('limit')
            )
        );
    }

    /**
     * Global interface which is used for the bundle backend extension of the product module.
     * Returns an offset of product variants for the passed product id.
     */
    public function getVariantsAction()
    {
        $this->View()->assign(
            $this->getVariants(
                $this->Request()->getParam('productId'),
                $this->Request()->getParam('start', 0),
                $this->Request()->getParam('limit', 20)
            )
        );
    }

    /**
     * Global interface to validate a bundle order number.
     * Used as remote validation for the number field of the backend module.
     * Expects the number in the "value" request parameter and a optional bundle id
     * parameter in the "param" key.
     */
    public function validateNumberAction()
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

        echo $this->validateNumber(
            $this->Request()->getParam('value'),
            $this->Request()->getParam('param')
        );
    }

    /**
     * The initAcl function initials the shopware acl for the bundle extension.
     */
    protected function initAcl()
    {
        $this->addAclPermission('index', 'read', 'Insufficient Permissions');
        $this->addAclPermission('createBundle', 'create', 'Insufficient Permissions');
        $this->addAclPermission('updateBundle', 'update', 'Insufficient Permissions');
        $this->addAclPermission('deleteBundle', 'delete', 'Insufficient Permissions');
    }

    /**
     * @param array $filter An array of listing filters to filter the result set
     * @param array $sort   An array of listing order by condition to sort the result set
     * @param int   $offset an offset for a paginated listing
     * @param int   $limit  a limit for a paginated listing
     *
     * @return array Result of the listing query or the exception code and message
     */
    protected function getFullList(array $filter, array $sort, $offset, $limit)
    {
        try {
            if (!empty($filter) && $filter[0]['property'] === 'free') {
                $filter = [
                    ['property' => 'bundle.name', 'value' => $filter[0]['value'], 'operator' => 'LIKE'],
                    ['property' => 'bundle.number', 'value' => $filter[0]['value'], 'operator' => 'LIKE'],
                    ['property' => 'article.name', 'value' => $filter[0]['value'], 'operator' => 'LIKE'],
                    ['property' => 'articleMainDetail.number', 'value' => $filter[0]['value'], 'operator' => 'LIKE'],
                ];
            }

            $bundleRepository = $this->get('models')->getRepository(Bundle::class);
            /** @var Query $query */
            $query = $bundleRepository->getFullListQuery($filter, $sort, $offset, $limit);

            $query->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);

            /** @var Paginator $paginator */
            $paginator = $this->get('models')->createPaginator($query);

            //returns the total count of the query
            $total = $paginator->count();

            $result = $paginator->getIterator()->getArrayCopy();

            foreach ($result as &$bundle) {
                $tax = $bundle['article']['tax'];
                foreach ($bundle['prices'] as &$price) {
                    $price['net'] = $price['price'];
                    if ($bundle['discountType'] === BundleComponentInterface::ABSOLUTE_DISCOUNT && $price['customerGroup']['taxInput']) {
                        $price['price'] = $price['price'] / 100 * (100 + $tax['tax']);
                    }
                }
            }

            return [
                'success' => true,
                'data' => $result,
                'total' => $total,
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
     * @param int   $productId Identifier for the product
     * @param array $filter    An array of listing filters to filter the result set
     * @param array $sort      An array of listing order by condition to sort the result set
     * @param int   $offset    an offset for a paginated listing
     * @param int   $limit     a limit for a paginated listing
     *
     * @return array Result of the listing query or the exception code and message
     */
    protected function getList($productId, array $filter, array $sort, $offset, $limit)
    {
        try {
            $bundleRepository = $this->get('models')->getRepository(Bundle::class);
            /** @var Query $query */
            $query = $bundleRepository->getProductBundlesQuery($productId, $filter, $sort, $offset, $limit);

            $query->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);
            /** @var Paginator $paginator */
            $paginator = $this->get('models')->createPaginator($query);

            //returns the total count of the query
            $total = $paginator->count();

            $result = $paginator->getIterator()->getArrayCopy();

            return [
                'success' => true,
                'data' => $result,
                'total' => $total,
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
     * Internal function to get the whole data for a single bundle. The bundle will be identified over the
     * passed id parameter.
     *
     * @param int $id
     *
     * @return array
     */
    protected function getDetail($id)
    {
        try {
            $bundleRepository = $this->get('models')->getRepository(Bundle::class);
            /** @var Query $query */
            $query = $bundleRepository->getBundleQuery($id);

            $query->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);
            /** @var Paginator $paginator */
            $paginator = $this->get('models')->createPaginator($query);

            $bundles = $paginator->getIterator()->getArrayCopy();
            $bundle = $bundles[0];

            foreach ($bundle['articles'] as &$product) {
                //we have to convert the bundle product prices to gross prices
                $tax = $product['articleDetail']['article']['tax'];
                foreach ($product['articleDetail']['prices'] as &$price) {
                    $price['net'] = $price['price'];
                    if ($price['customerGroup']['taxInput']) {
                        $price['price'] = $price['price'] / 100 * (100 + $tax['tax']);
                    }
                }
            }
            unset($product, $price);

            if ($bundle['discountType'] === BundleComponentInterface::ABSOLUTE_DISCOUNT) {
                $tax = $bundle['article']['tax'];
                foreach ($bundle['prices'] as &$price) {
                    $price['net'] = $price['price'];
                    if ($price['customerGroup']['taxInput']) {
                        $price['price'] = $price['price'] / 100 * (100 + $tax['tax']);
                    }
                }
            }

            return [
                'success' => true,
                'data' => $bundle,
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
     * Internal function to save a bundle. Used from the createBundleAction and updateBundleAction interface.
     * Contains the whole source code logic to save a single bundle.
     *
     * @param array $data The whole bundle data as array
     *
     * @return array Result of the delete process
     */
    protected function saveBundle(array $data)
    {
        try {
            /* @var Bundle $bundle */
            if (empty($data['id'])) {
                $bundle = new Bundle();
                $this->get('models')->persist($bundle);
                $data['created'] = new DateTime();
            } else {
                $bundle = $this->get('models')->getRepository(Bundle::class)->find($data['id']);
                unset($data['created']);
            }

            $data = $this->prepareBundleData($data);

            //prepare bundle data thrown an exception or not all required parameters passed.
            if (isset($data['success']) && $data['success'] === false) {
                return $data;
            }

            if ($bundle instanceof Bundle === false) {
                return ['success' => false];
            }

            $bundle->fromArray($data);
            $this->get('models')->flush();

            $data = $this->getDetail($bundle->getId());

            return ['success' => true, 'data' => $data['data']];
        } catch (Exception $e) {
            return [
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Internal helper function which used for the product suggest search in the bundle backend
     * extension of the product module.
     *
     * @param string|null $searchValue
     * @param int         $offset
     * @param int         $limit
     *
     * @return array
     */
    protected function searchProduct($searchValue, $offset = 0, $limit = 10)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->get('models')->createQueryBuilder();
        $builder->select(['details', 'prices', 'customerGroup', 'product', 'tax'])
            ->from(ProductVariant::class, 'details')
            ->innerJoin('details.article', 'product')
            ->innerJoin('details.prices', 'prices')
            ->innerJoin('prices.customerGroup', 'customerGroup')
            ->innerJoin('product.tax', 'tax')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($searchValue !== null && $searchValue !== '') {
            // Escape "_" (MySQL wildcards) and surround with "%" (MySQL wildcards)
            $searchValue = '%' . str_replace('_', '\_', $searchValue) . '%';

            $builder->where('product.name LIKE :searchValue')
                ->orWhere('details.number LIKE :searchValue')
                ->orWhere('product.description LIKE :searchValue')
                ->setParameter('searchValue', $searchValue);
        }

        $query = $builder->getQuery();

        $query->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);
        /** @var Paginator $paginator */
        $paginator = $this->get('models')->createPaginator($query);
        $products = $paginator->getIterator()->getArrayCopy();
        $totalCount = $paginator->count();

        foreach ($products as &$product) {
            $tax = $product['article']['tax'];
            foreach ($product['prices'] as &$price) {
                if ($price['customerGroup']['taxInput']) {
                    $price['price'] = $price['price'] / 100 * (100 + $tax['tax']);
                }
            }
        }

        return [
            'success' => true,
            'total' => $totalCount,
            'data' => $products,
        ];
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
    protected function getVariants($productId, $offset, $limit)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->get('models')->createQueryBuilder();
        $builder->select(['details'])
            ->from(ProductVariant::class, 'details')
            ->where('details.articleId = :productId')
            ->setParameters(['productId' => $productId])
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $query = $builder->getQuery();
        $query->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);
        /** @var Paginator $paginator */
        $paginator = $this->get('models')->createPaginator($query);

        return [
            'success' => true,
            'total' => $paginator->count(),
            'data' => $paginator->getIterator()->getArrayCopy(),
        ];
    }

    /**
     * Internal function to delete a bundle. Used from the deleteBundleAction interface.
     * Contains the whole source code logic to delete a single bundle.
     *
     * @param array $bundleIds an array of bundle ids
     *
     * @return array Result of the delete process
     */
    protected function deleteBundle(array $bundleIds)
    {
        try {
            foreach ($bundleIds as $bundleId) {
                /** @var Bundle $bundle */
                $bundle = $this->get('models')->find(Bundle::class, $bundleId);
                $this->get('models')->remove($bundle);
            }

            $this->get('models')->flush();

            return ['success' => true, 'data' => $bundleIds];
        } catch (Exception $e) {
            return [
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Internal function which validates the passed order number for product bundles.
     * Each bundle order number can only be defined one time.
     * Returns true if the passed number is unique.
     *
     * @param string $number   Number to validate
     * @param int    $bundleId Optional bundle id, sent to exclude an existing bundle
     *
     * @return bool
     */
    protected function validateNumber($number, $bundleId = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->get('models')->createQueryBuilder();
        $builder->select(['bundle'])
            ->from(Bundle::class, 'bundle')
            ->where('bundle.number = :number')
            ->setParameter('number', $number);

        if ($bundleId !== null) {
            $builder->andWhere('bundle.id != :bundleId')->setParameter('bundleId', $bundleId);
        }

        $result = $builder->getQuery()->getArrayResult();

        return empty($result);
    }

    /**
     * Internal function to save the position of the bundle products if the user rearranges the bundle products.
     *
     * @param array $items
     *
     * @return array
     */
    protected function savePosition(array $items)
    {
        try {
            foreach ($items as $item) {
                /** @var BundleProduct $bundleProduct */
                $bundleProduct = $this->get('models')->find(BundleProduct::class, $item->id);
                $bundleProduct->setPosition($item->position);
            }
            $this->get('models')->flush();

            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Internal helper function to prepare the associated data of a single bundle resource.
     *
     * @param array $data
     *
     * @return array
     */
    private function prepareBundleData(array $data)
    {
        if (empty($data['articleId'])) {
            return ['success' => false];
        }

        $data['article'] = $this->get('models')->find(Article::class, $data['articleId']);
        $productVariantRepository = $this->get('models')->getRepository(ProductVariant::class);

        if (!empty($data['articles'])) {
            foreach ($data['articles'] as &$productData) {
                if (!empty($productData['articleDetailId'])) {
                    $productData['articleDetail'] = $productVariantRepository->find(
                        (int) $productData['articleDetailId']
                    );
                }
                if (empty($productData['position'])) {
                    $productData['position'] = $this->getLatestPosition($data['id']);
                }
            }
            unset($productData);
        }

        if (!empty($data['customerGroups'])) {
            $customerGroups = [];
            foreach ($data['customerGroups'] as $groupData) {
                if (!empty($groupData['id'])) {
                    $customerGroups[] = $this->get('models')->find(Group::class, $groupData['id']);
                }
            }
            $data['customerGroups'] = $customerGroups;
        }

        /** @var Tax $tax */
        $tax = $data['article']->getTax();
        $data['tax'] = $tax;

        if (!empty($data['prices'])) {
            $prices = [];
            foreach ($data['prices'] as $priceData) {
                $customerGroupRepository = $this->get('models')->getRepository(Group::class);
                /** @var Group $customerGroup */
                $customerGroup = $customerGroupRepository->find($priceData['customerGroup'][0]['id']);

                if ($data['discountType'] === BundleComponentInterface::ABSOLUTE_DISCOUNT && $customerGroup->getTaxInput()) {
                    $priceData['price'] = ($priceData['price'] / (100 + $tax->getTax())) * 100;
                }
                $priceData['customerGroup'] = $customerGroup;

                $prices[] = $priceData;
            }
            $data['prices'] = $prices;
        }

        if (!empty($data['limitedDetails'])) {
            $limitedDetails = [];
            foreach ($data['limitedDetails'] as $limitData) {
                $limitedDetails[] = $productVariantRepository->find($limitData['id']);
            }
            $data['limitedDetails'] = $limitedDetails;
        }

        //removes date if it is not set
        $data['validFrom'] = isset($data['validFrom']) ? $data['validFrom'] : null;
        $data['validTo'] = isset($data['validTo']) ? $data['validTo'] : null;

        return $data;
    }

    /**
     * Internal function to get the latest saved position to have some manual auto-increment
     *
     * @param int $bundleId
     *
     * @return int The new position
     */
    private function getLatestPosition($bundleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->get('models')->createQueryBuilder();
        $builder->select('products.position')
            ->from(BundleProduct::class, 'products')
            ->where('products.bundleId = :bundleId')
            ->setParameter('bundleId', $bundleId)
            ->orderBy('products.position', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1);

        $result = $builder->getQuery()->getOneOrNullResult();
        $position = $result['position'];

        return empty($position) ? 1 : $position + 1;
    }
}
