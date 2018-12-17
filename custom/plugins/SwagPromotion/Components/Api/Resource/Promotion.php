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

namespace SwagPromotion\Components\Api\Resource;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Shopware\Components\Api\Exception as ApiException;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Components\Model\QueryBuilder;
use SwagPromotion\Models\Promotion as PromotionModel;

class Promotion extends Resource
{
    /**
     * Return a list of entities
     *
     * @param int   $offset
     * @param int   $limit
     * @param array $filter
     * @param array $sort
     *
     * @return array
     */
    public function getList($offset, $limit, array $filter, array $sort)
    {
        $builder = $this->getBaseQuery();
        $builder = $this->addQueryLimit($builder, $offset, $limit);

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }
        if (!empty($sort)) {
            $builder->addOrderBy($sort);
        }

        $query = $builder->getQuery();

        $query->setHydrationMode($this->getResultMode());

        $paginator = new Paginator($query);

        $totalResult = $paginator->count();

        $result = $paginator->getIterator()->getArrayCopy();

        return ['data' => $result, 'total' => $totalResult];
    }

    /**
     * Read the given entity $id
     *
     * @param int $id
     *
     * @throws ApiException\NotFoundException
     *
     * @return PromotionModel|array
     */
    public function getOne($id)
    {
        $builder = $this->getBaseQuery();

        $builder->where('promotion.id = :id')
            ->setParameter('id', $id);

        /** @var $model PromotionModel */
        $model = $builder->getQuery()->getOneOrNullResult($this->getResultMode());

        if (!$model) {
            throw new ApiException\NotFoundException("Promotion by id $id not found");
        }

        return $model;
    }

    /**
     * Create a new entity with $data
     *
     * @param array $data
     *
     * @throws ApiException\ValidationException
     *
     * @return PromotionModel
     */
    public function create(array $data)
    {
        $data = $this->prepareData($data);

        $model = new PromotionModel();
        $model->fromArray($data);

        $violations = $this->getManager()->validate($model);

        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->getManager()->persist($model);
        $this->flush();

        return $model;
    }

    /**
     * Update a given entity $id with $data
     *
     * @param int   $id
     * @param array $data
     *
     * @throws ApiException\NotFoundException
     * @throws ApiException\ParameterMissingException
     * @throws ApiException\ValidationException
     *
     * @return PromotionModel
     */
    public function update($id, array $data)
    {
        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $model PromotionModel */
        $model = $this->getManager()->find(PromotionModel::class, $id);

        if (!$model) {
            throw new ApiException\NotFoundException("promotion by id $id not found");
        }

        $data = $this->prepareData($data);

        $model->fromArray($data);

        $violations = $this->getManager()->validate($model);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->flush();

        return $model;
    }

    /**
     * Delete the given entity
     *
     * @param int $id
     *
     * @throws ApiException\NotFoundException
     * @throws ApiException\ParameterMissingException
     *
     * @return PromotionModel
     */
    public function delete($id)
    {
        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $model PromotionModel */
        $model = $this->getManager()->find(PromotionModel::class, $id);

        if (!$model) {
            throw new ApiException\NotFoundException("promotion by id $id not found");
        }

        $this->getManager()->remove($model);
        $this->flush();

        return $model;
    }

    /**
     * Here the data is prepared for automatic setting
     *
     * @param array $data
     *
     * @return array
     */
    protected function prepareData(array $data)
    {
        return $data;
    }

    /**
     * Adds a limit to the given query builder
     *
     * @param QueryBuilder $builder
     * @param int          $offset
     * @param int|null     $limit
     *
     * @return QueryBuilder
     */
    protected function addQueryLimit(QueryBuilder $builder, $offset, $limit = null)
    {
        $builder->setFirstResult($offset)->setMaxResults($limit);

        return $builder;
    }

    /**
     * Returns the base query builder
     *
     * @return QueryBuilder
     */
    protected function getBaseQuery()
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['promotion'])->from(PromotionModel::class, 'promotion');

        return $builder;
    }
}
