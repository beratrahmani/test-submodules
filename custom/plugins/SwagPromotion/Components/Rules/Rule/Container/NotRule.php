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

namespace SwagPromotion\Components\Rules\Rule\Container;

use SwagPromotion\Components\Rules\Rule;

/**
 * NotRule inverses the return value of the child rule. Only one child is possible
 */
class NotRule extends AbstractContainer
{
    /**
     * {@inheritdoc}
     */
    public function addRule(Rule $rule)
    {
        parent::addRule($rule);
        $this->checkRules();
    }

    /**
     * {@inheritdoc}
     */
    public function setRules($rules)
    {
        parent::setRules($rules);
        $this->checkRules();
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        return !$this->rules[0]->validate();
    }

    /**
     * Enforce that NOT only handles ONE child rule
     *
     * @throws \RuntimeException
     */
    protected function checkRules()
    {
        if (count($this->rules) > 1) {
            throw new \RuntimeException('NOT rule can only hold one rule');
        }
    }
}
