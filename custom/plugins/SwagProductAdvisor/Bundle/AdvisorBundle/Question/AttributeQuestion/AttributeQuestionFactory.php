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

use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionFactory;

/**
 * Class AttributeQuestionFactory
 */
class AttributeQuestionFactory extends QuestionFactory
{
    /**
     * @param array $data
     *
     * @return bool
     */
    public function supports(array $data)
    {
        return $data['type'] === 'attribute';
    }

    /**
     * @param array                $data
     * @param ShopContextInterface $context
     * @param array                $post
     *
     * @return AttributeQuestion
     */
    public function factory(array $data, ShopContextInterface $context, array $post)
    {
        $question = parent::factory($data, $context, $post);
        $question = AttributeQuestion::createFromQuestion($question);
        $question->setField($data['configuration']['field']);

        return $question;
    }
}
