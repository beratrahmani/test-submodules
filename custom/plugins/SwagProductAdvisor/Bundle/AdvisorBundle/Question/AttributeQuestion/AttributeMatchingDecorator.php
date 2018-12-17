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

use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionMatch;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\Advisor;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\AdvisorAttribute;
use SwagProductAdvisor\Bundle\SearchBundle\MatchingDecoratorInterface;

/**
 * Class AttributeMatchingDecorator
 */
class AttributeMatchingDecorator implements MatchingDecoratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function decorate(ProductSearchResult $result, ShopContextInterface $context, Advisor $advisor)
    {
        $questions = $this->getAttributeQuestions($advisor);

        foreach ($result->getProducts() as $product) {
            /** @var AdvisorAttribute $advisorAttribute */
            $advisorAttribute = $product->getAttribute('advisor');
            $coreAttribute = $product->getAttribute('core');

            foreach ($questions as $question) {
                $value = $coreAttribute->get($question->getField());

                foreach ($question->getSelectedAnswers() as $answer) {
                    $label = $answer->getValue();
                    if ($answer->getLabel()) {
                        $label = $answer->getLabel();
                    }
                    $match = new QuestionMatch($label, 'attribute');

                    if ($answer->getValue() == $value) {
                        $advisorAttribute->addMatch($match);
                    } else {
                        $advisorAttribute->addMiss($match);
                    }
                }
            }
        }
    }

    /**
     * @param Advisor $advisor
     *
     * @return AttributeQuestion[]
     */
    private function getAttributeQuestions(Advisor $advisor)
    {
        return array_filter($advisor->getAnsweredQuestions(), function (QuestionInterface $question) {
            return $question instanceof AttributeQuestion;
        });
    }
}
