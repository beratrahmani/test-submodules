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
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\Answer;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\Question;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionMatch;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\Advisor;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\AdvisorAttribute;
use SwagProductAdvisor\Bundle\SearchBundle\MatchingDecoratorInterface;

/**
 * Class PropertyMatchingDecorator
 */
class PropertyMatchingDecorator implements MatchingDecoratorInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * PropertyMatchingDecorator constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function decorate(ProductSearchResult $result, ShopContextInterface $context, Advisor $advisor)
    {
        $productIds = array_map(function (ListProduct $product) {
            return $product->getId();
        }, $result->getProducts());

        $answers = $this->getSelectedAnswers($advisor);

        $mapping = $this->getMatchingProductProperties(array_values($productIds));

        foreach ($result->getProducts() as $product) {
            /** @var AdvisorAttribute $attribute */
            $attribute = $product->getAttribute('advisor');

            $properties = [];
            if (array_key_exists($product->getId(), $mapping)) {
                $properties = $mapping[$product->getId()];
                $properties = explode('|', $properties);
            }

            foreach ($answers as $answer) {
                $label = $answer->getValue();
                if ($answer->getLabel()) {
                    $label = $answer->getLabel();
                }
                $match = new QuestionMatch($label, 'property');

                if (in_array($answer->getKey(), $properties, false)) {
                    $attribute->addMatch($match);
                } else {
                    $attribute->addMiss($match);
                }
            }
        }
    }

    /**
     * @param array $productIds
     *
     * @return array product id as key, property concat by | as value
     */
    public function getMatchingProductProperties(array $productIds)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['articleID', "GROUP_CONCAT(valueID SEPARATOR '|')"])
            ->from('s_filter_articles', 'filter')
            ->andWhere('filter.articleID IN (:productIds)')
            ->setParameter(':productIds', $productIds, Connection::PARAM_INT_ARRAY)
            ->groupBy('articleID');

        return $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * @param Advisor $advisor
     *
     * @return Answer[]
     */
    private function getSelectedAnswers(Advisor $advisor)
    {
        $answers = [];
        /** @var Question $question */
        foreach ($advisor->getAnsweredQuestions() as $question) {
            if ($question->getType() !== 'property') {
                continue;
            }
            $answers = array_merge($answers, $question->getSelectedAnswers());
        }

        return $answers;
    }
}
