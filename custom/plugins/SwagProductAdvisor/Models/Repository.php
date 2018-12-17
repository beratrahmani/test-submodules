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

namespace SwagProductAdvisor\Models;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Shopware\Components\Model\ModelRepository;

/**
 * Class Repository
 */
class Repository extends ModelRepository
{
    /**
     * @param int $limit
     * @param int $offset
     *
     * @return Query
     */
    public function getAdvisorListQuery($limit, $offset)
    {
        $builder = $this->getAdvisorListQueryBuilder($limit, $offset);

        return $builder->getQuery();
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return QueryBuilder
     */
    public function getAdvisorListQueryBuilder($limit, $offset)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(['advisor', 'questions', 'teaserBanner', 'stream', 'answers'])
            ->from($this->getEntityName(), 'advisor')
            ->leftJoin('advisor.teaserBanner', 'teaserBanner')
            ->leftJoin('advisor.stream', 'stream')
            ->leftJoin('advisor.questions', 'questions')
            ->leftJoin('questions.answers', 'answers')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $builder;
    }

    /**
     * @return QueryBuilder
     */
    public function getAdvisorQueryBuilder()
    {
        $builder = $this->getAdvisorListQueryBuilder();

        $builder->where('advisor.id = :advisorId');

        return $builder;
    }

    /**
     * @param int $advisorId
     *
     * @return Query
     */
    public function getAdvisorQuery($advisorId)
    {
        $builder = $this->getAdvisorQueryBuilder();
        $query = $builder->getQuery();
        $query->setParameter(':advisorId', $advisorId);

        return $query;
    }
}
