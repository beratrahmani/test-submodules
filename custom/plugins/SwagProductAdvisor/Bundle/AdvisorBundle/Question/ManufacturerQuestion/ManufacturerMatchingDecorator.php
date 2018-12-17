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

use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\Answer;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\Question;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionMatch;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\Advisor;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\AdvisorAttribute;
use SwagProductAdvisor\Bundle\SearchBundle\MatchingDecoratorInterface;

/**
 * Class ManufacturerMatchingDecorator
 */
class ManufacturerMatchingDecorator implements MatchingDecoratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function decorate(ProductSearchResult $result, ShopContextInterface $context, Advisor $advisor)
    {
        $answers = $this->getSelectedManufacturers($advisor);

        foreach ($result->getProducts() as $product) {
            /** @var AdvisorAttribute $attribute */
            $attribute = $product->getAttribute('advisor');

            $manufacturerId = $product->getManufacturer()->getId();

            foreach ($answers as $answer) {
                $label = $answer->getValue();
                if ($this->isMatchingAnswer($manufacturerId, $answer)) {
                    if ($answer->getLabel()) {
                        $label = $answer->getLabel();
                    }
                    $attribute->addMatch(new QuestionMatch($label, 'manufacturer'));
                } else {
                    $attribute->addMiss(new QuestionMatch($label, 'manufacturer'));
                }
            }
        }
    }

    /**
     * @param int    $manufacturerId
     * @param Answer $answer
     *
     * @return null|Answer
     */
    private function isMatchingAnswer($manufacturerId, Answer $answer)
    {
        if ($answer->getKey() === (string) $manufacturerId) {
            return true;
        }

        return null;
    }

    /**
     * @param Advisor $advisor
     *
     * @return Answer[]
     */
    private function getSelectedManufacturers(Advisor $advisor)
    {
        $answers = [];
        foreach ($advisor->getAnsweredQuestions() as $question) {
            /** @var Question $question */
            if ($question->getType() !== 'manufacturer') {
                continue;
            }
            $answers = array_merge($answers, $question->getSelectedAnswers());
        }

        return $answers;
    }
}
