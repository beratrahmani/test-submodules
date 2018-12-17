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

use SwagPromotion\Components\Rules\Registry\Registry;
use SwagPromotion\Components\Rules\Rule\Container\Container;

/**
 * RuleBuilder helps you creating nested rules from an array structure
 */
class RuleBuilder
{
    /**
     * @var Registry
     */
    private $ruleRegistry;

    /**
     * RuleBuilder constructor.
     *
     * @param Registry $ruleRegistry
     */
    public function __construct(Registry $ruleRegistry)
    {
        $this->ruleRegistry = $ruleRegistry;
    }

    /**
     * @param array  $array         Array to automatically create rule tree from
     * @param string $containerType Type of the overlaying container
     *
     * @return Container
     */
    public function fromArray($array, $containerType = 'and')
    {
        /** @var Container $container */
        $container = $this->ruleRegistry->get($containerType);
        foreach ($array as $name => $value) {
            $container->addRule($this->getRule($name, $value));
        }

        return $container;
    }

    /**
     * Return a rule object depending on the current array element
     *
     * @param string            $name
     * @param array|Rule|string $value
     *
     * @return Rule|Container
     */
    private function getRule($name, $value)
    {
        if (is_array($value) && $this->ruleRegistry->isContainer($name)) {
            // array = container. Build it by recursively the fromArray method
            return $this->fromArray($value, $name);
        }

        if ($value instanceof Rule) {
            // instance of rule
            return $value;
        }

        // If only a rule name was passed, normalize the form
        // e.g. array('false') is normalized to $name = 'false'
        if (is_numeric($name) && is_string($value)) {
            $name = $value;
            $value = [];
        }

        // any other form like array('myRule' => 333)
        return $this->ruleRegistry->get($name, $value);
    }
}
