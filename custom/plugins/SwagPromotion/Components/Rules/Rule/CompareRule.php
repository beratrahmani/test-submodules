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

namespace SwagPromotion\Components\Rules\Rule;

use SwagPromotion\Components\Rules\Rule;

/**
 * CompareRule allows simple comparisons of two operands.
 */
class CompareRule implements Rule
{
    /** @var string $operator */
    private $operator;

    /** @var string $leftOperand */
    private $leftOperand;

    /** @var string|null $rightOperand */
    private $rightOperand;

    /**
     * @param string      $leftOperand
     * @param string      $operator
     * @param string|null $rightOperand
     */
    public function __construct($leftOperand, $operator, $rightOperand = null)
    {
        $this->leftOperand = $leftOperand;
        $this->operator = $operator;
        $this->rightOperand = $rightOperand;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function validate()
    {
        if ($this->leftOperand === null) {
            throw new \RuntimeException('Left operand not defined');
        }
        if ($this->rightOperand === null) {
            throw new \RuntimeException('Right operand not defined');
        }

        switch ($this->operator) {
            case '>':
                return $this->leftOperand > $this->rightOperand;
            case '>=':
                return $this->leftOperand >= $this->rightOperand;
            case '<':
                return $this->leftOperand < $this->rightOperand;
            case '<=':
                return $this->leftOperand <= $this->rightOperand;
            case '=':
                return $this->leftOperand == $this->rightOperand;
            case '<>':
            case '!=':
                return $this->leftOperand != $this->rightOperand;
        }

        throw new \RuntimeException("Unknown operator {$this->operator}");
    }
}
