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

namespace SwagProductAdvisor\Bundle\AdvisorBundle\Question\PropertyQuestion;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionInterface;
use SwagProductAdvisor\Bundle\SearchBundleDBAL\QuestionHandlerInterface;

/**
 * Class PropertyQuestionHandlerDBAL
 */
class PropertyQuestionHandlerDBAL implements QuestionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(QuestionInterface $question)
    {
        return $question->getType() === 'property';
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QuestionInterface $question, ProductContextInterface $context, QueryBuilder $query)
    {
        $selected = $question->getSelectedAnswers();

        $this->joinTable($query);

        $selections = [];
        $ids = [];
        foreach ($selected as $answer) {
            $key = ':advisorPropertyId_' . md5(json_encode($answer));
            $selection = 'IF(FIND_IN_SET(' . $key . ', GROUP_CONCAT(DISTINCT advisorProperties.valueID)) > 0, ' . $question->getBoost() . ', 0)';
            $query->setParameter($key, (int) $answer->getKey());
            $ids[] = $answer->getKey();
            $selections[] = $selection;
        }

        if ($question->isExclude()) {
            $key = 'advisorPropertyExclude' . md5(json_encode($question));

            $query->innerJoin(
                'product',
                's_filter_articles',
                $key,
                'product.id = ' . $key . '.articleID AND ' . $key . '.valueID IN (:' . $key . ')'
            );
            $query->setParameter(':' . $key, $ids, Connection::PARAM_INT_ARRAY);
        }

        $query->groupBy('product.id');

        return $selections;
    }

    /**
     * @param QueryBuilder $query
     */
    private function joinTable(QueryBuilder $query)
    {
        if (!$query->hasState('advisor_properties')) {
            $query->leftJoin(
                'product',
                's_filter_articles',
                'advisorProperties',
                'product.id = advisorProperties.articleID'
            );
            $query->addState('advisor_properties');
        }
    }
}
