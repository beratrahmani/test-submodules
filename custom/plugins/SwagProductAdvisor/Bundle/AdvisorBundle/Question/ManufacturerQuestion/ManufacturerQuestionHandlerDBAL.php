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

namespace SwagProductAdvisor\Bundle\AdvisorBundle\Question\ManufacturerQuestion;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionInterface;
use SwagProductAdvisor\Bundle\SearchBundleDBAL\QuestionHandlerInterface;

/**
 * Class ManufacturerQuestionHandlerDBAL
 */
class ManufacturerQuestionHandlerDBAL implements QuestionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(QuestionInterface $question)
    {
        return $question->getType() === 'manufacturer';
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QuestionInterface $question, ProductContextInterface $context, QueryBuilder $query)
    {
        $answers = $question->getSelectedAnswers();

        $selections = [];
        $ids = [];
        foreach ($answers as $answer) {
            $key = ':manufacturer_' . md5(json_encode($answer));
            $selections[] = 'IF(product.supplierID = ' . $key . ', ' . $question->getBoost() . ', 0)';
            $query->setParameter($key, $answer->getKey());
            $ids[] = $answer->getKey();
        }

        if ($question->isExclude()) {
            $key = ':manufacturer' . md5(json_encode($question));
            $query->andWhere('product.supplierID IN (' . $key . ')');
            $query->setParameter($key, $ids, Connection::PARAM_INT_ARRAY);
        }

        return $selections;
    }
}
