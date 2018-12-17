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

namespace SwagProductAdvisor\Bundle\SearchBundleES;

use ONGR\ElasticsearchDSL\Query\BoolQuery;
use ONGR\ElasticsearchDSL\Search;
use ONGR\ElasticsearchDSL\Sort\FieldSort;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionInterface;
use SwagProductAdvisor\Bundle\SearchBundle\AdvisorSorting;

/**
 * Class AdvisorSortingHandler
 */
class AdvisorSortingHandler implements HandlerInterface
{
    /**
     * @var QuestionHandlerInterface[]
     */
    private $handlers;

    /**
     * AdvisorSortingHandler constructor.
     *
     * @param QuestionHandlerInterface[] $handlers
     */
    public function __construct($handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @param CriteriaPartInterface $condition
     *
     * @return bool
     */
    public function supports(CriteriaPartInterface $condition)
    {
        return $condition instanceof AdvisorSorting;
    }

    /**
     * @param CriteriaPartInterface|AdvisorSorting $condition
     * @param Criteria                             $criteria
     * @param Search                               $search
     * @param ShopContextInterface                 $context
     *
     * @throws \Exception
     */
    public function handle(
        CriteriaPartInterface $condition,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $query = new BoolQuery();

        /** @var ProductContextInterface $context */
        $questions = $condition->getAdvisor()->getAnsweredQuestions();

        foreach ($questions as $question) {
            $handler = $this->getHandler($question);
            $handler->handle($question, $context, $query);
        }

        $search->addSort(new FieldSort('_score', 'DESC'));
        $search->addQuery($query);
    }

    /**
     * @param QuestionInterface $question
     *
     * @throws \Exception
     *
     * @return QuestionHandlerInterface
     */
    private function getHandler(QuestionInterface $question)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($question)) {
                return $handler;
            }
        }
        throw new \Exception(sprintf('Question of type %s not supported', $question->getType()));
    }
}
