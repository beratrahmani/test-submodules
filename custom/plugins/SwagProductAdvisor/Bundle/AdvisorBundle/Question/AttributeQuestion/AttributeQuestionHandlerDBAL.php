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

namespace SwagProductAdvisor\Bundle\AdvisorBundle\Question\AttributeQuestion;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionInterface;
use SwagProductAdvisor\Bundle\SearchBundleDBAL\QuestionHandlerInterface;

/**
 * Class AttributeQuestionHandlerDBAL
 */
class AttributeQuestionHandlerDBAL implements QuestionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(QuestionInterface $question)
    {
        return $question instanceof AttributeQuestion;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QuestionInterface $question, ProductContextInterface $context, QueryBuilder $query)
    {
        $answers = $question->getSelectedAnswers();

        $field = 'productAttribute.' . $question->getField();
        $selections = [];

        $values = [];
        foreach ($answers as $answer) {
            $placeholder = ':attribute_' . md5(json_encode($answer));
            $query->setParameter($placeholder, $answer->getValue());
            $selections[] = 'IF(' . $field . ' = ' . $placeholder . ', ' . $question->getBoost() . ', 0)';
            $values[] = $answer->getValue();
        }

        if ($question->isExclude()) {
            $key = 'ad_attributes' . md5(json_encode($question));

            $query->innerJoin(
                'product',
                's_articles_attributes',
                $key,
                'product.id = ' . $key . '.articleID AND ' . $key . '.' . $question->getField() . ' IN (:' . $key . ')'
            );
            $query->setParameter(':' . $key, $values, Connection::PARAM_STR_ARRAY);
        }

        return $selections;
    }
}
