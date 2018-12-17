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

namespace SwagPromotion\Components\Rules;

class CompareRule implements Rule
{
    /** @var array $data */
    private $data;

    /** @var string $fieldName */
    private $fieldName;

    /** @var null|string $operator */
    private $operator;

    /** @var null|mixed $expectedValue */
    private $expectedValue;

    /**
     * @param array       $data      nested array of items to compare
     * @param string      $fieldName Field name to check
     * @param null|string $operator  Operator string
     * @param null|mixed  $value     Value to compare
     */
    public function __construct(array $data, $fieldName, $operator = null, $value = null)
    {
        $this->data = $data;
        $this->fieldName = $fieldName;
        $this->operator = $operator;
        $this->expectedValue = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        $subElement = false;
        $fieldName = $this->fieldName;

        if (strpos($this->fieldName, '.') !== false) {
            list($subElement, $fieldName) = explode('.', $this->fieldName);
        }

        // use gross price to compare
        if ($this->fieldName === 'price::price') {
            $fieldName = array_shift(explode('::', $this->fieldName));
        }

        foreach ($this->data as $datum) {
            $results = [];

            if ($subElement) {
                $values = array_column($datum[$subElement], $fieldName);
            } else {
                $values = [$datum[$fieldName]];

                if ($values[0] === null && strstr($fieldName, 'address')) {
                    $values = $this->getAddressValues($fieldName, $datum);
                }
            }

            foreach ($values as $value) {
                $results[] = $this->check($value);
            }

            if ($this->operator === 'notin' || $this->operator === '!=') {
                return !in_array(false, $results);
            }

            return in_array(true, $results);
        }

        return false;
    }

    /**
     * Iterates through the addresses array and searches for the value with the specified fieldName
     *
     * @param string $fieldName
     * @param array  $data
     *
     * @return array
     */
    private function getAddressValues($fieldName, array $data)
    {
        if (empty($data['user::addresses'])) {
            return [];
        }

        $result = [];

        foreach ($data['user::addresses'] as $address) {
            if (array_key_exists($fieldName, $address)) {
                $result[] = $address[$fieldName];
            }
        }

        return array_values($result);
    }

    /**
     * @param string $actualValue
     *
     * @throws \RuntimeException
     *
     * @return bool
     */
    private function check($actualValue)
    {
        switch ($this->operator) {
            case '>':
                return $actualValue > $this->expectedValue;
            case '>=':
                return $actualValue >= $this->expectedValue;
            case '<':
                return $actualValue < $this->expectedValue;
            case '<=':
                return $actualValue <= $this->expectedValue;
            case '=':
                return $actualValue == $this->expectedValue;
            case 'notcontains':
                return stripos($actualValue, $this->expectedValue) === false;
            case 'contains':
                return stripos($actualValue, $this->expectedValue) !== false;
            case '<>':
            case '!=':
                return $actualValue != $this->expectedValue;
            case 'istrue':
                return $actualValue == 1;
            case 'isfalse':
                return $actualValue == 0;
            case 'notin':
                if (!is_array($actualValue)) {
                    $actualValue = [$actualValue];
                }
                $expected = $this->split($this->expectedValue);
                $result = array_intersect($actualValue, $expected);

                return empty($result);
            case 'in':
                if (!is_array($actualValue)) {
                    $actualValue = [$actualValue];
                }
                $expected = $this->split($this->expectedValue);
                $result = array_intersect($actualValue, $expected);

                return !empty($result);
        }

        throw new \RuntimeException("Unknown operator {$this->operator}");
    }

    /**
     * @param string $expected
     *
     * @return array
     */
    private function split($expected)
    {
        return is_array($expected) ? $expected : array_map(
            function ($line) {
                return trim($line);
            },
            explode('|', $expected)
        );
    }
}
