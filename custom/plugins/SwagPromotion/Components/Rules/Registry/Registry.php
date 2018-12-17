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

namespace SwagPromotion\Components\Rules\Registry;

use SwagPromotion\Components\Rules\Registry\Type\Instance;
use SwagPromotion\Components\Rules\Rule;
use SwagPromotion\Components\Rules\Rule\Container;

/**
 * Registry keeps track of all known rules. If you want to add own rules, use the add() method.
 */
class Registry
{
    /**
     * @var Type[]
     */
    protected $rules = [];

    /**
     * Registry constructor.
     */
    public function __construct()
    {
        $this->add('and', new Container\AndRule());
        $this->add('or', new Container\OrRule());
        $this->add('xor', new Container\XorRule());
        $this->add('not', new Container\NotRule());

        $this->add('false', new Rule\FalseRule());
        $this->add('true', new Rule\TrueRule());
    }

    /**
     * Add a rule to the registry. Rule can be an instance of
     *  * Type\Callback
     *  * Type\Instance
     *  * Type\Name
     *
     * @param string    $name
     * @param Type|Rule $rule
     *
     * @throws \RuntimeException
     *
     * @return Registry
     */
    public function add($name, $rule)
    {
        // Convenience fallback: When having a rule instance, we can safely wrap it into a Type object
        if ($rule instanceof Rule) {
            $rule = new Instance($rule, $rule instanceof Container\Container);
        }

        // Enforce type objects
        if (!$rule instanceof Type) {
            throw new \RuntimeException('Rule must be an instance of Registry\\Type or Rule');
        }

        $this->rules[$this->normalizeName($name)] = $rule;

        return $this;
    }

    /**
     * @param string $name name of the rule to load
     * @param  $config array additional config to apply to the rule
     *
     * @throws \RuntimeException
     *
     * @return Rule
     */
    public function get($name, $config = [])
    {
        $name = $this->normalizeName($name);
        if (!isset($this->rules[$name])) {
            throw new \RuntimeException("Rule $name not found");
        }

        return $this->rules[$name]->get($config);
    }

    /**
     * Check if the requested rule is a container or not
     *
     * @param string $name
     *
     * @throws \RuntimeException
     *
     * @return bool
     */
    public function isContainer($name)
    {
        $name = $this->normalizeName($name);
        if (!isset($this->rules[$name])) {
            throw new \RuntimeException("Rule $name not found");
        }

        return $this->rules[$name]->isContainer();
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function normalizeName($name)
    {
        return preg_replace('#[^a-z]#i', '', $name);
    }
}
