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

namespace SwagProductAdvisor\Tests\Helper;

/**
 * Class QuestionHelper
 */
class QuestionHelper
{
    /**
     * Returns a mixed set of questions, where each question-type (Attribute, Property, Manufacturer, Price) appears once.
     *
     * @return array
     */
    public function getMixedQuestions()
    {
        $questions[] = $this->createAttributeQuestion();
        $questions[] = $this->createPropertyQuestion();
        $questions[] = $this->createManufacturerQuestion();

        return $questions;
    }

    /**
     * Creates and returns a property-question with valid answers as an array.
     *
     * @param array $customData
     *
     * @return array
     */
    public function createPropertyQuestion(array $customData = [])
    {
        $data = $this->mergeData(require __DIR__ . '/fixtures/property_question.php', $customData);

        return $data;
    }

    /**
     * Creates and returns a manufacturer-question with valid answers as an array.
     *
     * @param array $customData
     *
     * @return array
     */
    public function createManufacturerQuestion(array $customData = [])
    {
        $data = $this->mergeData(require __DIR__ . '/fixtures/manufacturer_question.php', $customData);

        return $data;
    }

    /**
     * Creates and returns a attribute-question with valid answers as an array.
     *
     * @param array $customData
     *
     * @return array
     */
    public function createAttributeQuestion(array $customData = [])
    {
        $data = $this->mergeData(require __DIR__ . '/fixtures/attribute_question.php', $customData);

        return $data;
    }

    /**
     * Creates and returns a price-default-question with valid answers as an array.
     *
     * @param array $customData
     *
     * @return array
     */
    public function createDefaultPriceQuestion(array $customData = [])
    {
        $data = $this->mergeData(require __DIR__ . '/fixtures/default_price_question.php', $customData);

        return $data;
    }

    /**
     * Creates and returns a price-range-question with valid answers as an array.
     *
     * @param array $customData
     *
     * @return array
     */
    public function createRangePriceQuestion(array $customData = [])
    {
        $data = $this->mergeData(require __DIR__ . '/fixtures/range_price_question.php', $customData);

        return $data;
    }

    /**
     * Helper method to merge the custom-data with the default-data.
     *
     * @param array $data
     * @param array $customData
     *
     * @return array
     */
    private function mergeData(array $data, array $customData = [])
    {
        if (!empty($customData)) {
            $data = array_merge($data, $customData);
        }

        return $data;
    }
}
